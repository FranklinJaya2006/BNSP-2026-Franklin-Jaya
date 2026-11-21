@extends('layouts.app')
@section('title', 'Ringkasan Sewa')
@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Ringkasan & Konfirmasi Sewa</h2>
<div class="max-w-2xl">
<div class="bg-white rounded-lg shadow divide-y">
    <div class="p-5">
        <h3 class="font-semibold text-gray-700 mb-3">Detail Sewa</h3>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-gray-500">Kendaraan</dt>
            <dd>{{ str_replace('_',' ',ucfirst($vehicle->type)) }} – {{ $vehicle->make }} {{ $vehicle->model }}</dd>
            <dt class="text-gray-500">Garasi</dt>
            <dd>{{ $garage->code }} ({{ $garage->type }})</dd>
            <dt class="text-gray-500">Jenis Sewa</dt>
            <dd>{{ ucfirst($rentalType) }}</dd>
            <dt class="text-gray-500">Tanggal Mulai</dt>
            <dd>{{ $startDate->format('d M Y') }}</dd>
            <dt class="text-gray-500">Tanggal Selesai</dt>
            <dd class="font-semibold text-blue-700">{{ $endDate->format('d M Y') }}</dd>
            @if($rentalType === 'harian')
            <dt class="text-gray-500">Jumlah Hari</dt>
            <dd>{{ $days }} hari</dd>
            @endif
        </dl>
    </div>
    <div class="p-5">
        <h3 class="font-semibold text-gray-700 mb-3">Rincian Biaya</h3>
        <dl class="text-sm space-y-1">
            <div class="flex justify-between">
                <span class="text-gray-500">Biaya Sewa</span>
                <span>Rp {{ number_format($result['rent_fee'], 0, ',', '.') }}</span>
            </div>
            @if($result['electricity_fee'] > 0)
            <div class="flex justify-between">
                <span class="text-gray-500">Pengisian Listrik ({{ $result['estimated_topup_kwh'] }} kWh)</span>
                <span>Rp {{ number_format($result['electricity_fee'], 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between border-t pt-2 font-bold text-base">
                <span>Total</span>
                <span class="text-blue-700">Rp {{ number_format($result['total'], 0, ',', '.') }}</span>
            </div>
        </dl>
    </div>
    <div class="p-5">
        <form method="POST" action="{{ route('penyewa.rentals.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @foreach($input as $k => $v)
                @if(!in_array($k, ['_token','payment_proof']))
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endif
            @endforeach
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Pembayaran</label>
                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required
                       class="w-full text-sm border border-gray-300 rounded px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, atau PDF. Maks 2MB.</p>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm">✅ Konfirmasi & Simpan</button>
                <a href="{{ route('penyewa.rentals.create') }}" class="text-gray-600 hover:underline text-sm py-2">← Kembali</a>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
