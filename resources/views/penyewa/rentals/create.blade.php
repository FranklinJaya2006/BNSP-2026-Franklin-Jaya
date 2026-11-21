@extends('layouts.app')
@section('title', 'Sewa Garasi Baru')
@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Sewa Garasi Baru</h2>

@if($vehicles->isEmpty())
    <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-4 rounded">
        Anda belum memiliki kendaraan. <a href="{{ route('penyewa.vehicles.create') }}" class="font-semibold underline">Tambah kendaraan dulu</a>.
    </div>
@else
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Step 1: Pilih parameter --}}
    <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-4">1. Pilih Parameter Sewa</h3>
        <form method="GET" action="{{ route('penyewa.rentals.create') }}" class="space-y-4" id="filterForm">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kendaraan</label>
                <select name="vehicle_id" required onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">-- Pilih Kendaraan --</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                            {{ str_replace('_',' ',ucfirst($v->type)) }} – {{ $v->make }} {{ $v->model }} {{ $v->plate ? '('.$v->plate.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('vehicle_id'))
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Sewa</label>
                    <select name="rental_type" required onchange="toggleDays(); this.form.submit()"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        <option value="harian" {{ request('rental_type','harian') === 'harian' ? 'selected' : '' }}>Harian</option>
                        <option value="bulanan" {{ request('rental_type') === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}" required onchange="this.form.submit()"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
            </div>
            <div id="daysRow" style="{{ request('rental_type','harian') === 'bulanan' ? 'display:none' : '' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Hari (maks {{ $maxDays }})</label>
                <input type="number" name="days" value="{{ request('days', 1) }}" min="1" max="{{ $maxDays }}"
                       required onchange="this.form.submit()"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
            @endif
        </form>
    </div>

    {{-- Step 2: Pilih garasi --}}
    @if($selectedVehicle && $garages->isNotEmpty())
    <div class="lg:col-span-1 bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-4">2. Garasi Tersedia</h3>
        <p class="text-xs text-gray-500 mb-3">Kompatibel dengan <strong>{{ str_replace('_',' ',ucfirst($selectedVehicle->type)) }}</strong></p>
        <div class="space-y-2">
            @foreach($garages as $g)
            <label class="block border rounded p-3 cursor-pointer hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                <input type="radio" name="selected_garage" value="{{ $g->id }}" class="sr-only"
                       onclick="selectGarage({{ $g->id }})">
                <div class="font-semibold text-sm">{{ $g->code }}</div>
                <div class="text-xs text-gray-500">Rp {{ number_format($g->daily_rate, 0, ',', '.') }}/hari</div>
                @if($g->electricity_rate_kwh)
                <div class="text-xs text-green-600">Rp {{ number_format($g->electricity_rate_kwh, 0, ',', '.') }}/kWh</div>
                @endif
            </label>
            @endforeach
        </div>
    </div>
    @elseif($selectedVehicle)
    <div class="lg:col-span-1 bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-2">2. Garasi Tersedia</h3>
        <p class="text-sm text-gray-500">Tidak ada garasi tersedia untuk rentang tanggal ini.</p>
    </div>
    @endif
</div>

{{-- Step 3: Form detail & submit --}}
@if($selectedVehicle && $garages->isNotEmpty())
<div class="mt-6 bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold text-gray-700 mb-4">3. Detail & Konfirmasi</h3>
    <form method="POST" action="{{ route('penyewa.rentals.preview') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ request('vehicle_id') }}">
        <input type="hidden" name="garage_id" id="garageIdInput" value="">
        <input type="hidden" name="rental_type" value="{{ request('rental_type','harian') }}">
        <input type="hidden" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
        <input type="hidden" name="days" value="{{ request('days', 1) }}">

        @if($selectedVehicle->isEV())
        <div class="grid grid-cols-2 gap-4 p-4 bg-green-50 rounded border border-green-200">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas Baterai (kWh)</label>
                <input type="number" name="battery_capacity_kwh" step="0.01" min="0.01" required
                       value="{{ $selectedVehicle->battery_capacity_kwh }}"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sisa Baterai Saat Ini (%)</label>
                <input type="number" name="remaining_pct" step="0.1" min="0" max="100" required value="0"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </div>
        </div>
        @endif

        <div class="bg-yellow-50 border border-yellow-200 rounded p-3 text-sm text-yellow-800">
            Pilih garasi di panel kanan sebelum melanjutkan.
        </div>

        <button type="submit" id="previewBtn" disabled
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
            Lihat Ringkasan Harga
        </button>
    </form>
</div>
@endif
@endif

<script>
function selectGarage(id) {
    document.getElementById('garageIdInput').value = id;
    document.getElementById('previewBtn').disabled = false;
    document.getElementById('previewBtn').closest('form').querySelector('.bg-yellow-50').style.display = 'none';
}
function toggleDays() {
    const type = document.querySelector('[name=rental_type]')?.value;
    const row = document.getElementById('daysRow');
    if (row) row.style.display = type === 'bulanan' ? 'none' : 'block';
}
</script>
@endsection
