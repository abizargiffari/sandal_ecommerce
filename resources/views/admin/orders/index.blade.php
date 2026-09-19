@extends('layouts.admin')

@section('title', 'Manajemen Pesanan')
@section('page-title', 'Manajemen Pesanan')

@section('content')
    {{-- Tab status --}}
    <div class="flex flex-wrap gap-1 mb-4 border-b">
        <a href="{{ route('admin.orders.index') }}"
           class="px-4 py-2 text-sm border-b-2 {{ ! request('status') ? 'border-gray-900 font-medium' : 'border-transparent text-gray-500' }}">
            Semua ({{ $statusCounts->sum() }})
        </a>
        @foreach (['pending' => 'Pending', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $key => $label)
            <a href="{{ route('admin.orders.index', ['status' => $key]) }}"
               class="px-4 py-2 text-sm border-b-2 {{ request('status') === $key ? 'border-gray-900 font-medium' : 'border-transparent text-gray-500' }}">
                {{ $label }} ({{ $statusCounts[$key] ?? 0 }})
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari No. Pesanan / Nama Customer..."
                       class="border rounded px-3 py-2 text-sm w-64">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="border rounded px-3 py-2 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="border rounded px-3 py-2 text-sm">
                <button type="submit" class="border rounded px-3 py-2 text-sm bg-gray-50">Filter</button>
            </form>

            <a href="{{ route('admin.orders.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">
                + Buat Pesanan Manual
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">No. Pesanan</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Pembayaran</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                        <td class="px-4 py-3">{{ $order->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $order->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3 font-medium">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $payStatus = $order->payment?->status ?? 'pending';
                                $payLabel = ['pending' => 'Belum Bayar', 'paid' => 'Lunas', 'failed' => 'Gagal', 'refunded' => 'Refund'][$payStatus] ?? $payStatus;
                                $payColor = ['pending' => 'bg-yellow-50 text-yellow-700', 'paid' => 'bg-green-50 text-green-700', 'failed' => 'bg-red-50 text-red-700', 'refunded' => 'bg-purple-50 text-purple-700'][$payStatus] ?? 'bg-gray-100';
                            @endphp
                            <span class="px-2 py-1 rounded text-xs {{ $payColor }}">{{ $payLabel }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusLabel = ['pending' => 'Pending', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'][$order->status];
                                $statusColor = [
                                    'pending' => 'bg-gray-100 text-gray-600',
                                    'processing' => 'bg-blue-50 text-blue-700',
                                    'shipped' => 'bg-indigo-50 text-indigo-700',
                                    'completed' => 'bg-green-50 text-green-700',
                                    'cancelled' => 'bg-red-50 text-red-600',
                                ][$order->status];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs {{ $statusColor }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Belum ada pesanan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $orders->links() }}
        </div>
    </div>
@endsection
