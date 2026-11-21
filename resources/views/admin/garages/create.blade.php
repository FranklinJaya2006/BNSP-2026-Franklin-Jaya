@extends('layouts.app')
@section('title', 'Tambah Garasi')
@section('content')
<div class="max-w-lg">
<h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Garasi</h2>
<div class="bg-white rounded-lg shadow p-6">
<form method="POST" action="{{ route('admin.garages.store') }}" class="space-y-4">
    @csrf
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Garasi</label>
            <input type="text" name="code" value="{{ old('code') }}" required maxlength="10" placeholder="S001"
                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono uppercase">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
            <select name="type" required onchange="toggleEV(this)"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="sepeda" {{ old('type') === 'sepeda' ? 'selected' : '' }}>Sepeda</option>
                <option value="mobil" {{ old('type') === 'mobil' ? 'selected' : '' }}>Mobil</option>
                <option value="ev" {{ old('type') === 'ev' ? 'selected' : '' }}>EV (Listrik)</option>
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tarif Harian (Rp)</label>
        <input type="number" name="daily_rate" value="{{ old('daily_rate') }}" required min="0" step="1000"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    <div id="evRateField" style="{{ old('type') === 'ev' ? '' : 'display:none' }}">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tarif Listrik (Rp/kWh)</label>
        <input type="number" name="electricity_rate_kwh" value="{{ old('electricity_rate_kwh') }}" min="0" step="100"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">Simpan</button>
        <a href="{{ route('admin.garages.index') }}" class="text-gray-600 hover:underline text-sm py-2">Batal</a>
    </div>
</form>
</div>
</div>
<script>
function toggleEV(sel) {
    document.getElementById('evRateField').style.display = sel.value === 'ev' ? 'block' : 'none';
}
</script>
@endsection
