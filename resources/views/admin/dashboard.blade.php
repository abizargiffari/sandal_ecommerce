@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Total Kategori</p>
            <p class="text-2xl font-bold">{{ \App\Models\Category::count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Total Produk</p>
            <p class="text-2xl font-bold">{{ \App\Models\Product::count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Total Pesanan</p>
            <p class="text-2xl font-bold">{{ \App\Models\Order::count() }}</p>
        </div>
    </div>

    <p class="text-sm text-gray-500 mt-6">
        Statistik dan grafik lengkap (penjualan, produk terlaris, dll) akan ditambahkan
        di fase Dashboard Analitik & Statistik.
    </p>
@endsection
