<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - Sandal Store</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- TODO: ganti ke build Tailwind lokal (bukan CDN) sebelum production --}}
</head>
<body class="bg-gray-100 min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 text-gray-200 flex-shrink-0 min-h-screen">
        <div class="p-4 text-lg font-bold border-b border-gray-700">
            Sandal Store Admin
        </div>
        <nav class="p-2 space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}"
               class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('admin.categories.index') }}"
               class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-800' : '' }}">
                Kategori
            </a>
            <a href="{{ route('admin.products.index') }}"
               class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.products.*') ? 'bg-gray-800' : '' }}">
                Produk
            </a>
            <a href="{{ route('admin.stock.index') }}"
               class="block px-3 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.stock.*') ? 'bg-gray-800' : '' }}">
                Stok
            </a>
            <a href="#" class="block px-3 py-2 rounded hover:bg-gray-800 opacity-50 cursor-not-allowed">
                Pengguna <span class="text-xs">(fase berikutnya)</span>
            </a>
            <a href="#" class="block px-3 py-2 rounded hover:bg-gray-800 opacity-50 cursor-not-allowed">
                Pesanan <span class="text-xs">(fase berikutnya)</span>
            </a>
            <a href="#" class="block px-3 py-2 rounded hover:bg-gray-800 opacity-50 cursor-not-allowed">
                Keuangan <span class="text-xs">(fase berikutnya)</span>
            </a>
        </nav>
    </aside>

    {{-- Main content --}}
    <div class="flex-1">
        <header class="bg-white shadow px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">
                    {{ auth()->user()->name }} ({{ auth()->user()->role }})
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:underline">Logout</button>
                </form>
            </div>
        </header>

        <main class="p-6">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 text-red-700 rounded text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

</body>
</html>
