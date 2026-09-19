@extends('layouts.admin')

@section('title', 'Manajemen Produk')
@section('page-title', 'Manajemen Produk')

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama/SKU..."
                       class="border rounded px-3 py-2 text-sm w-48">

                <select name="category_id" class="border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="border rounded px-3 py-2 text-sm bg-gray-50">Cari</button>
            </form>

            <a href="{{ route('admin.products.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">
                + Tambah Produk
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Gambar</th>
                    <th class="px-4 py-3">Nama / SKU</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Stok</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($product->primaryImage)
                                <img src="{{ Storage::url($product->primaryImage->image_path) }}"
                                     class="w-10 h-10 object-cover rounded">
                            @else
                                <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">
                                    N/A
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $product->sku }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->category->name }}</td>
                        <td class="px-4 py-3">
                            @if ($product->discount_price)
                                <p class="text-red-600 font-medium">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            @else
                                <p class="font-medium">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $stock = $product->total_stock ?? 0; @endphp
                            <span class="{{ $stock <= 5 ? 'text-red-600 font-medium' : '' }}">
                                {{ $stock }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($product->is_active)
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Aktif</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded text-xs">Nonaktif</span>
                            @endif
                            @if ($product->is_featured)
                                <span class="px-2 py-1 bg-yellow-50 text-yellow-700 rounded text-xs">Unggulan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Belum ada produk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $products->links() }}
        </div>
    </div>
@endsection
