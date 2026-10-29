<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ============================================================================
 *  SettingsController - profile, currency, password change, account deletion.
 * ============================================================================
 */
class SettingsController extends Controller
{
    public function index(): View
    {
        return view('settings.index', [
            'pageTitle' => 'Settings',
            'currencies' => config('finance.currencies'),
        ]);
    }

    /**
     * Update username and currency symbol.
     */
    public function profile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validator = validator($request->all(), [
            'username' => ['required', 'regex:/^[A-Za-z0-9_]{3,20}$/',
                Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')],
            'currency' => ['required', Rule::in(config('finance.currencies'))],
        ], [
            'username.regex' => 'Username must be 3-20 characters using letters, numbers or underscores only.',
            'username.unique' => 'This username is already taken.',
            'currency.in' => 'Please choose a valid currency.',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->errors()->first());

            return redirect()->route('settings');
        }

        $user->forceFill([
            'username' => $request->string('username')->trim()->value(),
            'currency' => $request->input('currency'),
        ])->save();

        flash('success', 'Profile updated.');

        return redirect()->route('settings');
    }

    /**
     * Change password (requires the current password).
     */
    public function password(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validator = validator($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'max:72',
                'regex:/[A-Za-z]/', 'regex:/\d/', 'confirmed'],
        ], [
            'new_password.regex' => 'Password must contain both letters and numbers.',
            'new_password.confirmed' => 'The two passwords do not match.',
        ]);

        if ($validator->fails()) {
            flash('error', $validator->errors()->first());

            return redirect()->route('settings');
        }

        if (! Hash::check($request->input('current_password'), $user->password_hash)) {
            flash('error', 'Your current password is incorrect.');

            return redirect()->route('settings');
        }

        $user->forceFill([
            'password_hash' => Hash::make($request->input('new_password')),
        ])->save();

        flash('success', 'Password changed successfully.');

        return redirect()->route('settings');
    }

    /**
     * Permanently delete the account and all its data (confirmed by password).
     * Transactions, budgets, codes and the user's own categories are removed
     * automatically by the ON DELETE CASCADE foreign keys.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! Hash::check((string) $request->input('confirm_password'), $user->password_hash)) {
            flash('error', 'Password incorrect - account was NOT deleted.');

            return redirect()->route('settings');
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        flash('info', 'Your account and all its data have been deleted. Goodbye!');

        return redirect()->route('register');
    }
}
