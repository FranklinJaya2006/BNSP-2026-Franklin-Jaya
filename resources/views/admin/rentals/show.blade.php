@extends('layouts.app')
@section('title', 'Detail Transaksi #' . $rental->id)
@section('content')
<div class="max-w-2xl">
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Detail Transaksi #{{ $rental->id }}</h2>
    <div class="flex gap-2">
        @if($rental->isActive())
        <a href="{{ route('admin.rentals.extend.form', $rental->id) }}"
           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm">Perpanjang</a>
        @endif
        <a href="{{ route('admin.rentals.index') }}" class="text-sm text-blue-600 hover:underline py-2">← Kembali</a>
    </div>
</div>
<div class="bg-white rounded-lg shadow divide-y">
    <div class="p-5">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-gray-500">Penyewa</dt><dd>{{ $rental->user->name }}</dd>
            <dt class="text-gray-500">Kendaraan</dt><dd>{{ $rental->vehicle->make }} {{ $rental->vehicle->model }} ({{ str_replace('_',' ',ucfirst($rental->vehicle->type)) }})</dd>
            <dt class="text-gray-500">Garasi</dt><dd>{{ $rental->garage->code }} – {{ ucfirst($rental->garage->type) }}</dd>
            <dt class="text-gray-500">Jenis Sewa</dt><dd>{{ ucfirst($rental->rental_type) }}</dd>
            <dt class="text-gray-500">Periode</dt><dd>{{ $rental->start_date->format('d M Y') }} – {{ $rental->end_date->format('d M Y') }}</dd>
            <dt class="text-gray-500">Status</dt>
            <dd><span class="px-2 py-0.5 rounded text-xs font-medium
                {{ $rental->status==='active'?'bg-green-100 text-green-700':($rental->status==='extended'?'bg-blue-100 text-blue-700':'bg-gray-100 text-gray-600') }}">
                {{ ucfirst($rental->status) }}</span></dd>
        </dl>
    </div>
    <div class="p-5">
        <h3 class="font-semibold text-sm text-gray-700 mb-2">Rincian Biaya</h3>
        <dl class="text-sm space-y-1">
            <div class="flex justify-between"><span class="text-gray-500">Biaya Sewa</span><span>Rp {{ number_format($rental->rent_fee, 0, ',', '.') }}</span></div>
            @if($rental->electricity_fee > 0)
            <div class="flex justify-between"><span class="text-gray-500">Biaya Listrik ({{ $rental->estimated_topup_kwh }} kWh × Rp {{ number_format($rental->unit_kwh_rate_snapshot, 0, ',', '.') }})</span><span>Rp {{ number_format($rental->electricity_fee, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between border-t pt-2 font-bold"><span>Total</span><span class="text-blue-700">Rp {{ number_format($rental->total, 0, ',', '.') }}</span></div>
        </dl>
    </div>
    @if($rental->extensions->isNotEmpty())
    <div class="p-5">
        <h3 class="font-semibold text-sm text-gray-700 mb-3">Riwayat Perpanjangan ({{ $rental->extensions->count() }}x)</h3>
        <div class="space-y-2">
            @foreach($rental->extensions as $i => $ext)
            <div class="bg-blue-50 rounded p-3 text-sm flex justify-between">
                <div>
                    <span class="font-medium">Perpanjangan #{{ $i+1 }}</span>
                    <span class="text-gray-500 ml-2">{{ $ext->previous_end->format('d M') }} → {{ $ext->new_end->format('d M Y') }}</span>
                    <div class="text-xs text-gray-500 mt-0.5">Oleh: {{ $ext->createdBy?->name }}</div>
                </div>
                <span class="font-semibold">Rp {{ number_format($ext->rent_fee, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</div>
@endsection
