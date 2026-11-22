@extends('layouts.app')
@section('title', 'Dashboard Penyewa')
@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Penyewa</h2>

@if(auth()->user()->status === 'pending')
    <div class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-4 rounded-lg mb-6 text-sm">
        <strong>Akun Anda sedang menunggu verifikasi admin.</strong> Anda belum bisa membuat sewa baru.
    </div>
@elseif(auth()->user()->status === 'rejected')
    <div class="bg-gray-50 border border-gray-200 text-gray-600 px-4 py-4 rounded-lg mb-6 text-sm">
        <strong>Akun Anda ditolak.</strong>
        @if(auth()->user()->rejection_notes)
            Alasan: {{ auth()->user()->rejection_notes }}
        @endif
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('penyewa.vehicles.index') }}" class="bg-white border border-gray-100 rounded-lg p-5 hover:bg-gray-50 transition">
        <div class="font-medium text-gray-700">Kendaraan Saya</div>
        <div class="text-sm text-gray-500 mt-1">Kelola kendaraan Anda</div>
    </a>
    <a href="{{ route('penyewa.rentals.create') }}" class="bg-blue-600 text-white border border-gray-100 rounded-lg p-5 hover:bg-blue-700 transition {{ auth()->user()->isApproved() ? '' : 'opacity-50 pointer-events-none' }}">
        <div class="font-medium">Sewa Garasi Baru</div>
        <div class="text-sm opacity-80 mt-1">Pesan garasi kendaraan</div>
    </a>
    <a href="{{ route('penyewa.rentals.index') }}" class="bg-white border border-gray-100 rounded-lg p-5 hover:bg-gray-50 transition">
        <div class="font-medium text-gray-700">Riwayat Sewa</div>
        <div class="text-sm text-gray-500 mt-1">Lihat semua transaksi sewa</div>
    </a>
</div>
@endsection
