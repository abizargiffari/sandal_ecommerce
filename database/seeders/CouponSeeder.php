<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'code' => 'SANDAL10',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 50000,
            'max_discount' => 20000,
            'usage_limit' => 100,
            'used_count' => 0,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'ONGKIRGRATIS',
            'type' => 'fixed',
            'value' => 15000,
            'min_purchase' => 100000,
            'max_discount' => null,
            'usage_limit' => 50,
            'used_count' => 0,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'is_active' => true,
        ]);
    }
}
