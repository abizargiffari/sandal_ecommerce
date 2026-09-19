@extends('layouts.admin')

@section('title', 'Catat Transaksi')
@section('page-title', 'Catat Transaksi Keuangan')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p class="text-sm text-gray-500 mb-4">
            Gunakan form ini untuk mencatat pengeluaran (operasional, gaji, dll) atau pemasukan
            di luar penjualan. Pemasukan dari penjualan tercatat otomatis saat pembayaran
            pesanan dikonfirmasi di halaman detail pesanan.
        </p>

        <form method="POST" action="{{ route('admin.finance.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Tipe Transaksi</label>
                <select name="type" id="type-select" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="expense" @selected(old('type', 'expense') === 'expense')>Pengeluaran</option>
                    <option value="income" @selected(old('type') === 'income')>Pemasukan</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Kategori</label>
                <input type="text" name="category" list="category-list" required
                       value="{{ old('category') }}"
                       class="w-full border rounded px-3 py-2 text-sm">
                <datalist id="category-list"></datalist>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Jumlah (Rp)</label>
                <input type="number" name="amount" min="0" step="0.01" required value="{{ old('amount') }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Tanggal Transaksi</label>
                <input type="date" name="transaction_date" required
                       value="{{ old('transaction_date', now()->toDateString()) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Deskripsi (opsional)</label>
                <textarea name="description" rows="2"
                          class="w-full border rounded px-3 py-2 text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Simpan
                </button>
                <a href="{{ route('admin.finance.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        const commonCategories = @json($commonCategories);

        const typeSelect = document.getElementById('type-select');
        const categoryList = document.getElementById('category-list');

        function refreshCategoryOptions() {
            const options = commonCategories[typeSelect.value] || [];
            categoryList.innerHTML = '';
            options.forEach(function (cat) {
                const opt = document.createElement('option');
                opt.value = cat;
                categoryList.appendChild(opt);
            });
        }

        typeSelect.addEventListener('change', refreshCategoryOptions);
        refreshCategoryOptions();
    </script>
@endsection
