<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Department Head', 'email' => 'dept@admas.edu.et', 'role' => 'department_head', 'department' => 'Computer Science'],
            ['name' => 'Library Officer', 'email' => 'library@admas.edu.et', 'role' => 'library', 'department' => 'Library'],
            ['name' => 'Store Officer', 'email' => 'store@admas.edu.et', 'role' => 'store', 'department' => 'University Store'],
            ['name' => 'Finance Officer', 'email' => 'finance@admas.edu.et', 'role' => 'finance', 'department' => 'Finance'],
            ['name' => 'Registrar Officer', 'email' => 'registrar@admas.edu.et', 'role' => 'registrar', 'department' => 'Registrar'],
            ['name' => 'System Administrator', 'email' => 'admin@admas.edu.et', 'role' => 'admin', 'department' => 'ICT / Administration'],
        ];

        foreach ($accounts as $acc) {
            User::updateOrCreate(
                ['email' => $acc['email']],
                [
                    'name' => $acc['name'],
                    'department' => $acc['department'],
                    'role' => $acc['role'],
                    'password' => Hash::make('password123'),
                ]
            );
        }
    }
}