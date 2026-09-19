<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin utama
        User::create([
            'name' => 'Admin Sandal Store',
            'email' => 'admin@sandalstore.test',
            'phone' => '081200000001',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Staff/kasir
        User::create([
            'name' => 'Staff Gudang',
            'email' => 'staff@sandalstore.test',
            'phone' => '081200000002',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Beberapa customer contoh
        $customers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@example.test'],
            ['name' => 'Siti Aminah', 'email' => 'siti@example.test'],
            ['name' => 'Rian Pratama', 'email' => 'rian@example.test'],
        ];

        foreach ($customers as $i => $customer) {
            User::create([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => '08130000000' . ($i + 1),
                'password' => Hash::make('password'),
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
}
