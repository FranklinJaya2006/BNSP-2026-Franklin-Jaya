@extends('layouts.app')
@section('title', 'Kelola Garasi')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Kelola Garasi</h2>
    <a href="{{ route('admin.garages.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">+ Tambah Garasi</a>
</div>

<form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Garasi</label>
        <select name="type" class="border border-gray-300 rounded px-2 py-1.5 text-sm">
            <option value="">Semua</option>
            @foreach(['sepeda' => 'Sepeda', 'mobil' => 'Mobil', 'ev' => 'EV (Listrik)'] as $value => $label)
            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
        <select name="is_active" class="border border-gray-300 rounded px-2 py-1.5 text-sm">
            <option value="">Semua</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
        </select>
    </div>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded text-sm">Filter</button>
    <a href="{{ route('admin.garages.index') }}" class="text-gray-500 hover:underline text-sm py-1.5">Reset</a>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
<table class="w-full text-sm">
    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
            <th class="px-4 py-3 text-left">Kode</th>
            <th class="px-4 py-3 text-left">Tipe</th>
            <th class="px-4 py-3 text-right">Tarif Harian</th>
            <th class="px-4 py-3 text-right">Tarif Listrik/kWh</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach($garages as $g)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono font-semibold">{{ $g->code }}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    {{ $g->type === 'ev' ? 'bg-green-100 text-green-700' : ($g->type === 'sepeda' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ ucfirst($g->type) }}
                </span>
            </td>
            <td class="px-4 py-3 text-right">Rp {{ number_format($g->daily_rate, 0, ',', '.') }}</td>
            <td class="px-4 py-3 text-right">{{ $g->electricity_rate_kwh ? 'Rp '.number_format($g->electricity_rate_kwh, 0, ',', '.') : '-' }}</td>
            <td class="px-4 py-3">
                <span class="{{ $g->is_active ? 'text-green-600' : 'text-red-500' }} text-xs font-medium">
                    {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </td>
            <td class="px-4 py-3">
                <a href="{{ route('admin.garages.edit', $g->id) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
<div class="mt-4">{{ $garages->links() }}</div>
@endsection
