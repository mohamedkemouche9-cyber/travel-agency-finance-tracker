<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Nine months of realistic activity for the 'demo' account (Nov 2025 -
 * Jul 2026) plus a small starter data set for 'demo_secondary'. The data deliberately
 * contains a gentle upward income trend and a large one-off purchase so the
 * dashboard charts, budget alerts and the linear-regression forecast all
 * have something meaningful to show.
 */
class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // [user_id, category_id, amount, type, transaction_date, description]
            [1, 5, '780.00', 'expense', '2025-11-01', 'Monthly rent'],
            [1, 4, '54.20', 'expense', '2025-11-03', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2025-11-05', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2025-11-07', 'Netflix subscription'],
            [1, 4, '47.80', 'expense', '2025-11-10', 'Groceries'],
            [1, 8, '28.50', 'expense', '2025-11-12', 'Dinner with friends'],
            [1, 7, '112.40', 'expense', '2025-11-15', 'Electricity and gas'],
            [1, 4, '61.35', 'expense', '2025-11-17', 'Groceries'],
            [1, 14, '29.99', 'expense', '2025-11-18', 'Gym membership'],
            [1, 11, '89.99', 'expense', '2025-11-22', 'Winter jacket'],
            [1, 4, '43.10', 'expense', '2025-11-24', 'Groceries'],
            [1, 8, '19.75', 'expense', '2025-11-26', 'Lunch out'],
            [1, 1, '2450.00', 'income', '2025-11-28', 'Monthly salary'],
            [1, 2, '180.00', 'income', '2025-11-30', 'Freelance logo design'],
            [1, 5, '780.00', 'expense', '2025-12-01', 'Monthly rent'],
            [1, 4, '58.40', 'expense', '2025-12-02', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2025-12-04', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2025-12-07', 'Netflix subscription'],
            [1, 4, '49.90', 'expense', '2025-12-09', 'Groceries'],
            [1, 8, '35.20', 'expense', '2025-12-12', 'Christmas dinner out'],
            [1, 7, '128.60', 'expense', '2025-12-14', 'Electricity and gas'],
            [1, 4, '66.25', 'expense', '2025-12-16', 'Groceries'],
            [1, 14, '29.99', 'expense', '2025-12-18', 'Gym membership'],
            [1, 11, '145.50', 'expense', '2025-12-20', 'Christmas gifts'],
            [1, 9, '24.00', 'expense', '2025-12-21', 'Cinema tickets'],
            [1, 11, '78.25', 'expense', '2025-12-22', 'Gifts for family'],
            [1, 4, '52.30', 'expense', '2025-12-27', 'Groceries'],
            [1, 1, '2450.00', 'income', '2025-12-28', 'Monthly salary'],
            [1, 10, '18.40', 'expense', '2025-12-29', 'Pharmacy'],
            [1, 15, '90.00', 'income', '2025-12-30', 'Blog advert revenue'],
            [1, 5, '780.00', 'expense', '2026-01-01', 'Monthly rent'],
            [1, 4, '51.20', 'expense', '2026-01-03', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2026-01-05', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2026-01-07', 'Netflix subscription'],
            [1, 4, '44.75', 'expense', '2026-01-10', 'Groceries'],
            [1, 7, '134.20', 'expense', '2026-01-14', 'Electricity and gas (winter)'],
            [1, 4, '59.60', 'expense', '2026-01-16', 'Groceries'],
            [1, 14, '29.99', 'expense', '2026-01-18', 'Gym membership'],
            [1, 12, '45.00', 'expense', '2026-01-20', 'Online SQL course'],
            [1, 8, '22.40', 'expense', '2026-01-23', 'Lunch out'],
            [1, 4, '48.30', 'expense', '2026-01-26', 'Groceries'],
            [1, 1, '2450.00', 'income', '2026-01-28', 'Monthly salary'],
            [1, 2, '260.00', 'income', '2026-01-30', 'Freelance website fixes'],
            [1, 5, '780.00', 'expense', '2026-02-01', 'Monthly rent'],
            [1, 4, '46.80', 'expense', '2026-02-03', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2026-02-04', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2026-02-07', 'Netflix subscription'],
            [1, 8, '31.60', 'expense', '2026-02-09', 'Valentine\'s dinner'],
            [1, 4, '52.15', 'expense', '2026-02-12', 'Groceries'],
            [1, 7, '119.70', 'expense', '2026-02-14', 'Electricity and gas'],
            [1, 9, '32.50', 'expense', '2026-02-15', 'Concert ticket'],
            [1, 14, '29.99', 'expense', '2026-02-18', 'Gym membership'],
            [1, 10, '42.30', 'expense', '2026-02-20', 'Dentist check-up'],
            [1, 4, '55.40', 'expense', '2026-02-24', 'Groceries'],
            [1, 8, '18.90', 'expense', '2026-02-26', 'Lunch out'],
            [1, 1, '2450.00', 'income', '2026-02-27', 'Monthly salary'],
            [1, 5, '780.00', 'expense', '2026-03-01', 'Monthly rent'],
            [1, 4, '49.50', 'expense', '2026-03-03', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2026-03-05', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2026-03-07', 'Netflix subscription'],
            [1, 4, '58.20', 'expense', '2026-03-10', 'Groceries'],
            [1, 7, '104.30', 'expense', '2026-03-14', 'Electricity and gas'],
            [1, 4, '44.90', 'expense', '2026-03-17', 'Groceries'],
            [1, 14, '29.99', 'expense', '2026-03-18', 'Gym membership'],
            [1, 11, '62.40', 'expense', '2026-03-21', 'Spring clothes'],
            [1, 8, '26.75', 'expense', '2026-03-24', 'Dinner out'],
            [1, 4, '61.70', 'expense', '2026-03-26', 'Groceries'],
            [1, 3, '55.20', 'income', '2026-03-27', 'Dividend payment'],
            [1, 1, '2520.00', 'income', '2026-03-28', 'Monthly salary (pay rise)'],
            [1, 2, '320.00', 'income', '2026-03-30', 'Freelance app prototype'],
            [1, 5, '780.00', 'expense', '2026-04-01', 'Monthly rent'],
            [1, 4, '53.60', 'expense', '2026-04-02', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2026-04-04', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2026-04-07', 'Netflix subscription'],
            [1, 4, '47.25', 'expense', '2026-04-09', 'Groceries'],
            [1, 8, '38.20', 'expense', '2026-04-11', 'Birthday dinner'],
            [1, 7, '96.10', 'expense', '2026-04-14', 'Electricity and gas'],
            [1, 4, '50.80', 'expense', '2026-04-16', 'Groceries'],
            [1, 14, '29.99', 'expense', '2026-04-18', 'Gym membership'],
            [1, 6, '18.60', 'expense', '2026-04-19', 'Taxi'],
            [1, 9, '15.00', 'expense', '2026-04-21', 'Cinema'],
            [1, 10, '27.80', 'expense', '2026-04-23', 'Pharmacy'],
            [1, 4, '64.40', 'expense', '2026-04-25', 'Groceries'],
            [1, 8, '21.50', 'expense', '2026-04-26', 'Lunch out'],
            [1, 1, '2520.00', 'income', '2026-04-28', 'Monthly salary'],
            [1, 15, '150.00', 'income', '2026-04-30', 'Template sales'],
            [1, 5, '780.00', 'expense', '2026-05-01', 'Monthly rent'],
            [1, 4, '55.10', 'expense', '2026-05-04', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2026-05-05', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2026-05-07', 'Netflix subscription'],
            [1, 4, '49.35', 'expense', '2026-05-11', 'Groceries'],
            [1, 7, '88.40', 'expense', '2026-05-14', 'Electricity and gas'],
            [1, 11, '349.99', 'expense', '2026-05-16', 'New smartphone'],
            [1, 14, '29.99', 'expense', '2026-05-18', 'Gym membership'],
            [1, 12, '60.00', 'expense', '2026-05-20', 'Udemy courses'],
            [1, 8, '33.40', 'expense', '2026-05-23', 'Dinner out'],
            [1, 4, '58.75', 'expense', '2026-05-26', 'Groceries'],
            [1, 1, '2520.00', 'income', '2026-05-28', 'Monthly salary'],
            [1, 2, '410.00', 'income', '2026-05-30', 'Freelance e-commerce site'],
            [1, 5, '780.00', 'expense', '2026-06-01', 'Monthly rent'],
            [1, 4, '52.40', 'expense', '2026-06-03', 'Weekly groceries'],
            [1, 6, '64.50', 'expense', '2026-06-04', 'Monthly travel pass'],
            [1, 9, '10.99', 'expense', '2026-06-07', 'Netflix subscription'],
            [1, 4, '61.15', 'expense', '2026-06-10', 'Groceries'],
            [1, 8, '29.60', 'expense', '2026-06-12', 'Lunch out'],
            [1, 7, '79.20', 'expense', '2026-06-14', 'Electricity and gas'],
            [1, 4, '47.90', 'expense', '2026-06-17', 'Groceries'],
            [1, 14, '29.99', 'expense', '2026-06-18', 'Gym membership'],
            [1, 9, '28.00', 'expense', '2026-06-20', 'Theatre ticket'],
            [1, 8, '44.15', 'expense', '2026-06-22', 'Dinner out'],
            [1, 4, '55.30', 'expense', '2026-06-25', 'Groceries'],
            [1, 3, '58.40', 'income', '2026-06-26', 'Dividend payment'],
            [1, 1, '2520.00', 'income', '2026-06-28', 'Monthly salary'],
            [1, 5, '780.00', 'expense', '2026-07-01', 'Monthly rent'],
            [1, 6, '64.50', 'expense', '2026-07-02', 'Monthly travel pass'],
            [1, 4, '57.85', 'expense', '2026-07-03', 'Weekly groceries'],
            [1, 14, '29.99', 'expense', '2026-07-05', 'Gym membership'],
            [1, 8, '24.60', 'expense', '2026-07-06', 'Lunch out'],
            [1, 9, '10.99', 'expense', '2026-07-07', 'Netflix subscription'],
            [1, 4, '49.20', 'expense', '2026-07-07', 'Groceries'],
            [2, 1, '1900.00', 'income', '2026-06-28', 'June salary'],
            [2, 5, '650.00', 'expense', '2026-07-01', 'Rent'],
            [2, 4, '38.75', 'expense', '2026-07-02', 'Groceries'],
            [2, 6, '42.00', 'expense', '2026-07-05', 'Fuel'],
            [2, 8, '18.20', 'expense', '2026-07-06', 'Lunch'],
        ];

        $insert = [];
        foreach ($rows as [$userId, $categoryId, $amount, $type, $date, $description]) {
            $insert[] = [
                'user_id' => $userId,
                'category_id' => $categoryId,
                'amount' => $amount,
                'type' => $type,
                'transaction_date' => $date,
                'description' => $description,
                'created_at' => $date.' 12:00:00',
                'updated_at' => $date.' 12:00:00',
            ];
        }

        // Chunked bulk insert keeps the seeding fast on XAMPP
        foreach (array_chunk($insert, 60) as $chunk) {
            DB::table('transactions')->insert($chunk);
        }
    }
}
