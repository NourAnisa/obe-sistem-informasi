@extends('layouts.dashboard')
@section('title', 'KRS Mahasiswa — ' . $mahasiswa->nama)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">📋 KRS: {{ $mahasiswa->nama }}</h1>
            <p class="text-sm text-brand-300 mt-1">
                NIM: {{ $mahasiswa->nim }}
                @if($mahasiswa->angkatan) | Angkatan {{ $mahasiswa->angkatan }} @endif
                | Semester {{ $mahasiswa->semester ?? '-' }}
                | TA {{ $mahasiswa->tahun_akademik ?? '2025/2026' }}
            </p>
        </div>
        <a href="{{ route('dosen.pa.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">
            ← Kembali
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/40 text-green-300 px-4 py-3 rounded-lg text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/20 border border-red-500/40 text-red-300 px-4 py-3 rounded-lg text-sm">❌ {{ session('error') }}</div>
    @endif

    {{-- Stats --}}
    @php
    $byStatus = $enrollments->groupBy('status');
    $pending = $byStatus->get('diajukan', collect())->count();
    $approved = $byStatus->get('disetujui', collect())->count();
    $rejected = $byStatus->get('ditolak', collect())->count();
    $draft = $byStatus->get('draft', collect())->count();
    $totalSks = $enrollments->sum(fn($e) => $e->mataKuliah?->sks ?? 0);
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-amber-400">{{ $pending }}</div>
            <div class="text-xs text-brand-400 mt-1">Menunggu</div>
        </div>
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-green-400">{{ $approved }}</div>
            <div class="text-xs text-brand-400 mt-1">Disetujui</div>
        </div>
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-red-400">{{ $rejected }}</div>
            <div class="text-xs text-brand-400 mt-1">Ditolak</div>
        </div>
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-blue-400">{{ $totalSks }}</div>
            <div class="text-xs text-brand-400 mt-1">Total SKS</div>
        </div>
    </div>

    {{-- Approve All Button --}}
    @if($pending > 0)
    <form method="POST" action="{{ route('dosen.pa.approve-all', $mahasiswa->id) }}"
        onsubmit="return confirm('Setujui semua {{ $pending }} MK yang diajukan?')">
        @csrf
        <button type="submit"
            class="w-full py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl font-semibold transition flex items-center justify-center gap-2">
            ✅ Setujui Semua ({{ $pending }} MK Diajukan)
        </button>
    </form>
    @endif

    {{-- KRS Table --}}
    @if($enrollments->isEmpty())
    <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-10 text-center text-brand-400">
        <div class="text-4xl mb-2">📭</div>
        <p>Mahasiswa belum mengajukan KRS</p>
    </div>
    @else
    <div class="bg-brand-800/60 border border-brand-700 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-700">
            <h2 class="text-base font-semibold text-white">
                Daftar KRS — TA {{ $mahasiswa->tahun_akademik ?? '2025/2026' }}
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-brand-200">
                <thead class="bg-brand-700/60 text-brand-300 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Mata Kuliah</th>
                        <th class="px-4 py-3 text-center">SKS</th>
                        <th class="px-4 py-3 text-center">Sem</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Catatan PA</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-700/40">
                    @foreach($enrollments as $enr)
                    @php
                    $mk = $enr->mataKuliah;
                    $statusConfig = match($enr->status) {
                    'diajukan' => ['label' => '⏳ Menunggu', 'class' => 'bg-amber-500/20 text-amber-300'],
                    'disetujui' => ['label' => '✅ Disetujui', 'class' => 'bg-green-500/20 text-green-300'],
                    'ditolak' => ['label' => '❌ Ditolak', 'class' => 'bg-red-500/20 text-red-300'],
                    default => ['label' => '📝 Draft', 'class' => 'bg-brand-700 text-brand-400'],
                    };
                    @endphp
                    <tr class="hover:bg-brand-700/20 transition">
                        <td class="px-4 py-3"><span class="font-mono text-xs text-brand-300">{{ $mk?->kode }}</span></td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $mk?->nama }}</div>
                            @if($mk?->pjmk)
                            <div class="text-xs text-brand-500">{{ $mk->pjmk }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="w-7 h-7 inline-flex items-center justify-center rounded-full bg-blue-500/20 text-blue-300 text-xs font-bold">
                                {{ $mk?->sks }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-brand-400">{{ $mk?->semester }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block px-2 py-1 rounded text-xs font-medium {{ $statusConfig['class'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-brand-400">
                            {{ $enr->catatan_pa ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($enr->status === 'diajukan')
                            <div class="flex items-center justify-center gap-1">
                                {{-- Approve --}}
                                <form method="POST" action="{{ route('dosen.pa.approve', $enr->id) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-2 py-1 bg-green-500/20 hover:bg-green-500/40 text-green-300 text-xs rounded transition"
                                        title="Setujui">✅</button>
                                </form>
                                {{-- Reject --}}
                                <button onclick="showRejectModal({{ $enr->id }}, '{{ addslashes($mk?->nama) }}')"
                                    class="px-2 py-1 bg-red-500/20 hover:bg-red-500/40 text-red-300 text-xs rounded transition"
                                    title="Tolak">❌</button>
                            </div>
                            @else
                            <span class="text-xs text-brand-500">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-brand-800 border border-brand-600 rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-white mb-1">Tolak Mata Kuliah</h3>
        <p id="rejectMkName" class="text-sm text-brand-400 mb-4"></p>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-brand-300 mb-1">Catatan / Alasan Penolakan</label>
                <textarea name="catatan_pa" rows="3"
                    class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                    placeholder="Tuliskan alasan penolakan..."></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeRejectModal()"
                    class="px-4 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm rounded-lg transition">Tolak MK</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showRejectModal(enrollmentId, mkName) {
        document.getElementById('rejectMkName').textContent = mkName;
        document.getElementById('rejectForm').action = '/dosen/pa/krs/' + enrollmentId + '/reject';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) closeRejectModal();
    });
</script>
@endsection