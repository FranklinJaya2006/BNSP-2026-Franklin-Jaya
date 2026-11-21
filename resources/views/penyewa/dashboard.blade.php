@extends('layouts.app')
@section('title', 'Dashboard Penyewa')
@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Penyewa</h2>

@if(auth()->user()->status === 'pending')
    <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-4 rounded mb-6">
        ⏳ <strong>Akun Anda sedang menunggu verifikasi admin.</strong> Anda belum bisa membuat sewa baru.
    </div>
@elseif(auth()->user()->status === 'rejected')
    <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-4 rounded mb-6">
        ❌ <strong>Akun Anda ditolak.</strong>
        @if(auth()->user()->rejection_notes)
            Alasan: {{ auth()->user()->rejection_notes }}
        @endif
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('penyewa.vehicles.index') }}" class="bg-white border rounded-lg p-5 hover:shadow-md transition">
        <div class="text-3xl mb-2">🚗</div>
        <div class="font-semibold text-gray-700">Kendaraan Saya</div>
        <div class="text-sm text-gray-500 mt-1">Kelola kendaraan Anda</div>
    </a>
    <a href="{{ route('penyewa.rentals.create') }}" class="bg-blue-600 text-white border rounded-lg p-5 hover:bg-blue-700 transition {{ auth()->user()->isApproved() ? '' : 'opacity-50 pointer-events-none' }}">
        <div class="text-3xl mb-2">➕</div>
        <div class="font-semibold">Sewa Garasi Baru</div>
        <div class="text-sm opacity-80 mt-1">Pesan garasi kendaraan</div>
    </a>
    <a href="{{ route('penyewa.rentals.index') }}" class="bg-white border rounded-lg p-5 hover:shadow-md transition">
        <div class="text-3xl mb-2">📋</div>
        <div class="font-semibold text-gray-700">Riwayat Sewa</div>
        <div class="text-sm text-gray-500 mt-1">Lihat semua transaksi sewa</div>
    </a>
</div>
@endsection
