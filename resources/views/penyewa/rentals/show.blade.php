@extends('layouts.app')
@section('title', 'Detail Sewa #' . $rental->id)
@section('content')
<div class="max-w-2xl">
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Detail Sewa #{{ $rental->id }}</h2>
    <a href="{{ route('penyewa.rentals.index') }}" class="text-sm text-blue-600 hover:underline">Kembali</a>
</div>

<div class="bg-white rounded-lg shadow divide-y">
    <div class="p-5">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-gray-500">Garasi</dt><dd>{{ $rental->garage->code }} ({{ $rental->garage->type }})</dd>
            <dt class="text-gray-500">Kendaraan</dt><dd>{{ $rental->vehicle->make }} {{ $rental->vehicle->model }}</dd>
            <dt class="text-gray-500">Jenis</dt><dd>{{ ucfirst($rental->rental_type) }}</dd>
            <dt class="text-gray-500">Mulai</dt><dd>{{ $rental->start_date->format('d M Y') }}</dd>
            <dt class="text-gray-500">Selesai</dt><dd>{{ $rental->end_date->format('d M Y') }}</dd>
            <dt class="text-gray-500">Status</dt>
            <dd><span class="px-2 py-0.5 rounded text-xs font-medium
                {{ $rental->status === 'active' ? 'bg-green-100 text-green-700' : ($rental->status === 'extended' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                {{ ucfirst($rental->status) }}</span></dd>
        </dl>
    </div>
    <div class="p-5">
        <h3 class="font-semibold text-sm text-gray-700 mb-2">Rincian Biaya</h3>
        <dl class="text-sm space-y-1">
            <div class="flex justify-between"><span class="text-gray-500">Biaya Sewa</span><span>Rp {{ number_format($rental->rent_fee, 0, ',', '.') }}</span></div>
            @if($rental->rental_type === 'bulanan')
            <div class="flex justify-between text-xs text-gray-500"><span>Harga harian × 25</span><span>Rp {{ number_format($rental->daily_rate_snapshot, 0, ',', '.') }} × 25</span></div>
            @endif
            @if($rental->electricity_fee > 0)
            <div class="flex justify-between"><span class="text-gray-500">Biaya Listrik</span><span>Rp {{ number_format($rental->electricity_fee, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between border-t pt-2 font-bold">
                <span>Total</span><span class="text-blue-700">Rp {{ number_format($rental->total, 0, ',', '.') }}</span>
            </div>
        </dl>
    </div>
    @if($rental->extensions->isNotEmpty())
    <div class="p-5">
        <h3 class="font-semibold text-sm text-gray-700 mb-2">Riwayat Perpanjangan</h3>
        <div class="space-y-2">
            @foreach($rental->extensions as $ext)
            <div class="bg-blue-50 rounded p-3 text-sm">
                <div class="flex justify-between">
                    <span>{{ $ext->previous_end->format('d M Y') }} - {{ $ext->new_end->format('d M Y') }}</span>
                    <span class="font-semibold">Rp {{ number_format($ext->rent_fee, 0, ',', '.') }}</span>
                </div>
                <div class="text-xs text-gray-500 mt-1">Oleh: {{ $ext->createdBy?->name }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</div>
@endsection

