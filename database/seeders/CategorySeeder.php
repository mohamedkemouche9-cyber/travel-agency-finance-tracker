<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Thirteen global default categories (user_id NULL, available to everyone,
 * read-only in the UI) plus two personal categories owned by 'demo'.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // [category_id, user_id, name, color]
            [1,  null, 'Salary',           '#10b981'],
            [2,  null, 'Freelance',        '#34d399'],
            [3,  null, 'Investments',      '#06b6d4'],
            [4,  null, 'Food & Groceries', '#f59e0b'],
            [5,  null, 'Rent & Housing',   '#6366f1'],
            [6,  null, 'Transport',        '#3b82f6'],
            [7,  null, 'Utilities',        '#0ea5e9'],
            [8,  null, 'Dining Out',       '#f97316'],
            [9,  null, 'Entertainment',    '#a855f7'],
            [10, null, 'Health',           '#ef4444'],
            [11, null, 'Shopping',         '#ec4899'],
            [12, null, 'Education',        '#8b5cf6'],
            [13, null, 'Savings',          '#14b8a6'],
            [14, 1,    'Gym',              '#22c55e'],
            [15, 1,    'Side Project',     '#eab308'],
        ];

        DB::table('categories')->insert(array_map(fn (array $r) => [
            'category_id' => $r[0],
            'user_id' => $r[1],
            'name' => $r[2],
            'color' => $r[3],
            'created_at' => '2025-11-01 10:00:00',
        ], $rows));
    }
}
