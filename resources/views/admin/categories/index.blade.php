@extends('layouts.admin')

@section('title', 'Manajemen Kategori')
@section('page-title', 'Manajemen Kategori')

@section('content')
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex items-center justify-between gap-4">
            <form method="GET" class="flex-1 max-w-sm">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kategori..."
                    class="w-full border rounded px-3 py-2 text-sm">
            </form>

            <a href="{{ route('admin.categories.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">
                + Tambah Kategori
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Gambar</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Induk</th>
                    <th class="px-4 py-3">Jumlah Produk</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($category->image)
                                <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}"
                                     class="w-10 h-10 object-cover rounded">
                            @else
                                <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs">
                                    N/A
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->parent?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $category->products_count }}</td>
                        <td class="px-4 py-3">
                            @if ($category->is_active)
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Aktif</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded text-xs">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada kategori.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $categories->links() }}
        </div>
    </div>
@endsection
