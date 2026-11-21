@extends('layouts.app')
@section('title', 'Laporan Sewa')
@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-4">Laporan Transaksi Sewa</h2>

<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Dari Tanggal</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded px-2 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Tanggal</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded px-2 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Garasi</label>
        <select name="garage_type" class="border border-gray-300 rounded px-2 py-1.5 text-sm">
            <option value="">Semua</option>
            @foreach(['sepeda','mobil','ev'] as $t)
            <option value="{{ $t }}" {{ request('garage_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Penyewa</label>
        <input type="text" name="user_search" value="{{ request('user_search') }}" placeholder="Cari nama..."
               class="border border-gray-300 rounded px-2 py-1.5 text-sm">
    </div>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm">Filter</button>
    <a href="{{ route('admin.reports.index') }}" class="text-gray-500 hover:underline text-sm py-1.5">Reset</a>
</form>

{{-- Ringkasan --}}
<div class="grid grid-cols-3 gap-4 mb-4">
    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs text-gray-500 mb-1">Total Transaksi</div>
        <div class="text-2xl font-bold text-gray-800">{{ $summary['total_transactions'] }}</div>
    </div>
    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs text-gray-500 mb-1">Total Pendapatan</div>
        <div class="text-xl font-bold text-blue-700">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
    </div>
    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs text-gray-500 mb-1">Pendapatan Listrik EV</div>
        <div class="text-xl font-bold text-green-700">Rp {{ number_format($summary['total_electricity'], 0, ',', '.') }}</div>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
<table class="w-full text-sm">
    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
            <th class="px-4 py-3 text-left">ID</th>
            <th class="px-4 py-3 text-left">Penyewa</th>
            <th class="px-4 py-3 text-left">Garasi</th>
            <th class="px-4 py-3 text-left">Tipe</th>
            <th class="px-4 py-3 text-left">Periode</th>
            <th class="px-4 py-3 text-right">Sewa</th>
            <th class="px-4 py-3 text-right">Listrik</th>
            <th class="px-4 py-3 text-right">Total</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach($rentals as $r)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-500">#{{ $r->id }}</td>
            <td class="px-4 py-3">{{ $r->user->name }}</td>
            <td class="px-4 py-3 font-mono">{{ $r->garage->code }}</td>
            <td class="px-4 py-3 text-xs">{{ ucfirst($r->rental_type) }}</td>
            <td class="px-4 py-3 text-xs">{{ $r->start_date->format('d M Y') }}<br><span class="text-gray-400">s/d</span> {{ $r->end_date->format('d M Y') }}</td>
            <td class="px-4 py-3 text-right">Rp {{ number_format($r->rent_fee, 0, ',', '.') }}</td>
            <td class="px-4 py-3 text-right text-green-700">{{ $r->electricity_fee > 0 ? 'Rp '.number_format($r->electricity_fee, 0, ',', '.') : '-' }}</td>
            <td class="px-4 py-3 text-right font-semibold">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
<div class="mt-4">{{ $rentals->links() }}</div>
@endsection
