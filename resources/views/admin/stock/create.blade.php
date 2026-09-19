@extends('layouts.admin')

@section('title', 'Penyesuaian Stok')
@section('page-title', 'Penyesuaian Stok Manual')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.stock.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Produk</label>
                <select id="product-select" class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">- Pilih Produk -</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Varian (Ukuran)</label>
                <select name="product_variant_id" id="variant-select" required
                        class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">- Pilih produk terlebih dahulu -</option>
                </select>
                <p id="current-stock-info" class="text-xs text-gray-400 mt-1"></p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Tipe Penyesuaian</label>
                <select name="type" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="in">Stok Masuk (tambah stok)</option>
                    <option value="out">Stok Keluar (kurangi stok)</option>
                    <option value="adjustment">Penyesuaian / Stok Opname (kurangi stok)</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Jumlah</label>
                <input type="number" name="quantity" min="1" required
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
                <textarea name="note" rows="2" placeholder="Contoh: barang rusak saat pengiriman, hasil stok opname, dll."
                          class="w-full border rounded px-3 py-2 text-sm"></textarea>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Simpan Penyesuaian
                </button>
                <a href="{{ route('admin.stock.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Data produk & varian untuk dropdown dinamis, tanpa perlu AJAX --}}
    <script>
        const productData = @json($productDataJson);

        const productSelect = document.getElementById('product-select');
        const variantSelect = document.getElementById('variant-select');
        const stockInfo = document.getElementById('current-stock-info');

        productSelect.addEventListener('change', function () {
            const variants = productData[this.value] || [];
            variantSelect.innerHTML = '';
            stockInfo.textContent = '';

            if (variants.length === 0) {
                variantSelect.innerHTML = '<option value="">- Tidak ada varian -</option>';
                return;
            }

            variantSelect.innerHTML = '<option value="">- Pilih Ukuran -</option>';
            variants.forEach(function (v) {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.label + ' (stok saat ini: ' + v.stock + ')';
                opt.dataset.stock = v.stock;
                variantSelect.appendChild(opt);
            });
        });

        variantSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            stockInfo.textContent = selected.dataset.stock !== undefined
                ? 'Stok saat ini: ' + selected.dataset.stock
                : '';
        });
    </script>
@endsection
