<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $startOfThisMonth = now()->startOfMonth();
        $startOfLastMonth = now()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = now()->subMonthNoOverflow()->endOfMonth();

        // --- Kartu ringkasan utama ---
        $revenueThisMonth = FinanceTransaction::income()
            ->where('transaction_date', '>=', $startOfThisMonth)
            ->sum('amount');

        $revenueLastMonth = FinanceTransaction::income()
            ->whereBetween('transaction_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('amount');

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : null; // null = tidak ada pembanding bulan lalu

        $ordersThisMonth = Order::where('created_at', '>=', $startOfThisMonth)->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts = Product::count();

        // --- Distribusi status pesanan (untuk pie/bar chart) ---
        $orderStatusCounts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // --- Tren penjualan 14 hari terakhir, dari pemasukan penjualan aktual ---
        $salesTrend = FinanceTransaction::income()
            ->where('category', 'Penjualan')
            ->where('transaction_date', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('transaction_date, SUM(amount) as total')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->pluck('total', 'transaction_date');

        // Isi tanggal yang tidak ada transaksi dengan 0, supaya grafik tetap
        // menampilkan 14 hari penuh (bukan cuma hari yang ada datanya).
        $salesChartLabels = [];
        $salesChartData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $salesChartLabels[] = now()->subDays($i)->format('d M');
            $salesChartData[] = (float) ($salesTrend[$date] ?? 0);
        }

        // --- Produk terlaris (berdasarkan qty terjual, hanya produk yang masih ada) ---
        $topProducts = DB::table('order_items')
            ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at')
            ->selectRaw('products.name, SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // --- Peringatan stok menipis ---
        $lowStockCount = ProductVariant::whereHas('product')
            ->where('stock', '<=', 5)
            ->count();

        // --- Pesanan terbaru ---
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(5)
            ->get();

        // --- Kategori dengan jumlah produk terbanyak ---
        $topCategories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'revenueThisMonth',
            'revenueGrowth',
            'ordersThisMonth',
            'totalCustomers',
            'totalProducts',
            'orderStatusCounts',
            'salesChartLabels',
            'salesChartData',
            'topProducts',
            'lowStockCount',
            'recentOrders',
            'topCategories'
        ));
    }
}
