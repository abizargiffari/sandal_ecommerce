# Product Requirements Document (PRD)
## Aplikasi E-Commerce UMKM Sandal

| | |
|---|---|
| **Versi Dokumen** | 1.0 |
| **Tanggal** | 19 September 2026 |
| **Status** | Draft — dalam pengembangan aktif |
| **Pemilik Produk** | Abizar |

---

## 1. Latar Belakang & Tujuan

### 1.1 Latar Belakang
UMKM penjual sandal membutuhkan platform e-commerce mandiri untuk memasarkan dan menjual produk secara online, tanpa bergantung sepenuhnya pada marketplace pihak ketiga. Platform ini memberi kontrol penuh atas branding, data pelanggan, dan proses bisnis (stok, keuangan, pengiriman).

### 1.2 Tujuan Produk
- Menyediakan kanal penjualan online resmi milik UMKM dengan identitas brand sendiri.
- Mempermudah pelanggan menemukan, memilih, dan membeli produk sandal secara online dengan berbagai metode pembayaran (termasuk COD).
- Memberi pemilik UMKM alat manajemen operasional terpusat: produk, stok, pesanan, keuangan, dan pelanggan — dalam satu dashboard admin.
- Menyajikan data analitik dasar untuk mendukung keputusan bisnis (produk terlaris, tren penjualan, laba/rugi).

### 1.3 Tujuan Bisnis
- Meningkatkan jangkauan pasar UMKM tanpa biaya komisi marketplace.
- Membangun basis data pelanggan (customer database) untuk strategi pemasaran jangka panjang (promo, retensi).
- Mengefisienkan pencatatan stok dan keuangan yang sebelumnya manual.

---

## 2. Target Pengguna

| Peran | Deskripsi | Kebutuhan Utama |
|---|---|---|
| **Customer** | Pembeli umum, browsing dan belanja sandal secara online | Pengalaman belanja mudah, banyak pilihan pembayaran, transparansi status pesanan |
| **Admin** | Pemilik UMKM, akses penuh ke seluruh sistem | Kontrol penuh atas produk, keuangan, pengguna, dan pengaturan sistem |
| **Staff** | Karyawan/kasir yang membantu operasional harian | Kelola pesanan, stok, dan produk — tanpa akses ke data sensitif seperti manajemen pengguna |

---

## 3. Ruang Lingkup (Scope)

### 3.1 Dalam Lingkup (In Scope)
- Website e-commerce responsif (desktop, tablet, mobile) berbasis Laravel + Blade.
- Fitur belanja lengkap: katalog, keranjang, checkout, pembayaran (gateway + COD), riwayat pesanan.
- Dashboard admin untuk manajemen produk, kategori, stok, pesanan, retur, keuangan, dan pengguna.
- Konten informasional: tentang kami, kontak, FAQ, syarat & ketentuan, kebijakan privasi, blog.
- Sistem autentikasi tunggal (satu form login) untuk seluruh role, dengan redirect otomatis sesuai role.

### 3.2 Di Luar Lingkup (Out of Scope) — untuk versi awal
- Aplikasi mobile native (iOS/Android) — saat ini hanya web responsif.
- Multi-bahasa (hanya Bahasa Indonesia di versi awal).
- Multi-gudang/multi-cabang.
- Program afiliasi/reseller.
- Live chat customer service real-time (bisa diarahkan ke WhatsApp/kontak manual dulu).

---

## 4. Peran & Hak Akses (Role-Based Access)

Sistem menggunakan **satu tabel `users`** dengan kolom `role` (`admin`, `staff`, `customer`) dan **satu form login** untuk semua peran. Redirect dan hak akses ditentukan otomatis berdasarkan role setelah login berhasil, bukan lewat form/route terpisah.

| Modul | Customer | Staff | Admin |
|---|:---:|:---:|:---:|
| Belanja (katalog, keranjang, checkout) | ✅ | – | – |
| Riwayat pesanan & akun pribadi | ✅ (miliknya sendiri) | – | – |
| Manajemen kategori | ❌ | ✅ | ✅ |
| Manajemen produk | ❌ | ✅ | ✅ |
| Manajemen stok | ❌ | ✅ | ✅ |
| Manajemen pesanan & retur | ❌ | ✅ | ✅ |
| Manajemen keuangan | ❌ | ✅ | ✅ |
| Manajemen pengguna/administrator | ❌ | ❌ | ✅ |
| Dashboard analitik | ❌ | ✅ | ✅ |

