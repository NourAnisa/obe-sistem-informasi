@extends('layouts.dashboard')
@section('title', 'Monitoring Ketercapaian CPL saat Lulus')

@section('content')
<div class="space-y-6">

    {{-- ── HEADER ────────────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🎓 Monitoring Ketercapaian CPL saat Lulus</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Pantau mahasiswa yang telah lulus SKS kurikulum ({{ $totalSksKurikulum }} SKS)
                dan status ketercapaian {{ $totalCplSystem }} CPL mereka.
            </p>
        </div>
        <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline self-start">← Dashboard OBE</a>
    </div>

    {{-- ── ALERT: mahasiswa lulus SKS tapi CPL belum terpenuhi ──────── --}}
    @if($alertRows > 0)
    <div class="bg-amber-50 border border-amber-300 rounded-xl px-5 py-4 flex items-start gap-3">
        <span class="text-2xl">⚠️</span>
        <div>
            <p class="font-semibold text-amber-800">Peringatan: {{ $alertRows }} mahasiswa lulus tanpa mencapai seluruh CPL</p>
            <p class="text-sm text-amber-700 mt-0.5">
                Mahasiswa ini telah memenuhi total SKS kelulusan namun belum seluruh CPL tercapai.
                Perlu tindak lanjut akademik.
            </p>
        </div>
    </div>
    @endif

    {{-- ── FILTER ─────────────────────────────────────────────────────── --}}
    <form method="GET" class="bg-white border rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs text-gray-500 block mb-1">Angkatan</label>
            <select name="angkatan" class="border rounded px-3 py-2 text-sm w-36">
                <option value="">Semua Angkatan</option>
                @foreach($angkatanList as $a)
                <option value="{{ $a }}" @selected($angkatan==$a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Filter TA CPL Achievement</label>
            <select name="ta" class="border rounded px-3 py-2 text-sm w-44">
                <option value="">Semua TA (akumulasi)</option>
                @foreach($taList as $t)
                <option value="{{ $t }}" @selected($ta==$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
            🔍 Tampilkan
        </button>
        @if($angkatan || $ta)
        <a href="{{ route('obe.monitoring-kelulusan-cpl') }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">
            ✕ Reset
        </a>
        @endif
    </form>

    {{-- ── SUMMARY CARDS ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white border rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-gray-800">{{ $totalMhs }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Mahasiswa</p>
        </div>
        <div class="bg-white border rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-blue-600">{{ $lulusSks }}</p>
            <p class="text-xs text-gray-500 mt-1">Lulus SKS (≥ {{ $totalSksKurikulum }})</p>
        </div>
        <div class="bg-white border rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-green-600">{{ $cplTercapai }}</p>
            <p class="text-xs text-gray-500 mt-1">CPL Tercapai</p>
        </div>
        <div class="bg-white border rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-red-500">{{ $belumTercapai }}</p>
            <p class="text-xs text-gray-500 mt-1">Lulus SKS – CPL Belum</p>
        </div>
        <div class="bg-white border rounded-xl p-4 text-center">
            @php
            $pctColor = $pctCpl >= 80 ? 'text-green-600' : ($pctCpl >= 60 ? 'text-yellow-600' : 'text-red-500');
            @endphp
            <p class="text-3xl font-extrabold {{ $pctColor }}">{{ $pctCpl }}%</p>
            <p class="text-xs text-gray-500 mt-1">% CPL Tercapai (dari lulus)</p>
        </div>
    </div>

    {{-- ── TABLE ───────────────────────────────────────────────────────── --}}
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Daftar Mahasiswa — Status CPL Kelulusan</h2>
            <span class="text-xs text-gray-400">
                {{ $rows->count() }} mahasiswa
                @if($angkatan) · Angkatan {{ $angkatan }}@endif
                @if($ta) · TA {{ $ta }}@endif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">NIM</th>
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-center">Angkatan</th>
                        <th class="px-4 py-2 text-center">SKS Lulus</th>
                        <th class="px-4 py-2 text-center">Status SKS</th>
                        <th class="px-4 py-2 text-center">CPL Tercapai</th>
                        <th class="px-4 py-2 text-center">Total CPL</th>
                        <th class="px-4 py-2 text-center">Rata Nilai CPL</th>
                        <th class="px-4 py-2 text-center">Status CPL</th>
                        <th class="px-4 py-2 text-center">Alert</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($rows as $r)
                    @php
                    // SKS progress bar color
                    $sksRatio = $totalSksKurikulum > 0 ? min(100, round($r->total_sks / $totalSksKurikulum * 100)) : 0;
                    $sksCls = $r->sudah_lulus ? 'bg-green-500' : ($sksRatio >= 70 ? 'bg-blue-400' : ($sksRatio >= 40 ? 'bg-yellow-400' : 'bg-red-400'));

                    // CPL badge
                    $cplBadge = match($r->status_cpl) {
                    'CPL Tercapai' => ['bg-green-100 text-green-700 border border-green-300', '✅'],
                    'Sebagian Tercapai' => ['bg-yellow-100 text-yellow-700 border border-yellow-300', '⚡'],
                    'Belum Dihitung' => ['bg-gray-100 text-gray-500 border border-gray-200', '—'],
                    default => ['bg-red-100 text-red-700 border border-red-300', '❌'],
                    };

                    // Avg nilai color
                    $avgCls = $r->avg_nilai_cpl >= 80 ? 'text-green-600' : ($r->avg_nilai_cpl >= 60 ? 'text-yellow-600' : 'text-red-500');
                    @endphp
                    <tr class="{{ $r->needs_alert ? 'bg-amber-50' : 'hover:bg-gray-50' }}">
                        <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ $r->nim }}</td>
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $r->nama }}</td>
                        <td class="px-4 py-2 text-center text-gray-600">{{ $r->angkatan ?? '—' }}</td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex items-center gap-2 justify-center">
                                <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                    <div class="{{ $sksCls }} h-1.5 rounded-full" style="width:{{ $sksRatio }}%"></div>
                                </div>
                                <span class="font-mono text-xs">{{ $r->total_sks }}/{{ $r->sks_kurikulum }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($r->sudah_lulus)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">✅ Lulus</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Dalam Studi</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center font-bold {{ $r->cpl_tercapai > 0 ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $r->cpl_tercapai }}
                        </td>
                        <td class="px-4 py-2 text-center text-gray-600">{{ $r->total_cpl }}</td>
                        <td class="px-4 py-2 text-center font-mono font-semibold {{ $avgCls }}">
                            {{ $r->avg_nilai_cpl > 0 ? number_format($r->avg_nilai_cpl, 1) : '—' }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $cplBadge[0] }}">
                                {{ $cplBadge[1] }} {{ $r->status_cpl }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($r->needs_alert)
                            <span class="text-amber-600 text-sm" title="Lulus SKS namun CPL belum seluruhnya tercapai">⚠️</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-gray-400">
                            Tidak ada data mahasiswa.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── CPL Progress per Mahasiswa (lulus SKS + belum tercapai) ─────── --}}
    @php
    $alertList = $rows->where('needs_alert', true);
    @endphp
    @if($alertList->isNotEmpty())
    <div class="bg-white border border-amber-200 rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-amber-50 flex items-center gap-2">
            <span>⚠️</span>
            <h2 class="font-semibold text-amber-800">Mahasiswa Lulus SKS — CPL Belum Seluruhnya Tercapai</h2>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($alertList as $r)
            <div class="border border-amber-200 rounded-lg p-4 bg-amber-50/50">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-bold text-gray-800">{{ $r->nama }}</p>
                        <p class="text-xs text-gray-500 font-mono">{{ $r->nim }} · Angkatan {{ $r->angkatan }}</p>
                    </div>
                    <span class="text-xs bg-amber-200 text-amber-800 px-2 py-0.5 rounded-full font-medium">
                        {{ $r->cpl_tercapai }}/{{ $r->total_cpl }} CPL
                    </span>
                </div>
                {{-- SKS bar --}}
                <div class="mb-2">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>SKS Lulus</span>
                        <span>{{ $r->total_sks }}/{{ $r->sks_kurikulum }}</span>
                    </div>
                    @php $p = min(100, round($r->total_sks / max(1,$r->sks_kurikulum) * 100)); @endphp
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width:{{ $p }}%"></div>
                    </div>
                </div>
                {{-- CPL bar --}}
                <div>
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>CPL Tercapai</span>
                        @php $cp = $r->total_cpl > 0 ? round($r->cpl_tercapai / $r->total_cpl * 100) : 0; @endphp
                        <span>{{ $r->cpl_tercapai }}/{{ $r->total_cpl }} ({{ $cp }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="{{ $cp >= 80 ? 'bg-green-500' : ($cp >= 60 ? 'bg-yellow-400' : 'bg-red-400') }} h-2 rounded-full" style="width:{{ $cp }}%"></div>
                    </div>
                </div>
                {{-- Rata nilai CPL --}}
                <p class="text-xs text-gray-500 mt-2">
                    Rata-rata Nilai CPL:
                    <span class="font-semibold {{ $r->avg_nilai_cpl >= 80 ? 'text-green-600' : ($r->avg_nilai_cpl >= 60 ? 'text-yellow-600' : 'text-red-500') }}">
                        {{ $r->avg_nilai_cpl > 0 ? number_format($r->avg_nilai_cpl, 1) : '—' }}
                    </span>
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── LEGEND ─────────────────────────────────────────────────────── --}}
    <div class="bg-white border rounded-xl p-4 flex flex-wrap gap-4 text-xs text-gray-600">
        <span class="font-semibold text-gray-700">Keterangan:</span>
        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 border border-green-300">✅ CPL Tercapai — semua CPL achieved</span>
        <span class="px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 border border-yellow-300">⚡ Sebagian Tercapai — ada CPL belum achieved</span>
        <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-300">❌ Belum Tercapai — tidak ada CPL achieved</span>
        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 border border-gray-200">— Belum Dihitung — OBE belum dijalankan</span>
        <span>⚠️ = Lulus SKS namun CPL belum semua tercapai</span>
    </div>

</div>
@endsection