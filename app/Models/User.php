<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

/**
 * ============================================================================
 *  User model (Eloquent) - one row of the `users` table.
 * ----------------------------------------------------------------------------
 *  Extends Laravel's Authenticatable so the framework's session guard can
 *  log this model in and out. The password lives in the `password_hash`
 *  column (bcrypt via Hash::make), so getAuthPassword() is overridden.
 *  Also carries the login-throttling state (failed_attempts/locked_until).
 * ============================================================================
 */
class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username', 'email', 'password_hash', 'currency', 'is_verified',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    /* --------------------- authentication integration ---------------------- */

    /** Tell the auth guard which column stores the bcrypt hash. */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /** Column name used by Laravel's automatic hash-rehash check. */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /* ----------------------------- relations -------------------------------- */

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'user_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class, 'user_id');
    }

    public function codes(): HasMany
    {
        return $this->hasMany(UserCode::class, 'user_id');
    }

    /* --------------------------- login throttling --------------------------- */

    /** Is the account temporarily locked because of failed logins? */
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Count one failed login. After finance.login_max_attempts the account
     * is locked for finance.login_lock_minutes.
     */
    public function recordFailedLogin(): void
    {
        $attempts = (int) $this->failed_attempts + 1;

        if ($attempts >= (int) config('finance.login_max_attempts')) {
            $this->forceFill([
                'failed_attempts' => 0,
                'locked_until' => Carbon::now()->addMinutes((int) config('finance.login_lock_minutes')),
            ])->save();
        } else {
            $this->forceFill(['failed_attempts' => $attempts])->save();
        }
    }

    /** Reset the throttling counters after a successful login. */
    public function clearFailedLogins(): void
    {
        $this->forceFill(['failed_attempts' => 0, 'locked_until' => null])->save();
    }
}