Catatan keamanan:
- Registrasi publik (`/register`) hanya bisa membuat akun dengan role `customer` — role tidak bisa dipilih dari form oleh pengguna umum.
- Akun `admin`/`staff` hanya bisa dibuat oleh admin lain melalui panel Manajemen Pengguna.
- Admin tidak bisa mengubah role atau menonaktifkan akunnya sendiri (mencegah self-lockout).

---

## 5. Kebutuhan Fungsional

### 5.1 Frontend — Halaman Publik & Customer

**Beranda**
- Banner promosi
- Grid produk & katalog
- Nama produk, harga, deskripsi singkat
- Tombol tambah ke keranjang

**Katalog & Detail Produk**
- Filter/pencarian produk per kategori
- Detail produk: gambar (multi), deskripsi, pilihan varian ukuran, harga (dengan indikator diskon jika ada)

**Keranjang & Checkout**
- Kelola item keranjang (ubah jumlah, hapus)
- Pilihan metode pengiriman
- Pilihan metode pembayaran: payment gateway (QRIS/e-wallet/virtual account) dan COD
- Ringkasan pesanan sebelum konfirmasi

**Autentikasi**
- Login (satu form untuk semua role)
- Registrasi (khusus customer)

**Halaman Informasional**
- Tentang Kami: profil UMKM, logo, penjelasan usaha, daftar produk/jasa
- Kontak Kami: form kontak, alamat, telepon, email, sosial media, peta lokasi
- FAQ & cara pemesanan
- Pilihan pengiriman & metode pembayaran (penjelasan)
- Syarat & Ketentuan, Kebijakan Privasi
- Testimoni pelanggan
- Blog/berita (termasuk artikel promosi)
- Halaman khusus Promo/Diskon

**Akun Pelanggan** (butuh login)
- Riwayat pesanan & status pembayaran
- Wishlist
- Ganti password & biodata/profil
- Pengaturan alamat pengiriman
- Pengaturan akun lainnya

### 5.2 Backend — Dashboard Admin

| Modul | Fitur |
|---|---|
| **Manajemen Produk** | Tambah, edit, hapus produk; multi-gambar; varian ukuran; SKU otomatis |
| **Manajemen Kategori** | CRUD kategori, sub-kategori, gambar kategori |
| **Manajemen Stok** | Histori pergerakan stok (masuk/keluar/penyesuaian/retur) sebagai audit trail; penyesuaian stok manual |
| **Manajemen Customer** | Lihat daftar customer, riwayat pesanan per customer |
| **Manajemen Pengguna/Administrator** | CRUD akun admin/staff, atur status aktif, reset password (khusus role admin) |
| **Manajemen Pesanan** | Lacak status pesanan (pending → diproses → dikirim → selesai/dibatalkan), histori perubahan status |
| **Manajemen Pengambilan Barang & Retur** | Proses permintaan retur, approve/reject, catat refund |
| **Manajemen Keuangan** | Pencatatan pemasukan & pengeluaran, laporan laba/rugi |
| **Dashboard Analitik & Statistik** | Ringkasan penjualan, produk terlaris, grafik tren (ditampilkan di halaman utama admin) |
| **Pengaturan Umum** | Profil UMKM, kontak, sosial media, lokasi peta, syarat & ketentuan, kebijakan privasi (key-value settings) |

---

## 6. Kebutuhan Non-Fungsional

| Kategori | Kebutuhan |
|---|---|
| **Responsif** | Wajib tampil optimal di desktop, tablet, dan mobile (mobile-first) |
| **Keamanan** | CSRF protection (bawaan Laravel), validasi input di setiap form, role hardcode di registrasi publik, password di-hash (bcrypt), proteksi self-lockout admin |
| **Audit Trail** | Semua perubahan stok tercatat di `stock_movements` dan tidak bisa diedit/dihapus setelah tercatat |
| **Integritas Data** | Snapshot harga & nama produk disimpan di `order_items`/`cart_items` agar histori pesanan tidak berubah walau data produk diedit kemudian; soft delete pada entitas penting (`users`, `products`, `categories`, `blog_posts`) |
| **Performa** | Query list menggunakan pagination; index database pada kolom yang sering difilter (status, role, kategori) |
| **Ketersediaan Pembayaran** | Minimal mendukung COD dan satu payment gateway (Midtrans/Xendit) dengan callback/webhook otomatis |

---

## 7. Tech Stack

| Layer | Teknologi |
|---|---|
| Backend Framework | Laravel (PHP) |
| Frontend Templating | Blade |
| Styling | Tailwind CSS |
| Database | MySQL |
| Payment Gateway | Midtrans / Xendit (sandbox → production) |
| Hosting (rencana) | Shared hosting cPanel atau VPS |
| Environment Development | XAMPP (lokal) |

