@extends('layouts.dashboard')
@section('title', 'Mahasiswa Bimbingan PA')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">👨‍🏫 Mahasiswa Bimbingan PA</h1>
            <p class="text-sm text-brand-300 mt-1">Periksa dan setujui KRS mahasiswa bimbingan Anda</p>
        </div>
        <a href="{{ route('dosen.dashboard') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">
            ← Dashboard
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/40 text-green-300 px-4 py-3 rounded-lg text-sm">✅ {{ session('success') }}</div>
    @endif

    @if($mahasiswas->isEmpty())
    <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-12 text-center text-brand-400">
        <div class="text-5xl mb-4">📭</div>
        <p class="text-lg font-medium text-white mb-1">Belum ada mahasiswa bimbingan</p>
        <p class="text-sm">Kaprodi akan menetapkan mahasiswa ke Anda sebagai Dosen PA</p>
    </div>
    @else

    @php $totalPending = $mahasiswas->sum(fn($m) => $m->enrollments->count()); @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-3xl font-extrabold text-blue-400">{{ $mahasiswas->count() }}</div>
            <div class="text-xs text-brand-400 mt-1">Total Mahasiswa</div>
        </div>
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-3xl font-extrabold text-amber-400">{{ $totalPending }}</div>
            <div class="text-xs text-brand-400 mt-1">KRS Menunggu Approval</div>
        </div>
    </div>

    {{-- Mahasiswa List --}}
    <div class="bg-brand-800/60 border border-brand-700 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-700">
            <h2 class="text-base font-semibold text-white">Daftar Mahasiswa Bimbingan</h2>
        </div>
        <div class="divide-y divide-brand-700/40">
            @foreach($mahasiswas as $mhs)
            @php $pendingCount = $mhs->enrollments->count(); @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 hover:bg-brand-700/20 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-300 font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($mhs->nama, 0, 2)) }}
                    </div>
                    <div>
                        <div class="font-medium text-white">{{ $mhs->nama }}</div>
                        <div class="text-xs text-brand-400">
                            NIM: {{ $mhs->nim }}
                            @if($mhs->angkatan) | Angkatan {{ $mhs->angkatan }} @endif
                            | Semester {{ $mhs->semester ?? '-' }}
                        </div>
                        <div class="text-xs text-brand-500">TA {{ $mhs->tahun_akademik ?? '2025/2026' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($pendingCount > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/20 text-amber-300 text-xs rounded-full border border-amber-500/30">
                        ⏳ {{ $pendingCount }} MK menunggu
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-500/20 text-green-300 text-xs rounded-full border border-green-500/30">
                        ✅ Semua diproses
                    </span>
                    @endif
                    <a href="{{ route('dosen.pa.krs', $mhs->id) }}"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg transition font-medium">
                        Lihat KRS →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection