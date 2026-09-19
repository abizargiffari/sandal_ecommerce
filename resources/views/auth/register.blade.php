@extends('layouts.guest')

@section('title', 'Daftar Akun - Sandal Store')

@section('content')
    <div class="bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-semibold mb-6 text-center">Buat Akun Baru</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                    class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium mb-1">No. Telepon</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                    class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full border rounded px-3 py-2">
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                    class="w-full border rounded px-3 py-2">
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white py-2 rounded hover:bg-gray-800">
                Daftar
            </button>
        </form>

        <p class="text-sm text-center mt-4">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk di sini</a>
        </p>
    </div>
@endsection
