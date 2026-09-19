<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            ShippingMethodSeeder::class,
            PaymentMethodSeeder::class,
            ProductSeeder::class,
            CouponSeeder::class,
            FaqSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
