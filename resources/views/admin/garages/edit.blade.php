@extends('layouts.app')
@section('title', 'Edit Garasi')
@section('content')
<div class="max-w-lg">
<h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Garasi – {{ $garage->code }}</h2>
<div class="bg-white rounded-lg shadow p-6">
<form method="POST" action="{{ route('admin.garages.update', $garage->id) }}" class="space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
        <input type="text" value="{{ ucfirst($garage->type) }}" disabled
               class="w-full border border-gray-200 bg-gray-50 rounded px-3 py-2 text-sm text-gray-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tarif Harian (Rp)</label>
        <input type="number" name="daily_rate" value="{{ old('daily_rate', $garage->daily_rate) }}" required min="0" step="1000"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    @if($garage->type === 'ev')
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tarif Listrik (Rp/kWh)</label>
        <input type="number" name="electricity_rate_kwh" min="0" step="100"
               value="{{ old('electricity_rate_kwh', $garage->electricity_rate_kwh) }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </div>
    @endif
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $garage->is_active ? 'checked' : '' }}>
        <label for="is_active" class="text-sm text-gray-700">Aktif</label>
    </div>
    <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">Simpan</button>
        <a href="{{ route('admin.garages.index') }}" class="text-gray-600 hover:underline text-sm py-2">Batal</a>
    </div>
</form>
</div>
</div>
@endsection
