<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================================
 *  UserCode model - the 6-digit codes (`user_codes` table).
 * ----------------------------------------------------------------------------
 *  Codes are used for two purposes: 'verify' (account verification after
 *  registration) and 'reset' (forgotten password). Delivery is DEMO ONLY:
 *  no e-mail is sent - the code is flashed on screen for a few seconds and
 *  written to the application log. Security properties are unchanged:
 *
 *    - only a SHA-256 hash of the code is stored, never the code itself;
 *    - codes expire after finance.code_ttl_minutes;
 *    - at most finance.code_max_attempts wrong guesses per code;
 *    - a cooldown (finance.resend_cooldown) throttles "resend" requests;
 *    - issuing a new code invalidates all previous codes for that purpose.
 * ============================================================================
 */
class UserCode extends Model
{
    protected $table = 'user_codes';
    protected $primaryKey = 'code_id';

    /** The table has created_at only. */
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'purpose', 'code_hash', 'expires_at', 'attempts', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /* ------------------------------ workflow --------------------------------- */

    /**
     * Create and store a fresh 6-digit code for the user. Returns the PLAIN
     * code so the controller can flash it on screen (demo delivery), or
     * null while the resend-cooldown is still running.
     */
    public static function issue(User $user, string $purpose): ?string
    {
        if (static::cooldownRemaining($user, $purpose) > 0) {
            return null;
        }

        // Void every previous active code of the same purpose
        static::where('user_id', $user->user_id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => Carbon::now()]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        static::create([
            'user_id' => $user->user_id,
            'purpose' => $purpose,
            'code_hash' => hash('sha256', $code),
            'expires_at' => Carbon::now()->addMinutes((int) config('finance.code_ttl_minutes')),
        ]);

        // Demo delivery: record the code in the application log as well
        Log::info("[demo-code] {$purpose} code for {$user->email}: {$code}");

        return $code;
    }

    /** Seconds the user still has to wait before a new code may be issued. */
    public static function cooldownRemaining(User $user, string $purpose): int
    {
        $last = static::where('user_id', $user->user_id)
            ->where('purpose', $purpose)
            ->max('created_at');

        if ($last === null) {
            return 0;                                  // no code issued yet
        }

        $elapsed = Carbon::parse($last)->diffInSeconds(Carbon::now());

        return max(0, (int) config('finance.resend_cooldown') - (int) $elapsed);
    }

    /**
     * Check a submitted code.
     *
     * @return string 'ok' | 'invalid' | 'expired' | 'toomany'
     */
    public static function check(User $user, string $purpose, string $code): string
    {
        $row = static::where('user_id', $user->user_id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->orderByDesc('code_id')
            ->first();

        if ($row === null) {
            return 'invalid';
        }
        if ($row->expires_at->isPast()) {
            return 'expired';
        }
        if ((int) $row->attempts >= (int) config('finance.code_max_attempts')) {
            return 'toomany';
        }

        if (hash_equals($row->code_hash, hash('sha256', $code))) {
            $row->forceFill(['used_at' => Carbon::now()])->save();

            return 'ok';
        }

        // Wrong guess: count the attempt
        $row->increment('attempts');

        return ((int) $row->attempts >= (int) config('finance.code_max_attempts')) ? 'toomany' : 'invalid';
    }
}
