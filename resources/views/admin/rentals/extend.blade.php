@extends('layouts.app')
@section('title', 'Perpanjang Sewa #' . $rental->id)
@section('content')
<div class="max-w-lg">
<h2 class="text-2xl font-bold text-gray-800 mb-6">Perpanjang Sewa #{{ $rental->id }}</h2>
<div class="bg-white rounded-lg shadow divide-y">
    <div class="p-5">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-gray-500">Penyewa</dt><dd>{{ $rental->user->name }}</dd>
            <dt class="text-gray-500">Garasi</dt><dd>{{ $rental->garage->code }}</dd>
            <dt class="text-gray-500">Jenis</dt><dd>{{ ucfirst($rental->rental_type) }}</dd>
            <dt class="text-gray-500">Akhir Saat Ini</dt><dd class="font-semibold">{{ $rental->end_date->format('d M Y') }}</dd>
            <dt class="text-gray-500">Perpanjangan ke-</dt><dd>{{ $extensionCount + 1 }}</dd>
            <dt class="text-gray-500">Akhir Baru</dt><dd class="font-bold text-blue-700">{{ $newEnd->format('d M Y') }}</dd>
            <dt class="text-gray-500">Biaya Tambahan</dt><dd class="font-bold text-blue-700">Rp {{ number_format($addFee, 0, ',', '.') }}</dd>
        </dl>
    </div>
    <div class="p-5">
        <form method="POST" action="{{ route('admin.rentals.extend', $rental->id) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Bukti Pembayaran Perpanjangan</label>
                <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required
                       class="w-full text-sm border border-gray-300 rounded px-3 py-2">
                <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, atau PDF. Maks 2MB.</p>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2 rounded text-sm">Simpan Perpanjangan</button>
                <a href="{{ route('admin.rentals.show', $rental->id) }}" class="text-gray-600 hover:underline text-sm py-2">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
