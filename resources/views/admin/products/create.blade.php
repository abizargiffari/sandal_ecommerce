@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category_id" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">- Pilih Kategori -</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nama Produk</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                       class="w-full border rounded px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">SKU akan dibuat otomatis oleh sistem.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="description" rows="4"
                          class="w-full border rounded px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Harga Normal (Rp)</label>
                    <input type="number" name="price" min="0" required value="{{ old('price') }}"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Harga Diskon (opsional)</label>
                    <input type="number" name="discount_price" min="0" value="{{ old('discount_price') }}"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Berat (gram)</label>
                <input type="number" name="weight" min="1" required value="{{ old('weight', 200) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Dipakai untuk menghitung ongkos kirim.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Gambar Produk (bisa lebih dari satu)</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-sm">
                <p class="text-xs text-gray-400 mt-1">Gambar pertama otomatis jadi gambar utama. Maks 2MB/gambar.</p>
            </div>

            <div class="mb-4 flex gap-6">
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="is_active" class="mr-2" @checked(old('is_active', true))>
                    Aktifkan produk
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="is_featured" class="mr-2" @checked(old('is_featured'))>
                    Tampilkan sebagai produk unggulan
                </label>
            </div>

            <hr class="my-6">

            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium">Varian Ukuran & Stok</label>
                    <button type="button" id="add-variant" class="text-sm text-blue-600 hover:underline">
                        + Tambah Ukuran
                    </button>
                </div>

                <div id="variant-list" class="space-y-2">
                    {{-- baris varian akan ditambahkan di sini oleh JS --}}
                </div>
                <p class="text-xs text-gray-400 mt-1">Tambahkan minimal satu ukuran beserta stok awalnya.</p>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Simpan Produk
                </button>
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Template baris varian, di-clone lewat JS --}}
    <template id="variant-row-template">
        <div class="variant-row flex gap-2 items-center">
            <input type="text" placeholder="Ukuran (mis. 40)" name="variants[__INDEX__][size]"
                   class="border rounded px-2 py-1.5 text-sm w-28" required>
            <input type="text" placeholder="Warna (opsional)" name="variants[__INDEX__][color]"
                   class="border rounded px-2 py-1.5 text-sm w-32">
            <input type="number" placeholder="Stok" min="0" name="variants[__INDEX__][stock]"
                   class="border rounded px-2 py-1.5 text-sm w-24" required>
            <button type="button" class="remove-variant text-red-600 text-sm">Hapus</button>
        </div>
    </template>

    <script>
        (function () {
            const list = document.getElementById('variant-list');
            const template = document.getElementById('variant-row-template');
            const addBtn = document.getElementById('add-variant');
            let index = 0;

            function addRow() {
                const clone = template.content.cloneNode(true);
                clone.querySelectorAll('[name]').forEach((el) => {
                    el.name = el.name.replace('__INDEX__', index);
                });
                clone.querySelector('.remove-variant').addEventListener('click', function (e) {
                    e.target.closest('.variant-row').remove();
                });
                list.appendChild(clone);
                index++;
            }

            addBtn.addEventListener('click', addRow);

            // Tambahkan satu baris kosong secara default
            addRow();
        })();
    </script>
@endsection
