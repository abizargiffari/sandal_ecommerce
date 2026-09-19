@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')

@section('content')
    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($user->id === auth()->id())
            <div class="mb-4 p-3 bg-blue-50 text-blue-700 rounded text-sm">
                Anda sedang mengedit akun sendiri — role dan status aktif tidak bisa diubah dari sini
                untuk mencegah Anda terkunci dari sistem sendiri.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">No. Telepon</label>
                <input type="text" name="phone" required value="{{ old('phone', $user->phone) }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" class="w-full border rounded px-3 py-2 text-sm"
                    @disabled($user->id === auth()->id())>
                    <option value="customer" @selected(old('role', $user->role) === 'customer')>Customer</option>
                    <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="flex items-center text-sm">
                    <input type="checkbox" name="is_active" class="mr-2"
                        @checked(old('is_active', $user->is_active))
                        @disabled($user->id === auth()->id())>
                    Akun aktif
                </label>
            </div>

            <hr class="my-4">

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Password Baru (opsional)</label>
                <input type="password" name="password"
                       class="w-full border rounded px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti password.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded hover:bg-gray-800">
                    Perbarui
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded border">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
