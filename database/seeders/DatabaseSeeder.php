<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Populate an empty development database with fictional demonstration data. */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            TransactionSeeder::class,
            BudgetSeeder::class,
            UserCodeSeeder::class,
        ]);
    }
}
