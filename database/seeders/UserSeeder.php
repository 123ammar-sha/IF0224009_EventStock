<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Akun Super Admin
        User::create([
            'name' => 'Ammar Shafiy',
            'email' => 'ammar@eventstock.test',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
        ]);

        // Akun Manajer Gudang
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'manager@eventstock.test',
            'password' => Hash::make('password123'),
            'role' => 'warehouse_manager',
        ]);

        // Akun Kru Lapangan
        User::create([
            'name' => 'Joko Crew',
            'email' => 'crew@eventstock.test',
            'password' => Hash::make('password123'),
            'role' => 'field_crew',
        ]);
    }
}
