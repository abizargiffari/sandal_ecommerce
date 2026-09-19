<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    /**
     * Histori semua pergerakan stok, bisa difilter per produk, tipe,
     * dan rentang tanggal. Ini adalah "buku besar" stok — dibaca saja,
     * tidak ada edit/hapus supaya jejak audit tidak bisa dimanipulasi.
     */
    public function index(Request $request): View
    {
        $movements = StockMovement::query()
            ->with(['productVariant.product', 'createdBy'])
            ->when($request->filled('product_id'), function ($query) use ($request) {
                $query->whereHas('productVariant', function ($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                });
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);

        // Ringkasan cepat: produk dengan stok menipis (<= 5) untuk peringatan admin
        $lowStockVariants = ProductVariant::with('product')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('admin.stock.index', compact('movements', 'products', 'lowStockVariants'));
    }

    /**
     * Form penyesuaian stok manual, dipisah dari form edit produk —
     * dipakai untuk kasus stok opname, barang rusak/hilang, dll.
     */
    public function create(): View
    {
        $products = Product::with(['variants' => fn ($q) => $q->orderBy('size')])
            ->orderBy('name')
            ->get();

        return view('admin.stock.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($validated['product_variant_id']);

            // 'in' menambah stok, 'out' & 'adjustment' (dianggap pengurangan) mengurangi.
            // Untuk kasus adjustment penambahan, admin cukup pilih tipe 'in'.
            if ($validated['type'] === 'in') {
                $variant->increment('stock', $validated['quantity']);
                $movementQuantity = $validated['quantity'];
            } else {
                if ($variant->stock < $validated['quantity']) {
                    abort(422, 'Jumlah pengurangan melebihi stok yang tersedia (' . $variant->stock . ').');
                }
                $variant->decrement('stock', $validated['quantity']);
                $movementQuantity = -$validated['quantity'];
            }

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'type' => $validated['type'],
                'quantity' => $movementQuantity,
                'reference_type' => 'manual',
                'note' => $validated['note'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('admin.stock.index')
            ->with('success', 'Penyesuaian stok berhasil dicatat.');
    }
}
