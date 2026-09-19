<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Bagaimana cara melakukan pemesanan?',
                'answer' => 'Pilih produk, tentukan ukuran, klik tambah ke keranjang, lalu lanjutkan ke proses checkout dan pilih metode pembayaran.',
            ],
            [
                'question' => 'Apakah bisa bayar di tempat (COD)?',
                'answer' => 'Bisa, kami menyediakan opsi COD untuk area yang terjangkau oleh kurir kami.',
            ],
            [
                'question' => 'Berapa lama waktu pengiriman?',
                'answer' => 'Estimasi pengiriman 1-4 hari kerja tergantung jasa kurir dan lokasi tujuan.',
            ],
            [
                'question' => 'Apakah barang bisa ditukar jika ukuran tidak sesuai?',
                'answer' => 'Bisa, silakan ajukan retur melalui halaman riwayat pesanan maksimal 3 hari setelah barang diterima.',
            ],
            [
                'question' => 'Metode pembayaran apa saja yang tersedia?',
                'answer' => 'Kami menerima COD, transfer bank manual, QRIS, e-wallet, dan virtual account.',
            ],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::create($faq + ['sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
