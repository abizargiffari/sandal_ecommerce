<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Bayar di Tempat (COD)', 'type' => 'cod'],
            ['name' => 'Transfer Bank Manual', 'type' => 'manual'],
            ['name' => 'QRIS / E-Wallet (Midtrans)', 'type' => 'gateway'],
            ['name' => 'Virtual Account (Midtrans)', 'type' => 'gateway'],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method + ['is_active' => true]);
        }
    }
}
