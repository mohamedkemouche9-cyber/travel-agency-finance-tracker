<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Monthly budgets for 'demo' (June + July 2026) and one for 'demo_secondary'.
 * July's Shopping/Dining budgets sit close to their limits on purpose so
 * the dashboard's budget alerts have something to show.
 */
class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // [user_id, category_id, month_start, amount]
            [1, 4,  '2026-07-01', '320.00'],   // Food & Groceries
            [1, 6,  '2026-07-01', '110.00'],   // Transport
            [1, 8,  '2026-07-01', '120.00'],   // Dining Out
            [1, 9,  '2026-07-01', '80.00'],    // Entertainment
            [1, 11, '2026-07-01', '150.00'],   // Shopping
            [1, 4,  '2026-06-01', '300.00'],
            [1, 8,  '2026-06-01', '110.00'],
            [1, 9,  '2026-06-01', '70.00'],
            [2, 4,  '2026-07-01', '200.00'],
        ];

        DB::table('budgets')->insert(array_map(fn (array $r) => [
            'user_id' => $r[0],
            'category_id' => $r[1],
            'month_start' => $r[2],
            'amount' => $r[3],
        ], $rows));
    }
}
