<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Akun Saya - Sandal Store</title>
</head>
<body>
    <h1>Akun Saya</h1>
    <p>Halo, {{ auth()->user()->name }}</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
    {{-- TODO: diganti dengan layout customer lengkap (riwayat pesanan, wishlist, dll) --}}
</body>
</html>
