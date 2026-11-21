@extends('layouts.app')
@section('title', 'Edit Kendaraan')
@section('content')
<div class="max-w-lg">
<h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Kendaraan</h2>
<div class="bg-white rounded-lg shadow p-6">
<form method="POST" action="{{ route('penyewa.vehicles.update', $vehicle->id) }}" class="space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kendaraan</label>
        <input type="text" value="{{ str_replace('_', ' ', ucfirst($vehicle->type)) }}" disabled
               class="w-full border border-gray-200 bg-gray-50 rounded px-3 py-2 text-sm text-gray-500">
    </div>
    @if($vehicle->type !== 'sepeda')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat</label>
        <input type="text" name="plate" value="{{ old('plate', $vehicle->plate) }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    @endif
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
        <input type="text" name="make" value="{{ old('make', $vehicle->make) }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
        <input type="text" name="model" value="{{ old('model', $vehicle->model) }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    @if($vehicle->isEV())
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Baterai (kWh)</label>
        <input type="number" name="battery_capacity_kwh" step="0.01" min="0.01"
               value="{{ old('battery_capacity_kwh', $vehicle->battery_capacity_kwh) }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    @endif
    <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">Simpan</button>
        <a href="{{ route('penyewa.vehicles.index') }}" class="text-gray-600 hover:underline text-sm py-2">Batal</a>
    </div>
</form>
</div>
</div>
@endsection
