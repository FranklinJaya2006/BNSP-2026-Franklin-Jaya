@extends('layouts.app')
@section('title', 'Riwayat Sewa')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Riwayat Sewa Saya</h2>
    @if(auth()->user()->isApproved())
    <a href="{{ route('penyewa.rentals.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">+ Sewa Baru</a>
    @endif
</div>

@if($rentals->isEmpty())
    <p class="text-gray-500 text-sm">Belum ada transaksi sewa.</p>
@else
<div class="bg-white rounded-lg shadow overflow-hidden">
<table class="w-full text-sm">
    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
            <th class="px-4 py-3 text-left">ID</th>
            <th class="px-4 py-3 text-left">Garasi</th>
            <th class="px-4 py-3 text-left">Kendaraan</th>
            <th class="px-4 py-3 text-left">Periode</th>
            <th class="px-4 py-3 text-right">Total</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach($rentals as $r)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-500">#{{ $r->id }}</td>
            <td class="px-4 py-3 font-medium">{{ $r->garage->code }}</td>
            <td class="px-4 py-3">{{ $r->vehicle->make }} {{ $r->vehicle->model }}</td>
            <td class="px-4 py-3 text-xs">
                {{ $r->start_date->format('d M Y') }}<br>
                <span class="text-gray-400">s/d</span> {{ $r->end_date->format('d M Y') }}
            </td>
            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs font-medium
                    {{ $r->status === 'active' ? 'bg-green-100 text-green-700' : ($r->status === 'extended' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                    {{ ucfirst($r->status) }}
                </span>
            </td>
            <td class="px-4 py-3">
                <a href="{{ route('penyewa.rentals.show', $r->id) }}" class="text-blue-600 hover:underline text-xs">Detail</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
<div class="mt-4">{{ $rentals->links() }}</div>
@endif
@endsection
