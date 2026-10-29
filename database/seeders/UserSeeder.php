<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/** Fictional local demonstration accounts; no reusable password is bundled. */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('finance.demo_password');
        if (! is_string($password) || strlen($password) < 12
            || ! preg_match('/[A-Za-z]/', $password) || ! preg_match('/\d/', $password)) {
            throw new RuntimeException(
                'Set DEMO_PASSWORD in your private .env to at least 12 characters, including a letter and a number, before seeding.'
            );
        }

        DB::table('users')->insert([
            [
                'user_id' => 1,
                'username' => 'demo',
                'email' => 'demo@example.test',
                'password_hash' => Hash::make($password),
                'currency' => '£',
                'is_verified' => 1,
                'created_at' => '2025-11-01 10:05:00',
                'updated_at' => '2025-11-01 10:05:00',
            ],
            [
                'user_id' => 2,
                'username' => 'demo_secondary',
                'email' => 'demo_secondary@example.test',
                'password_hash' => Hash::make($password),
                'currency' => '£',
                'is_verified' => 1,
                'created_at' => '2026-06-20 09:08:00',
                'updated_at' => '2026-06-20 09:08:00',
            ],
            [
                'user_id' => 3,
                'username' => 'demo_unverified',
                'email' => 'demo_unverified@example.test',
                'password_hash' => Hash::make($password),
                'currency' => '£',
                'is_verified' => 0,
                'created_at' => '2026-06-25 14:00:00',
                'updated_at' => '2026-06-25 14:00:00',
            ],
        ]);
    }
}
