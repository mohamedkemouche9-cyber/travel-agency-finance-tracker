<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ============================================================================
 *  Category model - one row of the `categories` table.
 * ----------------------------------------------------------------------------
 *  Two kinds of category exist:
 *    - global defaults (user_id IS NULL), available to everyone, read-only;
 *    - personal categories (user_id = owner), full CRUD for their owner.
 * ============================================================================
 */
class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';

    /** The table has created_at only. */
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'name', 'color'];

    /* ----------------------------- relations -------------------------------- */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id');
    }

    /* ------------------------------ scopes ---------------------------------- */

    /** Categories the given user may see and use: globals + their own. */
    public function scopeUsableBy(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->whereNull('categories.user_id')->orWhere('categories.user_id', $userId);
        });
    }

    /* ------------------------------ helpers --------------------------------- */

    public function isGlobal(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Every category the user may use, with a count of how many of THEIR
     * transactions use each one (drives the Categories page).
     */
    public static function allForUser(int $userId)
    {
        return static::usableBy($userId)
            ->withCount(['transactions as tx_count' => fn ($q) => $q->where('user_id', $userId)])
            ->orderBy('name')
            ->get();
    }

    /** May this user attach transactions to this category? */
    public static function isUsableBy(int $categoryId, int $userId): bool
    {
        return static::whereKey($categoryId)->usableBy($userId)->exists();
    }

    /**
     * Does a category with this name already exist for the user
     * (among the globals and their own)? Used for validation.
     */
    public static function nameExists(string $name, int $userId, int $excludeId = 0): bool
    {
        return static::usableBy($userId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->where('category_id', '<>', $excludeId)
            ->exists();
    }
}
