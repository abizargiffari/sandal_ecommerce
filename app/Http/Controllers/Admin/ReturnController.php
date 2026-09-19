<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnController extends Controller
{
    private const STATUSES = ['requested', 'approved', 'rejected', 'completed'];

    public function index(Request $request): View
    {
        $returns = ReturnRequest::query()
            ->with(['order.user', 'orderItem.productVariant.product'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = ReturnRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.returns.index', compact('returns', 'statusCounts'));
    }

    /**
     * Form ajukan retur baru — dipakai admin/staff untuk mencatat permintaan
     * retur yang masuk lewat telepon/WhatsApp, sama seperti pola pesanan manual.
     */
    public function create(): View
    {
        // Hanya pesanan yang sudah dikirim/selesai yang masuk akal untuk diretur.
        $orders = Order::whereIn('status', ['shipped', 'completed'])
            ->with('items.productVariant.product')
            ->latest()
            ->get();

        $ordersJson = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'label' => $order->order_number . ' - ' . ($order->user?->name ?? 'Customer'),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'label' => $item->product_name . ' (Qty: ' . $item->quantity . ')',
                    ];
                })->values(),
            ];
        })->values();

        return view('admin.returns.create', compact('orders', 'ordersJson'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'order_item_id' => ['required', 'exists:order_items,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        ReturnRequest::create([
            'order_id' => $validated['order_id'],
            'order_item_id' => $validated['order_item_id'],
            'reason' => $validated['reason'],
            'status' => 'requested',
        ]);

        return redirect()
            ->route('admin.returns.index')
            ->with('success', 'Permintaan retur berhasil dicatat.');
    }

    /**
     * Ubah status retur. Saat status menjadi 'completed', stok barang
     * dikembalikan otomatis ke product_variants + dicatat ke stock_movements
     * (tipe 'return') sebagai audit trail.
     */
    public function updateStatus(Request $request, ReturnRequest $return): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validated['status'] === $return->status) {
            return back()->with('error', 'Status sudah sama, tidak ada perubahan.');
        }

        DB::transaction(function () use ($validated, $return) {
            // Restock hanya dilakukan sekali, saat transisi MENUJU 'completed'
            // dari status lain — mencegah stok bertambah dobel kalau status
            // di-toggle bolak-balik.
            if ($validated['status'] === 'completed' && $return->status !== 'completed') {
                $this->restockReturnedItem($return);
            }

            $return->update([
                'status' => $validated['status'],
                'refund_amount' => $validated['refund_amount'] ?? $return->refund_amount,
                'processed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Status retur berhasil diperbarui.');
    }

    private function restockReturnedItem(ReturnRequest $return): void
    {
        $orderItem = $return->orderItem;
        $variant = $orderItem->productVariant;

        if (! $variant) {
            return; // varian/produk sudah dihapus, tidak ada yang bisa di-restock
        }

        $variant->increment('stock', $orderItem->quantity);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'type' => 'return',
            'quantity' => $orderItem->quantity,
            'reference_type' => 'return',
            'reference_id' => $return->id,
            'note' => 'Stok masuk kembali dari retur pesanan ' . $return->order->order_number,
            'created_by' => auth()->id(),
        ]);
    }
}
