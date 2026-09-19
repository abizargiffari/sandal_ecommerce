@extends('layouts.admin')

@section('title', 'Ajukan Retur')
@section('page-title', 'Ajukan Retur')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p class="text-sm text-gray-500 mb-4">
            Hanya pesanan berstatus "Dikirim" atau "Selesai" yang bisa diajukan retur.
        </p>

        <form method="POST" action="{{ route('admin.returns.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Pesanan</label>
                <select id="order-select" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">- Pilih Pesanan -</option>
                    @foreach ($orders as $order)
                        <option value="{{ $order->id }}">
                            {{ $order->order_number }} - {{ $order->user?->name ?? 'Customer' }}
                        </option>
                    @endforeach
                </select>
                @if ($orders->isEmpty())
                    <p class="text-xs text-red-500 mt-1">
                        Belum ada pesanan dengan status "Dikirim"/"Selesai" yang bisa diretur.
                    </p>
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Item yang Diretur</label>
                <select name="order_item_id" id="item-select" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">- Pilih pesanan terlebih dahulu -</option>
                </select>
                <input type="hidden" name="order_id" id="order-id-input">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Alasan Retur</label>
                <textarea name="reason" rows="3" required placeholder="Contoh: ukuran tidak sesuai, barang cacat, dll."
                          class="w-full border rounded px-3 py-2 text-sm"></textarea>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Ajukan Retur
                </button>
                <a href="{{ route('admin.returns.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        const ordersData = @json($ordersJson->keyBy('id'));

        const orderSelect = document.getElementById('order-select');
        const itemSelect = document.getElementById('item-select');
        const orderIdInput = document.getElementById('order-id-input');

        orderSelect.addEventListener('change', function () {
            const order = ordersData[this.value];
            itemSelect.innerHTML = '';
            orderIdInput.value = this.value;

            if (! order || order.items.length === 0) {
                itemSelect.innerHTML = '<option value="">- Tidak ada item -</option>';
                return;
            }

            itemSelect.innerHTML = '<option value="">- Pilih Item -</option>';
            order.items.forEach(function (item) {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.label;
                itemSelect.appendChild(opt);
            });
        });
    </script>
@endsection
