<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 *  Budget model - monthly spending limits per category (`budgets` table).
 * ----------------------------------------------------------------------------
 *  A budget row is unique per (user, category, month). The month is stored
 *  as the first day of the month (DATE), e.g. 2026-07-01.
 * ============================================================================
 */
class Budget extends Model
{
    protected $table = 'budgets';
    protected $primaryKey = 'budget_id';

    /** The table stores no timestamps at all. */
    public $timestamps = false;

    protected $fillable = ['user_id', 'category_id', 'month_start', 'amount'];

    /* ----------------------------- relations -------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /* ------------------------------ queries --------------------------------- */

    /**
     * Every category available to the user for a given month ('YYYY-MM'),
     * with the budget amount (if set) and the amount actually spent.
     */
    public static function forMonth(int $userId, string $ym)
    {
        return Category::usableBy($userId)
            ->leftJoin('budgets', function ($join) use ($userId, $ym) {
                $join->on('budgets.category_id', '=', 'categories.category_id')
                    ->where('budgets.user_id', $userId)
                    ->where('budgets.month_start', $ym.'-01');
            })
            ->select(
                'categories.category_id',
                'categories.name',
                'categories.color',
                'budgets.amount AS budget_amount',
            )
            ->selectRaw("COALESCE((SELECT SUM(t.amount) FROM transactions t
                          WHERE t.user_id = ?
                            AND t.category_id = categories.category_id
                            AND t.type = 'expense'
                            AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?), 0) AS spent", [$userId, $ym])
            ->orderBy('categories.name')
            ->get();
    }

    /**
     * Create or update a budget. An amount of zero removes the budget.
     */
    public static function put(int $userId, int $categoryId, string $ym, float $amount): void
    {
        if ($amount <= 0) {
            static::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->where('month_start', $ym.'-01')
                ->delete();

            return;
        }

        static::updateOrCreate(
            ['user_id' => $userId, 'category_id' => $categoryId, 'month_start' => $ym.'-01'],
            ['amount' => $amount],
        );
    }

    /**
     * Budgets that are close to (>= 80%) or over their limit this month -
     * shown as alerts on the dashboard.
     */
    public static function alerts(int $userId, string $ym, int $limit = 4): array
    {
        $alerts = [];
        foreach (static::forMonth($userId, $ym) as $row) {
            if ($row->budget_amount !== null && (float) $row->budget_amount > 0) {
                $ratio = (float) $row->spent / (float) $row->budget_amount;
                if ($ratio >= 0.8) {
                    $row->ratio = $ratio;
                    $alerts[] = $row;
                }
            }
        }
        usort($alerts, fn ($a, $b) => $b->ratio <=> $a->ratio);

        return array_slice($alerts, 0, $limit);
    }
}
