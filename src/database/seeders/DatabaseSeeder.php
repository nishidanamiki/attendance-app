<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\AttendancesSeeder;
use Database\Seeders\BreakTimesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersSeeder::class,
            AttendancesSeeder::class,
            BreakTimesSeeder::class,
        ]);
    }
}
