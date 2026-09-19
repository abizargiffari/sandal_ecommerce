@extends('layouts.admin')

@section('title', 'Manajemen Keuangan')
@section('page-title', 'Manajemen Keuangan')

@section('content')
    {{-- Filter rentang tanggal --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tipe</label>
            <select name="type" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua</option>
                <option value="income" @selected(request('type') === 'income')>Pemasukan</option>
                <option value="expense" @selected(request('type') === 'expense')>Pengeluaran</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Kategori</label>
            <select name="category" class="border rounded px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="border rounded px-4 py-2 text-sm bg-gray-50">Terapkan Filter</button>
    </form>

    {{-- Ringkasan laba/rugi --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Total Pemasukan</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Total Pengeluaran</p>
            <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Laba / Rugi Bersih</p>
            <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </p>
        </div>
    </div>
    <p class="text-xs text-gray-400 mb-4">
        Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
    </p>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-end">
            <a href="{{ route('admin.finance.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-800">
                + Catat Transaksi
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3">Dicatat Oleh</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($transactions as $trx)
                    <tr>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($trx->type === 'income')
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Pemasukan</span>
                            @else
                                <span class="px-2 py-1 bg-red-50 text-red-600 rounded text-xs">Pengeluaran</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $trx->category }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $trx->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $trx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $trx->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $trx->createdBy?->name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($trx->reference_type === 'manual')
                                <form method="POST" action="{{ route('admin.finance.destroy', $trx) }}"
                                      onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            @else
                                <span class="text-gray-300 text-xs" title="Tercatat otomatis dari pesanan">Otomatis</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Belum ada transaksi pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
