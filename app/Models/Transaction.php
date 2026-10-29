<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 *  Transaction model - one row of the `transactions` table.
 * ----------------------------------------------------------------------------
 *  Besides plain CRUD (handled by Eloquent), this model owns the aggregate
 *  queries that power the dashboard cards, the charts, the insights and
 *  the CSV export. Every query is scoped to the owning user, so one user
 *  can never read or modify another user's records.
 * ============================================================================
 */
class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'user_id', 'category_id', 'amount', 'type', 'transaction_date', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /* ----------------------------- relations -------------------------------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /* ------------------------------ scopes ---------------------------------- */

    /** Restrict any query to one user's records (mass-assignment safe). */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('transactions.user_id', $userId);
    }

    /**
     * Apply the list filters (type, category, date range, free-text search)
     * used by both the Transactions page and the CSV export.
     */
    public function scopeFiltered(Builder $query, array $f): Builder
    {
        return $query
            ->when(in_array($f['type'] ?? '', ['income', 'expense'], true),
                fn (Builder $q) => $q->where('type', $f['type']))
            ->when(ctype_digit((string) ($f['category_id'] ?? '')),
                fn (Builder $q) => $q->where('category_id', (int) $f['category_id']))
            ->when(($f['date_from'] ?? '') !== '',
                fn (Builder $q) => $q->whereDate('transaction_date', '>=', $f['date_from']))
            ->when(($f['date_to'] ?? '') !== '',
                fn (Builder $q) => $q->whereDate('transaction_date', '<=', $f['date_to']))
            ->when(($f['q'] ?? '') !== '', function (Builder $q) use ($f) {
                $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $f['q']).'%';
                $q->where(function (Builder $inner) use ($like) {
                    $inner->where('description', 'LIKE', $like)
                          ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'LIKE', $like));
                });
            });
    }

    /* --------------------- aggregates for dashboard/API --------------------- */

    /**
     * Income and expense per month for roughly the last $months months.
     * Missing months are filled with zeros; leading empty months are trimmed
     * so the forecast is not distorted by inactivity before first use.
     *
     * @return array[] each: ['ym' => '2026-07', 'income' => 0.0, 'expense' => 0.0]
     */
    public static function monthlyTotals(int $userId, int $months = 12): array
    {
        $start = date('Y-m-01', strtotime('-'.($months - 1).' months'));

        $rows = static::ownedBy($userId)
            ->where('transaction_date', '>=', $start)
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') AS ym")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income'  THEN amount END), 0) AS income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount END), 0) AS expense")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        // Fill the gaps so the chart has one point per month
        $series = [];
        $cursor = new \DateTime($start);
        $end = new \DateTime(date('Y-m-01'));
        while ($cursor <= $end) {
            $ym = $cursor->format('Y-m');
            $series[] = [
                'ym' => $ym,
                'income' => (float) ($rows[$ym]->income ?? 0),
                'expense' => (float) ($rows[$ym]->expense ?? 0),
            ];
            $cursor->modify('+1 month');
        }

        // Trim leading months with no activity (keep at least the last one)
        while (count($series) > 1 && $series[0]['income'] == 0 && $series[0]['expense'] == 0) {
            array_shift($series);
        }

        return $series;
    }

    /** Expense total per category for one month - feeds the doughnut chart. */
    public static function categoryTotals(int $userId, string $ym)
    {
        return static::ownedBy($userId)
            ->join('categories', 'categories.category_id', '=', 'transactions.category_id')
            ->where('transactions.type', 'expense')
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$ym])
            ->groupBy('categories.category_id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->get([
                'categories.name',
                'categories.color',
                DB::raw('SUM(transactions.amount) AS total'),
            ]);
    }

    /** Headline figures for the dashboard cards. */
    public static function summary(int $userId): array
    {
        $cur = date('Y-m');
        $prev = date('Y-m', strtotime('first day of last month'));

        $row = static::ownedBy($userId)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) AS balance")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income'  AND DATE_FORMAT(transaction_date, '%Y-%m') = ? THEN amount END), 0) AS month_income", [$cur])
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ? THEN amount END), 0) AS month_expense", [$cur])
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' AND DATE_FORMAT(transaction_date, '%Y-%m') = ? THEN amount END), 0) AS prev_expense", [$prev])
            ->first();

        return [
            'balance' => (float) $row->balance,
            'month_income' => (float) $row->month_income,
            'month_expense' => (float) $row->month_expense,
            'prev_expense' => (float) $row->prev_expense,
        ];
    }

    /** Latest transactions for the dashboard side list. */
    public static function recentFor(int $userId, int $limit = 6)
    {
        return static::ownedBy($userId)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_id')
            ->limit($limit)
            ->get();
    }

    /** The single largest expense of a month (dashboard insight). */
    public static function topExpense(int $userId, string $ym): ?Transaction
    {
        return static::ownedBy($userId)
            ->with('category')
            ->where('type', 'expense')
            ->whereRaw("DATE_FORMAT(transaction_date, '%Y-%m') = ?", [$ym])
            ->orderByDesc('amount')
            ->first();
    }
}
