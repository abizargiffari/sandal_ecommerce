@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Analitik & Statistik')

@section('content')

    {{-- Kartu ringkasan utama --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
            <p class="text-2xl font-bold">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</p>
            @if ($revenueGrowth !== null)
                <p class="text-xs mt-1 {{ $revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $revenueGrowth >= 0 ? '▲' : '▼' }} {{ abs($revenueGrowth) }}% dari bulan lalu
                </p>
            @else
                <p class="text-xs mt-1 text-gray-400">Belum ada data pembanding</p>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Pesanan Bulan Ini</p>
            <p class="text-2xl font-bold">{{ $ordersThisMonth }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Total Customer</p>
            <p class="text-2xl font-bold">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Produk Stok Menipis</p>
            <p class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-yellow-600' : '' }}">{{ $lowStockCount }}</p>
            @if ($lowStockCount > 0)
                <a href="{{ route('admin.stock.index') }}" class="text-xs text-blue-600 hover:underline">Lihat detail →</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Grafik tren penjualan --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-5">
            <h2 class="font-medium mb-3">Tren Penjualan 14 Hari Terakhir</h2>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        {{-- Distribusi status pesanan --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-medium mb-3">Status Pesanan</h2>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Produk terlaris --}}
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-medium mb-3">Produk Terlaris</h2>
            @forelse ($topProducts as $product)
                <div class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
                    <div>
                        <p class="font-medium">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->total_qty }} terjual</p>
                    </div>
                    <p class="font-medium">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada data penjualan.</p>
            @endforelse
        </div>

        {{-- Pesanan terbaru --}}
        <div class="bg-white rounded-lg shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-medium">Pesanan Terbaru</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            @forelse ($recentOrders as $order)
                <a href="{{ route('admin.orders.show', $order) }}"
                   class="flex justify-between items-center py-2 border-b last:border-0 text-sm hover:bg-gray-50 -mx-2 px-2 rounded">
                    <div>
                        <p class="font-medium">{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $order->user?->name ?? '-' }} · {{ $order->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 bg-gray-100 rounded capitalize">{{ $order->status }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-400">Belum ada pesanan.</p>
            @endforelse
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Grafik tren penjualan (line chart)
        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: @json($salesChartLabels),
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: @json($salesChartData),
                    borderColor: '#111827',
                    backgroundColor: 'rgba(17, 24, 39, 0.08)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } },
            },
        });

        // Grafik distribusi status pesanan (doughnut chart)
        const statusLabels = @json($orderStatusCounts->keys());
        const statusData = @json($orderStatusCounts->values());
        const statusLabelMap = {
            pending: 'Pending', processing: 'Diproses', shipped: 'Dikirim',
            completed: 'Selesai', cancelled: 'Dibatalkan',
        };

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(s => statusLabelMap[s] || s),
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#9CA3AF', '#3B82F6', '#6366F1', '#10B981', '#EF4444'],
                }],
            },
            options: {
                plugins: { legend: { position: 'bottom' } },
            },
        });
    </script>
@endsection
