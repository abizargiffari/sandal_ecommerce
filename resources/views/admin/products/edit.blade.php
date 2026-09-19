@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">SKU</label>
                <input type="text" value="{{ $product->sku }}" disabled
                       class="w-full border rounded px-3 py-2 text-sm bg-gray-50 text-gray-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <select name="category_id" required class="w-full border rounded px-3 py-2 text-sm">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nama Produk</label>
                <input type="text" name="name" required value="{{ old('name', $product->name) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Deskripsi</label>
                <textarea name="description" rows="4"
                          class="w-full border rounded px-3 py-2 text-sm">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Harga Normal (Rp)</label>
                    <input type="number" name="price" min="0" required value="{{ old('price', $product->price) }}"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Harga Diskon (opsional)</label>
                    <input type="number" name="discount_price" min="0" value="{{ old('discount_price', $product->discount_price) }}"
                           class="w-full border rounded px-3 py-2 text-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Berat (gram)</label>
                <input type="number" name="weight" min="1" required value="{{ old('weight', $product->weight) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-6 flex gap-6">
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="is_active" class="mr-2" @checked(old('is_active', $product->is_active))>
                    Aktifkan produk
                </label>
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="is_featured" class="mr-2" @checked(old('is_featured', $product->is_featured))>
                    Tampilkan sebagai produk unggulan
                </label>
            </div>

            <hr class="my-6">

            {{-- Gambar existing --}}
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Gambar Saat Ini</label>
                <div class="grid grid-cols-4 gap-3">
                    @forelse ($product->images as $image)
                        <div class="border rounded p-2 text-center">
                            <img src="{{ Storage::url($image->image_path) }}"
                                 class="w-full h-20 object-cover rounded mb-1">
                            <label class="text-xs flex items-center justify-center gap-1">
                                <input type="radio" name="primary_image_id" value="{{ $image->id }}"
                                    @checked($image->is_primary)>
                                Utama
                            </label>
                            <label class="text-xs flex items-center justify-center gap-1 text-red-600">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}">
                                Hapus
                            </label>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 col-span-4">Belum ada gambar.</p>
                    @endforelse
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Tambah Gambar Baru</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full text-sm">
            </div>

            <hr class="my-6">

            {{-- Varian existing --}}
            <div class="mb-6">
                <label class="block text-sm font-medium mb-2">Varian Ukuran & Stok</label>
                <div class="space-y-2">
                    @foreach ($product->variants as $variant)
                        <div class="flex gap-2 items-center">
                            <span class="border rounded px-2 py-1.5 text-sm w-28 bg-gray-50">
                                {{ $variant->size }}{{ $variant->color ? ' / ' . $variant->color : '' }}
                            </span>
                            <input type="number" min="0"
                                   name="variants[{{ $variant->id }}][stock]"
                                   value="{{ old('variants.' . $variant->id . '.stock', $variant->stock) }}"
                                   class="border rounded px-2 py-1.5 text-sm w-24">
                            <label class="text-xs flex items-center gap-1 text-red-600">
                                <input type="checkbox" name="variants[{{ $variant->id }}][delete]" value="1">
                                Hapus varian ini
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tambah varian baru --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium">Tambah Ukuran Baru</label>
                    <button type="button" id="add-variant" class="text-sm text-blue-600 hover:underline">
                        + Tambah Ukuran
                    </button>
                </div>
                <div id="variant-list" class="space-y-2"></div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Perbarui Produk
                </button>
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <template id="variant-row-template">
        <div class="variant-row flex gap-2 items-center">
            <input type="text" placeholder="Ukuran (mis. 40)" name="new_variants[__INDEX__][size]"
                   class="border rounded px-2 py-1.5 text-sm w-28">
            <input type="text" placeholder="Warna (opsional)" name="new_variants[__INDEX__][color]"
                   class="border rounded px-2 py-1.5 text-sm w-32">
            <input type="number" placeholder="Stok" min="0" name="new_variants[__INDEX__][stock]"
                   class="border rounded px-2 py-1.5 text-sm w-24">
            <button type="button" class="remove-variant text-red-600 text-sm">Hapus</button>
        </div>
    </template>

    <script>
        (function () {
            const list = document.getElementById('variant-list');
            const template = document.getElementById('variant-row-template');
            const addBtn = document.getElementById('add-variant');
            let index = 0;

            addBtn.addEventListener('click', function () {
                const clone = template.content.cloneNode(true);
                clone.querySelectorAll('[name]').forEach((el) => {
                    el.name = el.name.replace('__INDEX__', index);
                });
                clone.querySelector('.remove-variant').addEventListener('click', function (e) {
                    e.target.closest('.variant-row').remove();
                });
                list.appendChild(clone);
                index++;
            });
        })();
    </script>
@endsection
