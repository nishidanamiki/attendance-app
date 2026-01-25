<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $verifiedAt = now();

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者ユーザー',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => $verifiedAt,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff1@example.com'],
            [
                'name' => 'スタッフ1',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => $verifiedAt,
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff2@example.com'],
            [
                'name' => 'スタッフ2',
                'password' => Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => $verifiedAt,
            ]
        );
    }
}
