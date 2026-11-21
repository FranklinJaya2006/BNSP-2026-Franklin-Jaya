@extends('layouts.app')
@section('title', 'Kelola Penyewa')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Kelola Penyewa</h2>
</div>

<div class="flex gap-2 mb-4 flex-wrap">
    @foreach(['','pending','approved','rejected'] as $s)
    <a href="{{ route('admin.users.index') }}{{ $s ? '?status='.$s : '' }}"
       class="px-3 py-1 rounded text-sm border {{ request('status') == $s ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
        {{ $s ? ucfirst($s) : 'Semua' }}
    </a>
    @endforeach
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
<table class="w-full text-sm">
    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Email</th>
            <th class="px-4 py-3 text-left">NIK</th>
            <th class="px-4 py-3 text-left">Daftar</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @foreach($users as $u)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
            <td class="px-4 py-3 text-gray-600">{{ $u->email }}</td>
            <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ substr($u->nik, 0, 4) }}************</td>
            <td class="px-4 py-3 text-xs text-gray-500">{{ $u->created_at->format('d M Y') }}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs font-medium
                    {{ $u->status === 'approved' ? 'bg-green-100 text-green-700' : ($u->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($u->status) }}
                </span>
            </td>
            <td class="px-4 py-3">
                <a href="{{ route('admin.users.show', $u->id) }}" class="text-blue-600 hover:underline text-xs mr-2">Detail</a>
                @if($u->status === 'pending')
                <form method="POST" action="{{ route('admin.users.approve', $u->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="text-green-600 hover:underline text-xs mr-1"
                            onclick="return confirm('Setujui akun {{ $u->name }}?')">Setujui</button>
                </form>
                <button onclick="showRejectModal({{ $u->id }}, '{{ $u->name }}')"
                        class="text-red-600 hover:underline text-xs">Tolak</button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
<div class="mt-4">{{ $users->links() }}</div>

{{-- Modal Tolak --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <h3 class="font-semibold text-gray-800 mb-3">Tolak Pendaftaran</h3>
        <p class="text-sm text-gray-600 mb-4" id="rejectModalName"></p>
        <form method="POST" id="rejectForm" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan (opsional)</label>
                <textarea name="notes" rows="3" placeholder="Contoh: Data KTP tidak sesuai."
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm">Tolak</button>
                <button type="button" onclick="closeRejectModal()" class="text-gray-600 hover:underline text-sm">Batal</button>
            </div>
        </form>
    </div>
</div>
<script>
function showRejectModal(id, name) {
    document.getElementById('rejectForm').action = `/admin/users/${id}/reject`;
    document.getElementById('rejectModalName').textContent = `Penyewa: ${name}`;
    document.getElementById('rejectModal').classList.remove('hidden');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>
@endsection
