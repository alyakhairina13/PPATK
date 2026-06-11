<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create AdminStaff user
        User::create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'nama_lengkap' => 'Admin Utama',
            'role' => 'AdminStaff',
        ]);

        // Create Notaris user
        User::create([
            'username' => 'notaris',
            'password' => Hash::make('password'),
            'nama_lengkap' => 'Wiga Angraini',
            'role' => 'Notaris',
        ]);
    }
}
