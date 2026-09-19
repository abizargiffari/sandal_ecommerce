<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => 'Sandal Store UMKM',
            'company_tagline' => 'Sandal Nyaman Kualitas Terbaik',
            'logo' => 'settings/logo.png',
            'address' => 'Jl. Contoh Raya No. 123, Depok, Jawa Barat',
            'phone' => '081234567890',
            'email' => 'halo@sandalstore.test',
            'whatsapp' => '6281234567890',
            'instagram' => 'https://instagram.com/sandalstore',
            'facebook' => 'https://facebook.com/sandalstore',
            'tiktok' => 'https://tiktok.com/@sandalstore',
            'map_lat' => '-6.4025',
            'map_lng' => '106.7942',
            'terms_and_conditions' => 'Syarat dan ketentuan pembelian akan diperbarui oleh admin.',
            'privacy_policy' => 'Kebijakan privasi akan diperbarui oleh admin.',
        ];

        foreach ($settings as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value]);
        }
    }
}
