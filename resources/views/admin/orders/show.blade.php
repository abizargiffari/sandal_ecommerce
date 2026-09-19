@extends('layouts.admin')

@section('title', 'Detail Pesanan')
@section('page-title', 'Detail Pesanan ' . $order->order_number)

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom kiri: item & alamat --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-medium mb-3">Item Pesanan</h2>
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500 border-b">
                        <tr>
                            <th class="py-2">Produk</th>
                            <th class="py-2">Harga</th>
                            <th class="py-2">Qty</th>
                            <th class="py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-2">{{ $item->product_name }}</td>
                                <td class="py-2">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="py-2">{{ $item->quantity }}</td>
                                <td class="py-2 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t mt-3 pt-3 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ongkos Kirim ({{ $order->shippingMethod?->name ?? '-' }})</span>
                        <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    @if ($order->discount_amount > 0)
                        <div class="flex justify-between text-red-600">
                            <span>Diskon @if($order->coupon)({{ $order->coupon->code }})@endif</span>
                            <span>- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-semibold text-base pt-1">
                        <span>Total</span>
                        <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-medium mb-3">Alamat Pengiriman</h2>
                <p class="text-sm">{{ $order->address?->recipient_name ?? '-' }}</p>
                <p class="text-sm text-gray-500">{{ $order->address?->phone ?? '-' }}</p>
                <p class="text-sm text-gray-500">
                    {{ $order->address?->full_address ?? '-' }},
                    {{ $order->address?->district ?? '' }}, {{ $order->address?->city ?? '' }},
                    {{ $order->address?->province ?? '' }} {{ $order->address?->postal_code ?? '' }}
                </p>
                @if ($order->notes)
                    <p class="text-sm text-gray-500 mt-2"><span class="font-medium">Catatan:</span> {{ $order->notes }}</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-medium mb-3">Histori Status</h2>
                <div class="space-y-3">
                    @foreach ($order->statusHistories->sortByDesc('created_at') as $history)
                        <div class="text-sm border-l-2 border-gray-200 pl-3">
                            <p class="font-medium capitalize">{{ $history->status }}</p>
                            <p class="text-gray-400 text-xs">
                                {{ $history->created_at->format('d M Y H:i') }}
                                @if ($history->changedBy) oleh {{ $history->changedBy->name }} @endif
                            </p>
                            @if ($history->note)
                                <p class="text-gray-500">{{ $history->note }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Kolom kanan: info customer, pembayaran, ubah status --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-medium mb-3">Customer</h2>
                <p class="text-sm">{{ $order->user?->name ?? '-' }}</p>
                <p class="text-sm text-gray-500">{{ $order->user?->email ?? '-' }}</p>
                <p class="text-sm text-gray-500">{{ $order->user?->phone ?? '-' }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-medium mb-3">Pembayaran</h2>
                <p class="text-sm">Metode: {{ $order->payment?->paymentMethod?->name ?? '-' }}</p>
                <p class="text-sm">Status:
                    <span class="font-medium">{{ ucfirst($order->payment?->status ?? 'pending') }}</span>
                </p>
                @if ($order->payment && $order->payment->paid_at)
                    <p class="text-sm text-gray-500">Dibayar: {{ $order->payment->paid_at->format('d M Y H:i') }}</p>
                @endif

                @if ($order->payment && $order->payment->status !== 'paid')
                    <form method="POST" action="{{ route('admin.orders.confirmPayment', $order) }}" class="mt-3">
                        @csrf
                        <button type="submit"
                                onclick="return confirm('Konfirmasi pembayaran ini sudah diterima? Ini akan otomatis tercatat sebagai pemasukan di modul Keuangan.');"
                                class="w-full bg-green-600 text-white py-2 rounded text-sm hover:bg-green-700">
                            Tandai Sudah Dibayar
                        </button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-5">
                <h2 class="font-medium mb-3">Ubah Status Pesanan</h2>

                @if ($order->status === 'cancelled')
                    <p class="text-sm text-gray-400">Pesanan ini sudah dibatalkan.</p>
                @elseif ($order->status === 'completed')
                    <p class="text-sm text-gray-400">Pesanan ini sudah selesai.</p>
                @else
                    <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                        @csrf
                        <select name="status" class="w-full border rounded px-3 py-2 text-sm mb-2">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($order->status === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        <textarea name="note" rows="2" placeholder="Catatan (opsional)"
                                  class="w-full border rounded px-3 py-2 text-sm mb-2"></textarea>
                        <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded text-sm hover:bg-gray-800">
                            Perbarui Status
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-2">
                        Mengubah status menjadi "Dibatalkan" akan otomatis mengembalikan stok produk.
                    </p>
                @endif
            </div>

            <a href="{{ route('admin.orders.index') }}" class="block text-center text-sm text-gray-500 hover:underline">
                ← Kembali ke daftar pesanan
            </a>
        </div>
    </div>
@endsection
