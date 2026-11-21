<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Penyewaan Garasi')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-700">Garasi App</h1>
        <p class="text-gray-500 text-sm mt-1">Sistem Penyewaan Garasi Kendaraan</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-lg p-8">
        @if(session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded mb-4 text-sm">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </div>
</div>
</body>
</html>
