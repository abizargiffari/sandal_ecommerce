@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
    {{-- Tab role --}}
    <div class="flex gap-1 mb-4 border-b">
        @foreach (['customer' => 'Customer', 'staff' => 'Staff', 'admin' => 'Admin'] as $key => $label)
            <a href="{{ route('admin.users.index', ['role' => $key]) }}"
               class="px-4 py-2 text-sm border-b-2 {{ $role === $key ? 'border-gray-900 font-medium' : 'border-transparent text-gray-500' }}">
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex gap-2 flex-1 max-w-sm">
                <input type="hidden" name="role" value="{{ $role }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama/email..."
                       class="w-full border rounded px-3 py-2 text-sm">
            </form>

            <a href="{{ route('admin.users.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded hover:bg-gray-800 whitespace-nowrap">
                + Tambah Pengguna
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Telepon</th>
                    <th class="px-4 py-3">Role</th>
                    @if ($role === 'customer')
                        <th class="px-4 py-3">Total Pesanan</th>
                    @endif
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->phone ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs capitalize">{{ $user->role }}</span>
                        </td>
                        @if ($role === 'customer')
                            <td class="px-4 py-3">{{ $user->orders_count }}</td>
                        @endif
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Aktif</span>
                            @else
                                <span class="px-2 py-1 bg-red-50 text-red-600 rounded text-xs">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="text-blue-600 hover:underline">Edit</a>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            Belum ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $users->links() }}
        </div>
    </div>
@endsection
