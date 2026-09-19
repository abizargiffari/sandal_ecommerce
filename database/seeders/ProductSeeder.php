<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $jepit = Category::where('slug', 'sandal-jepit')->first();
        $gunung = Category::where('slug', 'sandal-gunung')->first();
        $selop = Category::where('slug', 'sandal-selop')->first();
        $anak = Category::where('slug', 'sandal-anak')->first();
        $wanita = Category::where('slug', 'sandal-wanita')->first();

        $products = [
            [
                'category_id' => $jepit->id,
                'name' => 'Sandal Jepit Karet Klasik',
                'price' => 25000,
                'discount_price' => null,
                'weight' => 150,
                'description' => 'Sandal jepit karet dengan bahan lentur dan nyaman dipakai sehari-hari. Tahan air dan tidak licin.',
                'is_featured' => true,
                'sizes' => ['38', '39', '40', '41', '42'],
            ],
            [
                'category_id' => $jepit->id,
                'name' => 'Sandal Jepit Motif Batik',
                'price' => 35000,
                'discount_price' => 29000,
                'weight' => 160,
                'description' => 'Sandal jepit dengan motif batik khas Indonesia, cocok untuk oleh-oleh maupun pemakaian harian.',
                'is_featured' => true,
                'sizes' => ['37', '38', '39', '40'],
            ],
            [
                'category_id' => $gunung->id,
                'name' => 'Sandal Gunung Outdoor Trekking',
                'price' => 120000,
                'discount_price' => 99000,
                'weight' => 400,
                'description' => 'Sandal gunung dengan sol anti-slip, cocok untuk aktivitas outdoor, mendaki, dan traveling.',
                'is_featured' => true,
                'sizes' => ['39', '40', '41', '42', '43'],
            ],
            [
                'category_id' => $gunung->id,
                'name' => 'Sandal Gunung Strap Adjustable',
                'price' => 135000,
                'discount_price' => null,
                'weight' => 420,
                'description' => 'Sandal gunung dengan tali strap yang bisa disesuaikan, memberi kenyamanan ekstra saat trekking.',
                'is_featured' => false,
                'sizes' => ['40', '41', '42', '43', '44'],
            ],
            [
                'category_id' => $selop->id,
                'name' => 'Sandal Selop Kulit Pria',
                'price' => 85000,
                'discount_price' => null,
                'weight' => 300,
                'description' => 'Sandal selop berbahan kulit sintetis berkualitas, tampilan elegan cocok untuk acara santai maupun formal.',
                'is_featured' => false,
                'sizes' => ['39', '40', '41', '42', '43'],
            ],
            [
                'category_id' => $selop->id,
                'name' => 'Sandal Selop Anyaman Bali',
                'price' => 95000,
                'discount_price' => 79000,
                'weight' => 280,
                'description' => 'Sandal selop dengan anyaman khas Bali, ringan dan bergaya etnik.',
                'is_featured' => true,
                'sizes' => ['38', '39', '40', '41'],
            ],
            [
                'category_id' => $anak->id,
                'name' => 'Sandal Anak Karakter Kartun',
                'price' => 40000,
                'discount_price' => null,
                'weight' => 120,
                'description' => 'Sandal anak dengan gambar karakter kartun favorit, bahan empuk dan aman untuk kaki anak.',
                'is_featured' => false,
                'sizes' => ['28', '29', '30', '31', '32'],
            ],
            [
                'category_id' => $anak->id,
                'name' => 'Sandal Anak Velcro Strap',
                'price' => 45000,
                'discount_price' => 39000,
                'weight' => 130,
                'description' => 'Sandal anak dengan perekat velcro yang mudah dipakai sendiri oleh anak, anti slip.',
                'is_featured' => false,
                'sizes' => ['26', '27', '28', '29', '30'],
            ],
            [
                'category_id' => $wanita->id,
                'name' => 'Sandal Wanita Hak Wedges',
                'price' => 110000,
                'discount_price' => 95000,
                'weight' => 350,
                'description' => 'Sandal wedges wanita dengan desain modern, nyaman dipakai untuk aktivitas sehari-hari maupun santai.',
                'is_featured' => true,
                'sizes' => ['36', '37', '38', '39'],
            ],
            [
                'category_id' => $wanita->id,
                'name' => 'Sandal Wanita Flat Casual',
                'price' => 65000,
                'discount_price' => null,
                'weight' => 200,
                'description' => 'Sandal flat casual wanita, ringan dan simpel, cocok dipadukan dengan berbagai outfit.',
                'is_featured' => false,
                'sizes' => ['36', '37', '38', '39', '40'],
            ],
        ];

        foreach ($products as $index => $data) {
            $slug = Str::slug($data['name']);
            $sku = 'SDL-' . strtoupper(Str::random(6));

            $product = Product::create([
                'category_id' => $data['category_id'],
                'sku' => $sku,
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'],
                'price' => $data['price'],
                'discount_price' => $data['discount_price'],
                'weight' => $data['weight'],
                'is_active' => true,
                'is_featured' => $data['is_featured'],
                'meta_title' => $data['name'],
                'meta_description' => Str::limit($data['description'], 150),
            ]);

            // Gambar produk (placeholder path — ganti dengan file asli di storage/app/public/products)
            $product->images()->create([
                'image_path' => 'products/' . $slug . '-1.jpg',
                'is_primary' => true,
                'sort_order' => 1,
            ]);
            $product->images()->create([
                'image_path' => 'products/' . $slug . '-2.jpg',
                'is_primary' => false,
                'sort_order' => 2,
            ]);

            // Varian ukuran, tiap ukuran punya stok acak
            foreach ($data['sizes'] as $size) {
                $product->variants()->create([
                    'size' => $size,
                    'color' => null,
                    'sku_variant' => $sku . '-' . $size,
                    'price_adjustment' => 0,
                    'stock' => rand(5, 30),
                ]);
            }
        }
    }
}
