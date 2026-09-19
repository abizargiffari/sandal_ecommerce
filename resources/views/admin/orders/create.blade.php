@extends('layouts.admin')

@section('title', 'Buat Pesanan Manual')
@section('page-title', 'Buat Pesanan Manual')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p class="text-sm text-gray-500 mb-4">
            Gunakan form ini untuk mencatat pesanan yang masuk lewat telepon/WhatsApp (mis. COD),
            selama halaman checkout customer belum tersedia.
        </p>

        <form method="POST" action="{{ route('admin.orders.store') }}" id="order-form">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Customer</label>
                <select name="user_id" id="customer-select" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">- Pilih Customer -</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">
                    Customer belum ada di daftar? Tambahkan dulu lewat Manajemen Pengguna.
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Alamat Pengiriman</label>
                <select name="address_id" id="address-select" required class="w-full border rounded px-3 py-2 text-sm">
                    <option value="">- Pilih customer terlebih dahulu -</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Metode Pengiriman</label>
                    <select name="shipping_method_id" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">- Pilih -</option>
                        @foreach ($shippingMethods as $method)
                            <option value="{{ $method->id }}">
                                {{ $method->name }} (Rp {{ number_format($method->base_cost, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Metode Pembayaran</label>
                    <select name="payment_method_id" required class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">- Pilih -</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Catatan (opsional)</label>
                <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2 text-sm"></textarea>
            </div>

            <hr class="my-6">

            <div class="mb-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium">Item Pesanan</label>
                    <button type="button" id="add-item" class="text-sm text-blue-600 hover:underline">
                        + Tambah Item
                    </button>
                </div>
                <div id="item-list" class="space-y-2"></div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Simpan Pesanan
                </button>
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <template id="item-row-template">
        <div class="item-row flex gap-2 items-center">
            <select name="items[__INDEX__][product_variant_id]" class="variant-select border rounded px-2 py-1.5 text-sm flex-1" required>
                <option value="">- Pilih Produk &amp; Ukuran -</option>
            </select>
            <input type="number" name="items[__INDEX__][quantity]" min="1" value="1" placeholder="Qty"
                class="border rounded px-2 py-1.5 text-sm w-20" required>
            <button type="button" class="remove-item text-red-600 text-sm">Hapus</button>
        </div>
    </template>

    <script>
        const customerAddresses = @json($customerAddressesJson);
        const variantOptions = @json($variantOptionsJson);

        // Dropdown alamat mengikuti customer yang dipilih
        const customerSelect = document.getElementById('customer-select');
        const addressSelect = document.getElementById('address-select');

        customerSelect.addEventListener('change', function () {
            const addresses = customerAddresses[this.value] || [];
            addressSelect.innerHTML = '';

            if (addresses.length === 0) {
                addressSelect.innerHTML = '<option value="">- Customer ini belum punya alamat -</option>';
                return;
            }

            addressSelect.innerHTML = '<option value="">- Pilih Alamat -</option>';
            addresses.forEach(function (a) {
                const opt = document.createElement('option');
                opt.value = a.id;
                opt.textContent = a.label;
                addressSelect.appendChild(opt);
            });
        });

        // Baris item pesanan dinamis
        const itemList = document.getElementById('item-list');
        const itemTemplate = document.getElementById('item-row-template');
        const addItemBtn = document.getElementById('add-item');
        let itemIndex = 0;

        function populateVariantSelect(select) {
            variantOptions.forEach(function (v) {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.label;
                select.appendChild(opt);
            });
        }

        function addItemRow() {
            const clone = itemTemplate.content.cloneNode(true);
            clone.querySelectorAll('[name]').forEach((el) => {
                el.name = el.name.replace('__INDEX__', itemIndex);
            });
            populateVariantSelect(clone.querySelector('.variant-select'));
            clone.querySelector('.remove-item').addEventListener('click', function (e) {
                e.target.closest('.item-row').remove();
            });
            itemList.appendChild(clone);
            itemIndex++;
        }

        addItemBtn.addEventListener('click', addItemRow);
        addItemRow(); // baris pertama otomatis muncul
    </script>
@endsection
