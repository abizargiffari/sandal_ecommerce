<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sandal Store')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- TODO: hubungkan ke Tailwind/Bootstrap saat fase styling frontend --}}
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        @yield('content')
    </div>
</body>
</html>
