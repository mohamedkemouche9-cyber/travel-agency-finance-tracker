<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * ============================================================================
 *  AuthController - registration, verification, login/logout and password
 *  reset (all the "account" flows).
 * ----------------------------------------------------------------------------
 *  Security measures:
 *    - passwords hashed with Hash::make() / verified with Hash::check()
 *    - 6-digit codes for account verification and password reset,
 *      delivered DEMO-ONLY as an on-screen banner (no e-mail is sent)
 *    - login throttling (temporary lock after repeated failures)
 *    - identical error messages that do not reveal whether an e-mail exists
 *    - session ID regeneration on login (anti session-fixation)
 *    - CSRF token checked on every POST by Laravel's web middleware
 * ============================================================================
 */
class AuthController extends Controller
{
    /* ------------------------------ REGISTER -------------------------------- */

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'regex:/^[A-Za-z0-9_]{3,20}$/', 'unique:users,username'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72',
                'regex:/[A-Za-z]/', 'regex:/\d/', 'confirmed'],
        ], [
            'username.regex' => 'Username must be 3-20 characters using letters, numbers or underscores only.',
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'currency' => config('finance.default_currency'),
            'is_verified' => false,
        ]);

        $request->session()->put('pending_verify_id', $user->user_id);
        $this->issueDemoCode($user, 'verify');

        flash('success', 'Account created! Enter the 6-digit code shown above to verify your account.');

        return redirect()->route('verify');
    }

    /* --------------------------- VERIFICATION ------------------------------- */

    public function showVerify(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user instanceof User) {
            return $user;                                    // redirect
        }

        return view('auth.verify', [
            'maskedEmail' => self::maskEmail($user->email),
            'cooldown' => UserCode::cooldownRemaining($user, 'verify'),
        ]);
    }

    public function verify(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user instanceof User) {
            return $user;                                    // redirect
        }

        $request->validate(['code' => ['required', 'digits:6']]);

        $result = UserCode::check($user, 'verify', $request->string('code')->trim()->value());

        if ($result === 'ok') {
            $user->forceFill(['is_verified' => true])->save();
            $request->session()->forget('pending_verify_id');

            // Log the user straight in for a smooth first experience
            Auth::login($user);
            $request->session()->regenerate();

            flash('success', 'Account verified - welcome, '.$user->username.'!');

            return redirect()->route('dashboard');
        }

        $message = match ($result) {
            'expired' => 'That code has expired. Please request a new one.',
            'toomany' => 'Too many wrong attempts. Please request a new code.',
            default => 'Incorrect code. Please check and try again.',
        };

        return back()->withErrors(['code' => $message]);
    }

    /**
     * "Resend code" button on the verify and reset pages (POST).
     */
    public function resend(Request $request): RedirectResponse
    {
        $purpose = $request->input('purpose') === 'reset' ? 'reset' : 'verify';

        if ($purpose === 'verify') {
            $user = User::find($request->session()->get('pending_verify_id'));
        } else {
            $email = (string) $request->session()->get('reset_email', '');
            $user = $email !== '' ? User::where('email', $email)->first() : null;
        }

        if ($user !== null) {
            if ($this->issueDemoCode($user, $purpose) === null) {
                flash('warning', 'Please wait a moment before requesting another code.');
            } else {
                flash('success', 'A new code has been issued.');
            }
        }

        return redirect()->route($purpose === 'reset' ? 'reset' : 'verify');
    }

    /* -------------------------------- LOGIN --------------------------------- */

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'max:72'],
        ]);

        $identifier = $request->string('identifier')->trim()->value();

        // Login accepts either the username or the e-mail address
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if ($user !== null && $user->isLocked()) {
            $minutes = (int) ceil(now()->diffInSeconds($user->locked_until, true) / 60);

            return back()->withInput($request->only('identifier'))->withErrors([
                'identifier' => "Account temporarily locked after repeated failures. Try again in about {$minutes} minute(s).",
            ]);
        }

        // One generic message whether the account or the password is wrong,
        // so an attacker cannot discover which e-mails are registered.
        if ($user === null || ! Hash::check($request->input('password'), $user->password_hash)) {
            $user?->recordFailedLogin();

            return back()->withInput($request->only('identifier'))->withErrors([
                'identifier' => 'Invalid username/e-mail or password.',
            ]);
        }

        if (! $user->is_verified) {
            $request->session()->put('pending_verify_id', $user->user_id);
            $this->issueDemoCode($user, 'verify');
            flash('info', 'Your account is not verified yet. Use the code shown above.');

            return redirect()->route('verify');
        }

        // Successful login
        $user->clearFailedLogins();
        Auth::login($user);
        $request->session()->regenerate();       // anti session-fixation

        flash('success', 'Welcome back, '.$user->username.'!');

        return redirect()->route('dashboard');
    }

    /* -------------------------------- LOGOUT -------------------------------- */

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        flash('success', 'You have been logged out. See you soon!');

        return redirect()->route('login');
    }

    /* --------------------------- FORGOT / RESET ----------------------------- */

    public function showForgot(): View
    {
        return view('auth.forgot');
    }

    public function forgot(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $email = $request->string('email')->trim()->value();
        $user = User::where('email', $email)->first();

        if ($user !== null) {
            $this->issueDemoCode($user, 'reset');
        }

        // Same message whether or not the account exists (no enumeration)
        $request->session()->put('reset_email', $email);
        flash('info', 'If an account exists for that address, a 6-digit reset code has been issued.');

        return redirect()->route('reset');
    }

    public function showReset(Request $request): View
    {
        return view('auth.reset', [
            'resetEmail' => (string) $request->session()->get('reset_email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'max:72',
                'regex:/[A-Za-z]/', 'regex:/\d/', 'confirmed'],
        ], [
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);

        $user = User::where('email', $request->string('email')->trim()->value())->first();

        $result = $user !== null
            ? UserCode::check($user, 'reset', $request->string('code')->trim()->value())
            : 'invalid';

        if ($result === 'ok') {
            $user->forceFill([
                'password_hash' => Hash::make($request->input('password')),
                'failed_attempts' => 0,
                'locked_until' => null,
            ])->save();

            $request->session()->forget('reset_email');
            flash('success', 'Password updated. You can now log in with your new password.');

            return redirect()->route('login');
        }

        $message = match ($result) {
            'expired' => 'That code has expired. Please request a new one.',
            'toomany' => 'Too many wrong attempts. Please request a new code.',
            default => 'The code is not valid for this e-mail address.',
        };

        return back()->withInput($request->only('email'))->withErrors(['code' => $message]);
    }

    /* ------------------------------- helpers -------------------------------- */

    /**
     * Issue a fresh code (respecting the cooldown) and deliver it DEMO-ONLY:
     * the code is flashed as an on-screen banner that disappears after a few
     * seconds, and written to the application log. No e-mail is sent.
     *
     * @return string|null the code, or null while the cooldown is active
     */
    private function issueDemoCode(User $user, string $purpose): ?string
    {
        $code = UserCode::issue($user, $purpose);

        if ($code !== null) {
            flash('code', "Demo verification - your 6-digit code is: {$code}");
        }

        return $code;
    }

    /**
     * Resolve the account that is waiting for verification, or redirect
     * back to the login page when there is nothing to verify.
     */
    private function pendingUser(Request $request): User|RedirectResponse
    {
        $user = User::find($request->session()->get('pending_verify_id'));

        if ($user === null) {
            $request->session()->forget('pending_verify_id');

            return redirect()->route('login');
        }

        if ($user->is_verified) {
            $request->session()->forget('pending_verify_id');
            flash('info', 'This account is already verified. Please log in.');

            return redirect()->route('login');
        }

        return $user;
    }

    /**
     * j*****e@gmail.com - hide most of the address on the verify screen.
     */
    private static function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1)
            .str_repeat('*', max(3, mb_strlen($local) - 1))
            .'@'.$domain;
    }
}
