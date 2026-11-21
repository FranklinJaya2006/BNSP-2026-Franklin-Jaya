@extends('layouts.app')
@section('title', 'Detail Penyewa')
@section('content')
<div class="max-w-lg">
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Detail Penyewa</h2>
    <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline">Kembali</a>
</div>
<div class="bg-white rounded-lg shadow divide-y">
    <div class="p-5">
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
            <dt class="text-gray-500">Nama</dt><dd class="font-medium">{{ $user->name }}</dd>
            <dt class="text-gray-500">Email</dt><dd>{{ $user->email }}</dd>
            <dt class="text-gray-500">Telepon</dt><dd>{{ $user->phone }}</dd>
            <dt class="text-gray-500">NIK</dt><dd class="font-mono text-xs">{{ substr($user->nik, 0, 4) }}************</dd>
            <dt class="text-gray-500">Daftar</dt><dd>{{ $user->created_at->format('d M Y H:i') }}</dd>
            <dt class="text-gray-500">Status</dt>
            <dd><span class="px-2 py-0.5 rounded text-xs font-medium
                {{ $user->status === 'approved' ? 'bg-green-100 text-green-700' : ($user->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                {{ ucfirst($user->status) }}</span></dd>
            @if($user->approvedBy)
            <dt class="text-gray-500">Diproses oleh</dt><dd>{{ $user->approvedBy->name }} – {{ $user->approved_at?->format('d M Y') }}</dd>
            @endif
            @if($user->rejection_notes)
            <dt class="text-gray-500">Alasan Tolak</dt><dd class="text-red-600">{{ $user->rejection_notes }}</dd>
            @endif
        </dl>
    </div>
    @if($user->status === 'pending')
    <div class="p-5 flex gap-3">
        <form method="POST" action="{{ route('admin.users.approve', $user->id) }}">
            @csrf
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">Setujui</button>
        </form>
        <form method="POST" action="{{ route('admin.users.reject', $user->id) }}" class="flex gap-2 items-start">
            @csrf
            <input type="text" name="notes" placeholder="Alasan penolakan (opsional)"
                   class="border border-gray-300 rounded px-3 py-2 text-sm w-64">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">Tolak</button>
        </form>
    </div>
    @endif
</div>
</div>
@endsection


