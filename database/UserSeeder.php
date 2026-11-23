<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ሲስተም አድሚን (Admin)
        User::firstOrCreate(
            ['email' => 'admin@admas.edu.et'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. የቤተ-መጽሐፍት ኃላፊ (Department)
        User::firstOrCreate(
            ['email' => 'library@admas.edu.et'],
            [
                'name' => 'Library Head',
                'password' => Hash::make('password123'),
                'role' => 'department',
                'department' => 'Library',
            ]
        );
    }
}