---

## 8. Ringkasan Model Data (ERD)

Skema database terdiri dari 25 entitas utama, dikelompokkan sebagai berikut:

- **User & Auth**: `users`, `addresses`
- **Produk**: `categories`, `products`, `product_images`, `product_variants`, `stock_movements`
- **Transaksi**: `carts`, `cart_items`, `orders`, `order_items`, `order_status_histories`, `payment_methods`, `payments`, `returns`
- **Promosi**: `coupons`, `promotions`, `wishlists`
- **Keuangan**: `finance_transactions`
- **Konten/CMS**: `testimonials`, `blog_posts`, `faqs`, `contacts`, `settings`
- **Pengiriman**: `shipping_methods`

*(Detail kolom & relasi tiap tabel telah didokumentasikan terpisah dalam skema ERD dan file migration Laravel.)*

---

## 9. Alur Pengembangan (Development Roadmap)

| Fase | Deskripsi | Status |
|---|---|---|
| Fase 0 | Perencanaan fitur, wireframe, ERD | ✅ Selesai |
| Fase 1 | Setup project Laravel, package pendukung, struktur folder | ✅ Selesai |
| Fase 2 | Migration database, Model Eloquent, Seeder data awal | ✅ Selesai |
| Fase 3 | Autentikasi & Role (satu form login, middleware role) | ✅ Selesai |
| Fase 4.1 | Manajemen Kategori (admin) | ✅ Selesai |
| Fase 4.2 | Manajemen Produk (admin) | ✅ Selesai |
| Fase 4.3 | Manajemen Stok (admin) | ✅ Selesai |
| Fase 4.4 | Manajemen Pengguna (Customer + Admin/Staff) | ✅ Selesai |
| Fase 4.5 | Manajemen Pesanan | ✅ Selesai |
| Fase 4.6 | Manajemen Pengambilan Barang & Retur | ✅ Selesai |
| Fase 4.7 | Manajemen Keuangan | ✅ Selesai |
| Fase 4.8 | Dashboard Analitik & Statistik | ✅ Selesai |
| Fase 5 | Frontend Customer (beranda, katalog, checkout, akun, halaman statis) | ⏳ Berikutnya|
| Fase 6 | Integrasi Payment Gateway (Midtrans/Xendit) | ⏳ Belum dikerjakan |
| Fase 7 | Testing (fungsional, alur pembelian end-to-end, keamanan dasar) | ⏳ Belum dikerjakan |
| Fase 8 | Deployment (hosting, domain, SSL) | ⏳ Belum dikerjakan |
| Fase 9 | Maintenance (monitoring, update konten, evaluasi analitik) | ⏳ Berkelanjutan |

---

## 10. Kriteria Sukses (Success Metrics)

- Customer dapat menyelesaikan alur belanja penuh (browsing → checkout → pembayaran) tanpa error, di ketiga jenis perangkat (desktop/tablet/mobile).
- Admin dapat mengelola produk, stok, dan pesanan sepenuhnya dari dashboard tanpa perlu akses langsung ke database.
- Laporan laba/rugi di modul keuangan sinkron otomatis dengan transaksi pembayaran yang berhasil (`status = paid`).
- Tidak ada data pesanan/histori yang rusak akibat perubahan data produk atau penghapusan akun (berkat snapshot & soft delete).

---

## 11. Asumsi & Batasan

- Target awal adalah satu UMKM/satu toko (bukan platform multi-vendor).
- Pengiriman ditangani lewat metode manual yang dicatat di sistem (JNE, J&T, dll) — belum terintegrasi API real-time ongkir (RajaOngkir dsb.), bisa jadi peningkatan di fase berikutnya.
- Payment gateway menggunakan mode sandbox selama development, beralih ke mode live sebelum peluncuran resmi.
- Kapasitas trafik awal diasumsikan skala UMKM (belum dirancang untuk traffic tinggi/high-concurrency).

---

## 12. Potensi Pengembangan Selanjutnya (Future Enhancements)

- Integrasi API ongkos kirim real-time.
- Aplikasi mobile native atau PWA.
- Program loyalitas/poin pelanggan.
- Notifikasi otomatis via WhatsApp/email untuk update status pesanan.
- Multi-bahasa untuk menjangkau pembeli internasional.
- Live chat customer service.

---

## 13. Lampiran

- Skema ERD lengkap (25 tabel) — didokumentasikan terpisah.
- File migration Laravel — tersedia di `database/migrations/`.
- Model Eloquent & Seeder — tersedia di `app/Models/` dan `database/seeders/`.
