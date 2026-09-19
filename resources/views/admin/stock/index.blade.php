@extends('layouts.admin')

@section('title', 'Manajemen Stok')
@section('page-title', 'Manajemen Stok')

@section('content')

    @if ($lowStockVariants->isNotEmpty())
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm font-medium text-yellow-800 mb-2">⚠ Stok menipis (≤ 5):</p>
            <ul class="text-sm text-yellow-700 space-y-1">
                @foreach ($lowStockVariants as $variant)
                    <li>
                        {{ $variant->product->name }} - Ukuran {{ $variant->size }}
                        <span class="font-medium">({{ $variant->stock }} tersisa)</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                <select name="product_id" class="border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>

                <select name="type" class="border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="in" @selected(request('type') === 'in')>Masuk</option>
                    <option value="out" @selected(request('type') === 'out')>Keluar</option>
                    <option value="adjustment" @selected(request('type') === 'adjustment')>Penyesuaian</option>
                    <option value="return" @selected(request('type') === 'return')>Retur</option>
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="border rounded px-3 py-2 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="border rounded px-3 py-2 text-sm">

                <button type="submit" class="border rounded px-3 py-2 text-sm bg-gray-50">Filter</button>
            </form>

            <a href="{{ route('admin.stock.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">
                + Penyesuaian Stok
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Jumlah</th>
                    <th class="px-4 py-3">Catatan</th>
                    <th class="px-4 py-3">Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($movements as $movement)
                    <tr>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $movement->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            {{ $movement->productVariant->product->name ?? '-' }}
                            <span class="text-gray-400 text-xs">
                                (Ukuran {{ $movement->productVariant->size ?? '-' }})
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $typeLabel = [
                                    'in' => ['Masuk', 'bg-green-50 text-green-700'],
                                    'out' => ['Keluar', 'bg-red-50 text-red-700'],
                                    'adjustment' => ['Penyesuaian', 'bg-blue-50 text-blue-700'],
                                    'return' => ['Retur', 'bg-purple-50 text-purple-700'],
                                ][$movement->type] ?? [$movement->type, 'bg-gray-100 text-gray-600'];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs {{ $typeLabel[1] }}">{{ $typeLabel[0] }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium {{ $movement->quantity < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $movement->note ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $movement->createdBy->name ?? 'Sistem' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada pergerakan stok.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $movements->links() }}
        </div>
    </div>
@endsection
