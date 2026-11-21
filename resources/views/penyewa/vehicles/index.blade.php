@extends('layouts.app')
@section('title', 'Kendaraan Saya')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Kendaraan Saya</h2>
    <a href="{{ route('penyewa.vehicles.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">+ Tambah Kendaraan</a>
</div>

@if($vehicles->isEmpty())
    <p class="text-gray-500 text-sm">Belum ada kendaraan. <a href="{{ route('penyewa.vehicles.create') }}" class="text-blue-600 hover:underline">Tambah sekarang</a>.</p>
@else
<div class="bg-white rounded-lg shadow overflow-hidden">
<table class="w-full text-sm">
    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
            <th class="px-4 py-3 text-left">Tipe</th>
            <th class="px-4 py-3 text-left">Plat</th>
            <th class="px-4 py-3 text-left">Merek / Model</th>
            <th class="px-4 py-3 text-left">Kapasitas Baterai</th>
            <th class="px-4 py-3 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach($vehicles as $v)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs font-medium
                    {{ $v->type === 'mobil_listrik' ? 'bg-green-100 text-green-700' : ($v->type === 'sepeda' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ str_replace('_', ' ', ucfirst($v->type)) }}
                </span>
            </td>
            <td class="px-4 py-3">{{ $v->plate ?? '-' }}</td>
            <td class="px-4 py-3">{{ $v->make }} {{ $v->model }}</td>
            <td class="px-4 py-3">{{ $v->battery_capacity_kwh ? $v->battery_capacity_kwh . ' kWh' : '-' }}</td>
            <td class="px-4 py-3">
                <a href="{{ route('penyewa.vehicles.edit', $v->id) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
@endif
@endsection
