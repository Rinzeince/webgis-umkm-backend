<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Admin Account
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Default Editor Account
        User::updateOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Editor Konten',
                'password' => Hash::make('password'),
                'role' => 'editor',
                'email_verified_at' => now(),
            ]
        );
    }
}
