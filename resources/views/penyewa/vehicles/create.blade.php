@extends('layouts.app')
@section('title', 'Tambah Kendaraan')
@section('content')
<div class="max-w-lg">
<h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Kendaraan</h2>
<div class="bg-white rounded-lg shadow p-6">
<form method="POST" action="{{ route('penyewa.vehicles.store') }}" class="space-y-4" id="vehicleForm">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Kendaraan</label>
        <select name="type" id="vehicleType" required onchange="toggleFields()"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">-- Pilih Tipe --</option>
            <option value="sepeda" {{ old('type') === 'sepeda' ? 'selected' : '' }}>Sepeda</option>
            <option value="sepeda_motor" {{ old('type') === 'sepeda_motor' ? 'selected' : '' }}>Sepeda Motor</option>
            <option value="mobil_bensin" {{ old('type') === 'mobil_bensin' ? 'selected' : '' }}>Mobil Bensin</option>
            <option value="mobil_listrik" {{ old('type') === 'mobil_listrik' ? 'selected' : '' }}>Mobil Listrik (EV)</option>
        </select>
    </div>
    <div id="plateField">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Plat</label>
        <input type="text" name="plate" value="{{ old('plate') }}" placeholder="Contoh: B 1234 XYZ"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
        <input type="text" name="make" value="{{ old('make') }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
        <input type="text" name="model" value="{{ old('model') }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    <div id="batteryField" class="hidden">
        <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Baterai (kWh)</label>
        <input type="number" name="battery_capacity_kwh" step="0.01" min="0.01"
               value="{{ old('battery_capacity_kwh') }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">Simpan</button>
        <a href="{{ route('penyewa.vehicles.index') }}" class="text-gray-600 hover:underline text-sm py-2">Batal</a>
    </div>
</form>
</div>
</div>
<script>
function toggleFields() {
    const type = document.getElementById('vehicleType').value;
    document.getElementById('plateField').style.display = type === 'sepeda' ? 'none' : 'block';
    document.getElementById('batteryField').style.display = type === 'mobil_listrik' ? 'block' : 'none';
}
toggleFields();
</script>
@endsection
