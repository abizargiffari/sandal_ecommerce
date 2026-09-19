<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const STATUSES = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['user', 'payment'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('order_number', 'like', '%' . $request->search . '%')
                        ->orWhereHas('user', function ($uq) use ($request) {
                            $uq->where('name', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->date_to);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Order::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'address', 'shippingMethod', 'coupon', 'items.productVariant.product', 'statusHistories.changedBy', 'payment.paymentMethod']);

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Update status pesanan. Setiap perubahan otomatis dicatat ke
     * order_status_histories sebagai jejak lacak pesanan.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['status'] === $order->status) {
            return back()->with('error', 'Status sudah sama, tidak ada perubahan.');
        }

        DB::transaction(function () use ($validated, $order) {
            // Jika pesanan dibatalkan, kembalikan stok yang sudah dikurangi
            if ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
                $this->restockCancelledOrder($order);
            }

            $order->update(['status' => $validated['status']]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
                'changed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function create(): View
    {
        $customers = User::where('role', 'customer')
            ->with('addresses')
            ->orderBy('name')
            ->get();

        // whereHas('product') memastikan variant dari produk yang sudah
        // dihapus (soft delete) tidak ikut muncul sebagai pilihan item pesanan.
        $variants = ProductVariant::with('product')
            ->whereHas('product')
            ->where('stock', '>', 0)
            ->get();

        $shippingMethods = ShippingMethod::active()->get();
        $paymentMethods = PaymentMethod::active()->get();

        // Data disiapkan sebagai array PHP polos di sini (bukan closure
        // bersarang di dalam @json() di Blade), supaya Blade tidak kesulitan
        // mem-parsing directive-nya dan aman langsung di-JSON-encode di view.
        $customerAddressesJson = $customers->keyBy('id')->map(function ($customer) {
            return $customer->addresses->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->label . ' - ' . $address->recipient_name . ', ' . $address->city,
                ];
            })->values();
        });

        $variantOptionsJson = $variants->map(function ($variant) {
            $colorLabel = $variant->color ? ' / ' . $variant->color : '';

            return [
                'id' => $variant->id,
                'label' => $variant->product->name . ' - Ukuran ' . $variant->size . $colorLabel . ' (stok: ' . $variant->stock . ')',
            ];
        })->values();

        return view('admin.orders.create', compact(
            'customers',
            'variants',
            'shippingMethods',
            'paymentMethods',
            'customerAddressesJson',
            'variantOptionsJson'
        ));
    }

    /**
     * Buat pesanan manual (mis. pesanan COD lewat telepon/WhatsApp),
     * dipakai selama frontend checkout customer belum tersedia.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'address_id' => ['required', 'exists:addresses,id'],
            'shipping_method_id' => ['required', 'exists:shipping_methods,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $shippingMethod = ShippingMethod::findOrFail($validated['shipping_method_id']);

            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with('product')->lockForUpdate()->findOrFail($item['product_variant_id']);

                if (! $variant->product) {
                    abort(422, 'Salah satu produk yang dipilih sudah tidak tersedia (mungkin baru saja dihapus). Silakan muat ulang halaman dan pilih ulang produknya.');
                }

                if ($variant->stock < $item['quantity']) {
                    abort(422, "Stok {$variant->product->name} (ukuran {$variant->size}) tidak mencukupi. Sisa stok: {$variant->stock}.");
                }

                $price = $variant->final_price;
                $itemsData[] = [
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $price * $item['quantity'],
                ];
                $subtotal += $price * $item['quantity'];
            }

            $total = $subtotal + $shippingMethod->base_cost;

            $order = Order::create([
                'user_id' => $validated['user_id'],
                'address_id' => $validated['address_id'],
                'shipping_method_id' => $validated['shipping_method_id'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingMethod->base_cost,
                'discount_amount' => 0,
                'total' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $data) {
                $order->items()->create([
                    'product_variant_id' => $data['variant']->id,
                    'product_name' => $data['variant']->product->name . ' - ' . $data['variant']->size,
                    'price' => $data['price'],
                    'quantity' => $data['quantity'],
                    'subtotal' => $data['subtotal'],
                ]);

                $data['variant']->decrement('stock', $data['quantity']);

                StockMovement::create([
                    'product_variant_id' => $data['variant']->id,
                    'type' => 'out',
                    'quantity' => -$data['quantity'],
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'note' => 'Stok keluar untuk pesanan ' . $order->order_number,
                    'created_by' => auth()->id(),
                ]);
            }

            $order->payment()->create([
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $total,
                'status' => 'pending',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'note' => 'Pesanan dibuat manual oleh admin/staff',
                'changed_by' => auth()->id(),
            ]);

            return $order;
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat: ' . $order->order_number);
    }

    /**
     * Tandai pembayaran pesanan sebagai lunas. Ini titik penting yang
     * menghubungkan modul Pesanan dengan Keuangan: begitu pembayaran
     * dikonfirmasi, sistem otomatis mencatat pemasukan ke finance_transactions
     * supaya laporan laba/rugi selalu sinkron dengan transaksi asli,
     * tanpa admin perlu input manual dua kali.
     */
    public function confirmPayment(Order $order): RedirectResponse
    {
        $order->load('payment');

        if (! $order->payment) {
            return back()->with('error', 'Pesanan ini belum memiliki data pembayaran.');
        }

        if ($order->payment->status === 'paid') {
            return back()->with('error', 'Pembayaran pesanan ini sudah dikonfirmasi sebelumnya.');
        }

        DB::transaction(function () use ($order) {
            $order->payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            FinanceTransaction::create([
                'type' => 'income',
                'category' => 'Penjualan',
                'amount' => $order->total,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'description' => 'Pembayaran pesanan ' . $order->order_number,
                'transaction_date' => now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Pembayaran berhasil dikonfirmasi dan tercatat sebagai pemasukan.');
    }

    /**
     * Kembalikan stok yang sudah dikurangi saat pesanan dibatalkan.
     */
    private function restockCancelledOrder(Order $order): void
    {
        foreach ($order->items as $item) {
            $item->productVariant?->increment('stock', $item->quantity);

            StockMovement::create([
                'product_variant_id' => $item->product_variant_id,
                'type' => 'in',
                'quantity' => $item->quantity,
                'reference_type' => 'order_cancelled',
                'reference_id' => $order->id,
                'note' => 'Stok dikembalikan karena pesanan ' . $order->order_number . ' dibatalkan',
                'created_by' => auth()->id(),
            ]);
        }
    }
}
