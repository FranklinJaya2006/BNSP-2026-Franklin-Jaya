@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Admin</h2>
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <a href="{{ route('admin.users.index') }}?status=pending" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 hover:shadow-md">
        <div class="text-2xl font-bold text-yellow-700">{{ \App\Models\User::where('role','penyewa')->where('status','pending')->count() }}</div>
        <div class="text-sm text-gray-600 mt-1">Menunggu Verifikasi</div>
    </a>
    <a href="{{ route('admin.users.index') }}" class="bg-green-50 border border-green-200 rounded-lg p-4 hover:shadow-md">
        <div class="text-2xl font-bold text-green-700">{{ \App\Models\User::where('role','penyewa')->where('status','approved')->count() }}</div>
        <div class="text-sm text-gray-600 mt-1">Penyewa Aktif</div>
    </a>
    <a href="{{ route('admin.garages.index') }}" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hover:shadow-md">
        <div class="text-2xl font-bold text-blue-700">{{ \App\Models\Garage::where('is_active', true)->count() }}</div>
        <div class="text-sm text-gray-600 mt-1">Garasi Aktif</div>
    </a>
    <a href="{{ route('admin.rentals.index') }}" class="bg-purple-50 border border-purple-200 rounded-lg p-4 hover:shadow-md">
        <div class="text-2xl font-bold text-purple-700">{{ \App\Models\Rental::whereIn('status',['active','extended'])->count() }}</div>
        <div class="text-sm text-gray-600 mt-1">Sewa Berjalan</div>
    </a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <a href="{{ route('admin.users.index') }}" class="bg-white border rounded-lg p-4 hover:shadow-md flex items-center gap-3">
        <span class="text-2xl">👥</span><div><div class="font-semibold text-sm">Kelola Penyewa</div><div class="text-xs text-gray-500">Verifikasi akun pendaftar</div></div>
    </a>
    <a href="{{ route('admin.garages.index') }}" class="bg-white border rounded-lg p-4 hover:shadow-md flex items-center gap-3">
        <span class="text-2xl">🏠</span><div><div class="font-semibold text-sm">Kelola Garasi</div><div class="text-xs text-gray-500">Tambah & atur unit garasi</div></div>
    </a>
    <a href="{{ route('admin.rentals.index') }}" class="bg-white border rounded-lg p-4 hover:shadow-md flex items-center gap-3">
        <span class="text-2xl">📋</span><div><div class="font-semibold text-sm">Semua Transaksi</div><div class="text-xs text-gray-500">Kelola sewa & perpanjangan</div></div>
    </a>
    <a href="{{ route('admin.reports.index') }}" class="bg-white border rounded-lg p-4 hover:shadow-md flex items-center gap-3">
        <span class="text-2xl">📊</span><div><div class="font-semibold text-sm">Laporan</div><div class="text-xs text-gray-500">Statistik & filter transaksi</div></div>
    </a>
</div>
@endsection
