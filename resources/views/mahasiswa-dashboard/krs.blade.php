@extends('layouts.dashboard')
@section('title', 'KRS — Pengambilan Mata Kuliah')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">📚 Kartu Rencana Studi (KRS)</h1>
            <p class="text-sm text-brand-300 mt-1">
                {{ $mahasiswa->nama }} — NIM: {{ $mahasiswa->nim }}
                @if($mahasiswa->angkatan) | Angkatan {{ $mahasiswa->angkatan }} @endif
            </p>
        </div>
        <a href="{{ route('mahasiswa.dashboard') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">
            ← Dashboard
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/40 text-green-300 px-4 py-3 rounded-lg text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/20 border border-red-500/40 text-red-300 px-4 py-3 rounded-lg text-sm">❌ {{ session('error') }}</div>
    @endif

    {{-- PA Info + Submit KRS Banner --}}
    @php
        $draftCount = $statusCounts->get('draft', 0);
        $diajukanCount = $statusCounts->get('diajukan', 0);
        $disetujuiCount = $statusCounts->get('disetujui', 0);
        $ditolakCount = $statusCounts->get('ditolak', 0);
    @endphp
    <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-5">
        <div class="flex flex-col md:flex-row gap-4">
            {{-- PA Info --}}
            <div class="flex-1">
                <div class="text-xs text-brand-400 mb-1">Dosen Pembimbing Akademik (PA)</div>
                @if($dosenPa)
                <div class="font-semibold text-white">{{ $dosenPa->name }}</div>
                <div class="text-xs text-brand-400">{{ $dosenPa->email }}</div>
                @else
                <div class="text-sm text-amber-400">⚠️ Belum ada Dosen PA. Hubungi Program Studi.</div>
                @endif
            </div>

            {{-- Status Summary --}}
            <div class="flex flex-wrap gap-3 items-center">
                @if($draftCount > 0)
                <span class="px-3 py-1.5 bg-brand-700 text-brand-300 text-xs rounded-lg border border-brand-600">
                    📝 {{ $draftCount }} Draft
                </span>
                @endif
                @if($diajukanCount > 0)
                <span class="px-3 py-1.5 bg-amber-500/20 text-amber-300 text-xs rounded-lg border border-amber-500/30">
                    ⏳ {{ $diajukanCount }} Menunggu
                </span>
                @endif
                @if($disetujuiCount > 0)
                <span class="px-3 py-1.5 bg-green-500/20 text-green-300 text-xs rounded-lg border border-green-500/30">
                    ✅ {{ $disetujuiCount }} Disetujui
                </span>
                @endif
                @if($ditolakCount > 0)
                <span class="px-3 py-1.5 bg-red-500/20 text-red-300 text-xs rounded-lg border border-red-500/30">
                    ❌ {{ $ditolakCount }} Ditolak
                </span>
                @endif
            </div>

            {{-- Submit to PA --}}
            @if($draftCount > 0 && $dosenPa)
            <form method="POST" action="{{ route('mahasiswa.krs.ajukan') }}"
                onsubmit="return confirm('Ajukan {{ $draftCount }} MK ke Dosen PA untuk persetujuan?')">
                @csrf
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg font-semibold transition">
                    📤 Ajukan KRS ke PA
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Info Card --}}
    <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-5">
        <div class="flex flex-col md:flex-row md:items-start gap-4">
            {{-- Stats --}}
            <div class="flex flex-wrap gap-5 flex-1">
                <div class="text-center">
                    <div class="text-3xl font-extrabold text-blue-400">{{ $mahasiswa->semester ?? 1 }}</div>
                    <div class="text-xs text-brand-400 mt-0.5">Semester Aktif</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-emerald-400">{{ $tahunAkademik }}</div>
                    <div class="text-xs text-brand-400 mt-0.5">Tahun Akademik</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-400">{{ $enrolledRaw->count() }}</div>
                    <div class="text-xs text-brand-400 mt-0.5">MK Diambil</div>
                </div>
                {{-- SKS Meter --}}
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $totalSks >= $maxSks ? 'text-red-400' : 'text-amber-400' }}">
                        {{ $totalSks }} / {{ $maxSks }}
                    </div>
                    <div class="text-xs text-brand-400 mt-0.5">SKS Terpakai / Maks</div>
                    @if($totalSks < $maxSks)
                    <div class="text-xs text-green-400 mt-0.5">Sisa {{ $sisaSks }} SKS</div>
                    @else
                    <div class="text-xs text-red-400 mt-0.5">Kuota penuh</div>
                    @endif
                </div>
                {{-- IPK & IPS --}}
                <div class="flex gap-3">
                    <div class="text-center">
                        <div class="text-xl font-bold {{ $mahasiswa->ipk >= 3.0 ? 'text-green-400' : 'text-orange-400' }}">
                            {{ number_format($mahasiswa->ipk ?? 0, 2) }}
                        </div>
                        <div class="text-xs text-brand-400 mt-0.5">IPK</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold {{ $mahasiswa->ips >= 3.0 ? 'text-green-400' : 'text-orange-400' }}">
                            {{ number_format($mahasiswa->ips ?? 0, 2) }}
                        </div>
                        <div class="text-xs text-brand-400 mt-0.5">IPS</div>
                    </div>
                </div>
            </div>

            {{-- Update Semester --}}
            <form method="POST" action="{{ route('mahasiswa.krs.semester') }}"
                class="flex flex-wrap items-end gap-2 border-t border-brand-700 pt-4 md:border-t-0 md:pt-0 md:border-l md:border-brand-700 md:pl-4">
                @csrf
                <div>
                    <label class="block text-xs text-brand-400 mb-1">Semester Saya</label>
                    <select name="semester"
                        class="bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                        @for($s = 1; $s <= 8; $s++)
                        <option value="{{ $s }}" {{ ($mahasiswa->semester ?? 1) == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-brand-400 mb-1">Tahun Akademik</label>
                    <select name="tahun_akademik"
                        class="bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                        @foreach(['2024/2025', '2025/2026', '2026/2027'] as $ta)
                        <option value="{{ $ta }}" {{ $tahunAkademik === $ta ? 'selected' : '' }}>{{ $ta }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg transition">
                    💾 Simpan
                </button>
            </form>
        </div>

        {{-- SKS Rule Info --}}
        <div class="mt-3 pt-3 border-t border-brand-700/50 text-xs text-brand-400">
            @if(($mahasiswa->semester ?? 1) <= 1)
            ℹ️ Semester 1: maksimal 20 SKS. Batas maks 24 SKS berlaku mulai semester 2 jika IPK ≥ 3,00 dan IPS ≥ 3,00.
            @elseif($mahasiswa->ipk >= 3.0 && $mahasiswa->ips >= 3.0)
            🎉 IPK ≥ 3,00 & IPS ≥ 3,00 — Anda mendapat kuota <span class="text-green-400 font-semibold">24 SKS</span> dan dapat mengambil mata kuliah hingga semester
            {{ collect($allowedSems)->max() }}.
            @else
            ⚠️ IPK atau IPS belum mencapai 3,00 — batas maksimal <span class="text-amber-400 font-semibold">20 SKS</span>.
            Tingkatkan IPK & IPS untuk mendapat kuota 24 SKS dan akses semester lebih tinggi.
            @endif
        </div>
    </div>

    {{-- Semester Tabs --}}
    <div class="flex flex-wrap gap-2">
        @for($s = 1; $s <= 8; $s++)
        @php $isAllowed = in_array($s, $allowedSems); @endphp
        @if($isAllowed)
        <a href="{{ route('mahasiswa.krs', ['semester' => $s]) }}"
            class="px-4 py-2 rounded-lg text-sm font-medium transition
                {{ $semesterView === $s
                    ? 'bg-blue-600 text-white shadow'
                    : 'bg-brand-700/60 text-brand-300 hover:bg-brand-600 border border-brand-600' }}">
            Semester {{ $s }}
            @if(($mahasiswa->semester ?? 1) == $s)
            <span class="ml-1 text-xs {{ $semesterView === $s ? 'text-blue-200' : 'text-yellow-400' }}">★</span>
            @endif
        </a>
        @else
        <span class="px-4 py-2 rounded-lg text-sm font-medium opacity-30 cursor-not-allowed bg-brand-700/30 text-brand-500 border border-brand-700"
            title="Semester {{ $s }} belum bisa diambil">
            Semester {{ $s }} 🔒
        </span>
        @endif
        @endfor
    </div>

    {{-- MK Available Table --}}
    <div class="bg-brand-800/60 rounded-xl border border-brand-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-700 flex items-center justify-between">
            <h2 class="text-base font-semibold text-white">
                📖 Mata Kuliah Semester {{ $semesterView }}
                <span class="text-sm font-normal text-brand-400">({{ $mataKuliahs->count() }} MK)</span>
            </h2>
            <span class="text-xs text-brand-400">TA {{ $tahunAkademik }}</span>
        </div>

        @if($mataKuliahs->isEmpty())
        <div class="text-center py-12 text-brand-400">
            <div class="text-4xl mb-2">📭</div>
            <p>Tidak ada mata kuliah untuk semester {{ $semesterView }}</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-brand-200">
                <thead class="bg-brand-700/60 text-brand-300 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Mata Kuliah</th>
                        <th class="px-4 py-3 text-center">SKS</th>
                        <th class="px-4 py-3 text-center">T/P</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-left">PJMK</th>
                        <th class="px-4 py-3 text-center">Status & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-700/40">
                    @foreach($mataKuliahs as $mk)
                    @php
                        $enrRecord = $enrolledRaw->get($mk->id);
                        $isEnrolled = $enrRecord !== null;
                        $enrStatus = $enrRecord?->status ?? null;
                        $statusConfig = match($enrStatus) {
                            'diajukan'  => ['label' => '⏳ Menunggu', 'class' => 'text-amber-300', 'bg' => 'bg-amber-500/10'],
                            'disetujui' => ['label' => '✅ Disetujui', 'class' => 'text-green-300', 'bg' => 'bg-green-500/10'],
                            'ditolak'   => ['label' => '❌ Ditolak', 'class' => 'text-red-300', 'bg' => 'bg-red-500/10'],
                            'draft'     => ['label' => '📝 Draft', 'class' => 'text-brand-400', 'bg' => 'bg-brand-700/30'],
                            default     => ['label' => '', 'class' => '', 'bg' => ''],
                        };
                        $rowBg = $isEnrolled ? ($statusConfig['bg'] ?? '') : '';
                    @endphp
                    <tr class="hover:bg-brand-700/30 transition {{ $rowBg }}">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-brand-300">{{ $mk->kode }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $mk->nama }}</div>
                            @if($mk->is_mbkm)<span class="text-xs bg-teal-500/20 text-teal-300 px-1.5 py-0.5 rounded">MBKM</span>@endif
                            @if(!$mk->is_wajib)<span class="text-xs bg-orange-500/20 text-orange-300 px-1.5 py-0.5 rounded">Pilihan</span>@endif
                            @if($enrStatus === 'ditolak' && $enrRecord?->catatan_pa)
                            <div class="text-xs text-red-400 mt-0.5">Catatan: {{ $enrRecord->catatan_pa }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-500/20 text-blue-300 text-sm font-bold">
                                {{ $mk->sks }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-brand-400">
                            {{ $mk->sks_teori ?? '-' }}T / {{ $mk->sks_praktikum ?? '0' }}P
                        </td>
                        <td class="px-4 py-3">
                            @php
                            $katColor = match($mk->kategori) {
                                'MKKP' => 'bg-blue-500/20 text-blue-300',
                                'MKWK' => 'bg-purple-500/20 text-purple-300',
                                'MKPP' => 'bg-green-500/20 text-green-300',
                                'MKP'  => 'bg-amber-500/20 text-amber-300',
                                default => 'bg-brand-700 text-brand-300',
                            };
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-xs {{ $katColor }}">{{ $mk->kategori }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-brand-400">{{ $mk->pjmk ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($isEnrolled)
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-xs font-medium {{ $statusConfig['class'] }}">{{ $statusConfig['label'] }}</span>
                                @if(in_array($enrStatus, ['draft', 'ditolak']))
                                <form method="POST"
                                    action="{{ route('mahasiswa.krs.unenroll', $enrRecord->id) }}"
                                    onsubmit="return confirm('Batalkan {{ addslashes($mk->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-xs px-2 py-0.5 bg-red-500/20 hover:bg-red-500/40 text-red-300 rounded transition">
                                        Batalkan
                                    </button>
                                </form>
                                @endif
                            </div>
                            @elseif(!in_array($mk->semester, $allowedSems))
                            <span class="text-xs text-brand-500 opacity-50" title="Semester {{ $mk->semester }} tidak tersedia untuk Anda">🔒 Terkunci</span>
                            @elseif($totalSks + $mk->sks > $maxSks)
                            <span class="text-xs text-red-400 opacity-70" title="Penambahan MK ini akan melebihi batas {{ $maxSks }} SKS">SKS penuh</span>
                            @else
                            <form method="POST" action="{{ route('mahasiswa.krs.enroll') }}">
                                @csrf
                                <input type="hidden" name="mata_kuliah_id" value="{{ $mk->id }}">
                                <button type="submit"
                                    class="text-xs px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition font-medium">
                                    + Ambil
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if($enrolledRaw->count() > 0)
        <div class="px-5 py-3 border-t border-brand-700 bg-brand-700/20 flex items-center justify-between text-sm">
            <span class="text-brand-400">Total MK diambil TA {{ $tahunAkademik }}: <strong class="text-white">{{ $enrolledRaw->count() }} MK</strong></span>
            <span class="text-brand-400">Total SKS: <strong class="text-amber-400">{{ $totalSks }} SKS</strong></span>
        </div>
        @endif
    </div>

    {{-- Semua MK yang Diambil --}}
    @if($enrolledRaw->count() > 0)
    <div class="bg-brand-800/60 rounded-xl border border-brand-700 p-5">
        <h3 class="text-sm font-semibold text-white mb-3">🗂️ Ringkasan KRS — TA {{ $tahunAkademik }}</h3>
        @php
        $allEnrolled = \App\Models\MahasiswaMk::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_aktif', $tahunAkademik)
            ->get();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($allEnrolled as $enr)
            @php
                $mk = $enr->mataKuliah;
                $statusBadge = match($enr->status) {
                    'diajukan'  => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                    'disetujui' => 'bg-green-500/20 text-green-300 border-green-500/30',
                    'ditolak'   => 'bg-red-500/20 text-red-300 border-red-500/30',
                    default     => 'bg-brand-700/40 text-brand-400 border-brand-600',
                };
                $statusLabel = match($enr->status) {
                    'diajukan'  => '⏳',
                    'disetujui' => '✅',
                    'ditolak'   => '❌',
                    default     => '📝',
                };
            @endphp
            <div class="flex items-center justify-between gap-2 bg-brand-700/40 rounded-lg px-3 py-2 border border-brand-600/40">
                <div class="min-w-0">
                    <div class="text-xs font-medium text-white truncate">{{ $mk?->nama }}</div>
                    <div class="text-xs text-brand-400">{{ $mk?->kode }} | Sem {{ $mk?->semester }} | {{ $mk?->sks }} SKS</div>
                    <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-xs border {{ $statusBadge }}">
                        {{ $statusLabel }} {{ ucfirst($enr->status) }}
                    </span>
                </div>
                @if(in_array($enr->status, ['draft', 'ditolak']))
                <form method="POST"
                    action="{{ route('mahasiswa.krs.unenroll', $enr->id) }}"
                    onsubmit="return confirm('Batalkan?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-400 hover:text-red-300 text-xs flex-shrink-0" title="Batalkan">✕</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection