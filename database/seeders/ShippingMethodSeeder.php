<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'JNE Reguler', 'base_cost' => 12000, 'estimation' => '2-3 hari'],
            ['name' => 'J&T Express', 'base_cost' => 11000, 'estimation' => '2-4 hari'],
            ['name' => 'SiCepat', 'base_cost' => 10000, 'estimation' => '1-3 hari'],
            ['name' => 'Ambil di Tempat (COD Toko)', 'base_cost' => 0, 'estimation' => 'Hari yang sama'],
        ];

        foreach ($methods as $method) {
            ShippingMethod::create($method + ['is_active' => true]);
        }
    }
}
