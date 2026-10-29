<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** Verification and reset codes are generated at runtime, never published as fixtures. */
class UserCodeSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally empty: registration and reset flows create their own codes.
    }
}
