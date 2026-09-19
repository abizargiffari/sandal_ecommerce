<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        // Default rentang laporan: bulan berjalan
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', now()->endOfMonth()->toDateString());

        $transactions = FinanceTransaction::query()
            ->with('createdBy')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->category);
            })
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Ringkasan laba/rugi untuk rentang tanggal yang dipilih (bukan cuma
        // halaman saat ini) — dihitung terpisah dari query paginasi di atas.
        $summaryQuery = FinanceTransaction::whereBetween('transaction_date', [$dateFrom, $dateTo]);
        $totalIncome = (clone $summaryQuery)->income()->sum('amount');
        $totalExpense = (clone $summaryQuery)->expense()->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        $categories = FinanceTransaction::distinct()->pluck('category');

        return view('admin.finance.index', compact(
            'transactions',
            'dateFrom',
            'dateTo',
            'totalIncome',
            'totalExpense',
            'netProfit',
            'categories'
        ));
    }

    public function create(): View
    {
        $commonCategories = [
            'income' => ['Penjualan', 'Pendapatan Lain-lain'],
            'expense' => ['Operasional', 'Gaji', 'Bahan Baku', 'Pemasaran', 'Sewa', 'Lain-lain'],
        ];

        return view('admin.finance.create', compact('commonCategories'));
    }

    /**
     * Catat transaksi manual — terutama untuk pengeluaran (operasional, gaji,
     * dll). Pemasukan dari penjualan idealnya tercatat otomatis lewat
     * konfirmasi pembayaran pesanan (lihat OrderController::confirmPayment),
     * tapi form ini tetap dibuka untuk pemasukan lain di luar penjualan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'transaction_date' => ['required', 'date'],
        ]);

        FinanceTransaction::create([
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'reference_type' => 'manual',
            'description' => $validated['description'] ?? null,
            'transaction_date' => $validated['transaction_date'],
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.finance.index')
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    /**
     * Hanya transaksi manual yang boleh dihapus. Transaksi otomatis dari
     * pesanan (reference_type = 'order') dikunci — kalau perlu dikoreksi,
     * harus lewat pembatalan/refund pesanan terkait, supaya data keuangan
     * tetap konsisten dengan histori pesanan yang sebenarnya.
     */
    public function destroy(FinanceTransaction $finance): RedirectResponse
    {
        if ($finance->reference_type !== 'manual') {
            return back()->with('error', 'Transaksi otomatis dari pesanan tidak bisa dihapus manual, untuk menjaga konsistensi data.');
        }

        $finance->delete();

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }
}
