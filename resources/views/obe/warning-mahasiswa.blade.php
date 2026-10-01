@extends('layouts.dashboard')
@section('title', 'Early Warning Mahasiswa')

@section('content')
<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">⚠️ Early Warning Mahasiswa</h1>
            <p class="text-sm text-gray-500 mt-1">Mahasiswa dengan capaian CPL di bawah threshold — perlu intervensi</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('obe.early-warning.csv', ['ta'=>$ta,'angkatan'=>$angkatan]) }}"
                class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                📥 Export CSV
            </a>
            <a href="{{ route('obe.dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                ← Dashboard OBE
            </a>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <form method="GET" action="{{ route('obe.early-warning') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun Akademik</label>
                <select name="ta" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    @foreach($taList as $t)
                    <option value="{{ $t }}" {{ $ta === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                    @if(!$taList->contains($ta))
                    <option value="{{ $ta }}" selected>{{ $ta }}</option>
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Angkatan</label>
                <select name="angkatan" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    <option value="">Semua Angkatan</option>
                    @foreach($angkatanList as $ang)
                    <option value="{{ $ang }}" {{ $angkatan == $ang ? 'selected' : '' }}>{{ $ang }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                🔍 Filter
            </button>
            @if($angkatan)
            <a href="{{ route('obe.early-warning', ['ta'=>$ta]) }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>
    </div>

    @if($warningRows->isEmpty())
    <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
        <div class="text-4xl mb-2">🎉</div>
        <p class="text-green-700 font-semibold">Tidak ada mahasiswa dengan CPL di bawah threshold</p>
        <p class="text-green-600 text-sm mt-1">untuk TA <strong>{{ $ta }}</strong>{{ $angkatan ? ' angkatan '.$angkatan : '' }}</p>
    </div>
    @else

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center border-l-4 border-l-red-500">
            <div class="text-3xl font-bold text-red-600">{{ $totalWarningMhs }}</div>
            <div class="text-xs text-gray-500 mt-1">Mahasiswa Warning</div>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center border-l-4 border-l-orange-500">
            <div class="text-3xl font-bold text-orange-600">{{ $warningRows->count() }}</div>
            <div class="text-xs text-gray-500 mt-1">Total CPL Belum Tercapai</div>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center border-l-4 border-l-yellow-500">
            <div class="text-lg font-bold text-yellow-700 truncate">{{ $cplTerburuk?->cpl_kode ?? '—' }}</div>
            <div class="text-xs text-gray-500 mt-1">CPL Nilai Terendah</div>
            @if($cplTerburuk)
            <div class="text-xs text-red-500 font-mono mt-0.5">{{ $cplTerburuk->nilai_cpl }}</div>
            @endif
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center border-l-4 border-l-purple-500">
            @php $kritis = $warningRows->where('status', 'Kritis')->count(); @endphp
            <div class="text-3xl font-bold text-purple-700">{{ $kritis }}</div>
            <div class="text-xs text-gray-500 mt-1">Status Kritis</div>
        </div>
    </div>

    {{-- ── Per-CPL Summary ── --}}
    @if($perCpl->isNotEmpty())
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">📊 Ringkasan per CPL — {{ $warningRows->count() }} record warning</h2>
        </div>
        <div class="flex flex-wrap gap-3 p-4">
            @foreach($perCpl as $c)
            @php
            $barW = min(100, round(($c['jumlah_mhs'] / max(1,$totalWarningMhs)) * 100));
            @endphp
            <div class="flex-1 min-w-[160px] bg-red-50 border border-red-200 rounded-lg p-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-red-700 text-sm">{{ $c['kode'] }}</span>
                    <span class="text-xs text-red-600 font-mono">avg {{ $c['rata_nilai'] }}</span>
                </div>
                <div class="text-xs text-gray-600 mb-2 truncate">{{ $c['deskripsi'] }}</div>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-red-200 rounded-full h-1.5">
                        <div class="bg-red-500 h-1.5 rounded-full" style="width:{{ $barW }}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-red-700">{{ $c['jumlah_mhs'] }} mhs</span>
                </div>
                <div class="text-xs text-gray-400 mt-1">Gap rata-rata: {{ $c['avg_gap'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Main Warning Table ── --}}
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">
                ⚠️ Daftar Mahasiswa Warning
                <span class="ml-2 text-xs font-normal text-gray-500">{{ $warningRows->count() }} records</span>
            </h2>
            <div class="flex gap-2 text-xs">
                <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 font-medium">🔴 Kritis</span>
                <span class="px-2 py-0.5 rounded bg-orange-100 text-orange-700 font-medium">🟠 Perlu Intervensi</span>
                <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-700 font-medium">🟡 Perhatian</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">NIM</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center">Angkatan</th>
                        <th class="px-4 py-3 text-center">CPL</th>
                        <th class="px-4 py-3 text-left max-w-xs">Deskripsi CPL</th>
                        <th class="px-4 py-3 text-center">Nilai</th>
                        <th class="px-4 py-3 text-center">Threshold</th>
                        <th class="px-4 py-3 text-center">Gap</th>
                        <th class="px-4 py-3 text-left w-32">Progress</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($warningRows as $r)
                    @php
                    $pct = (float)$r->threshold > 0
                    ? min(100, round((float)$r->nilai_cpl / (float)$r->threshold * 100))
                    : 0;
                    $barColor = $r->status === 'Kritis'
                    ? 'bg-red-500'
                    : ($r->status === 'Perlu Intervensi' ? 'bg-orange-400' : 'bg-yellow-400');
                    $badgeCls = $r->status === 'Kritis'
                    ? 'bg-red-100 text-red-700'
                    : ($r->status === 'Perlu Intervensi' ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700');
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $r->status==='Kritis' ? 'bg-red-50/30' : '' }}">
                        <td class="px-4 py-2 font-mono font-medium text-gray-800">{{ $r->nim }}</td>
                        <td class="px-4 py-2 text-gray-800">
                            {{ $r->nama }}
                            <a href="{{ route('obe.student-evaluasi', ['nim'=>$r->nim,'angkatan'=>$r->angkatan,'ta'=>$ta]) }}"
                                class="ml-1 text-blue-500 hover:text-blue-700 text-xs" title="Lihat detail mahasiswa">↗</a>
                        </td>
                        <td class="px-4 py-2 text-center text-gray-500">{{ $r->angkatan }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="font-bold text-blue-700">{{ $r->cpl_kode }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-600 max-w-xs">
                            <div class="line-clamp-2">{{ $r->cpl_deskripsi }}</div>
                        </td>
                        <td class="px-4 py-2 text-center font-mono font-semibold
                            {{ (float)$r->nilai_cpl < (float)$r->threshold * 0.5 ? 'text-red-600' : 'text-orange-600' }}">
                            {{ number_format((float)$r->nilai_cpl, 2) }}
                        </td>
                        <td class="px-4 py-2 text-center font-mono text-gray-500 text-xs">
                            {{ number_format((float)$r->threshold, 0) }}
                        </td>
                        <td class="px-4 py-2 text-center font-mono text-red-600 text-xs font-semibold">
                            -{{ number_format((float)$r->gap, 2) }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-1.5">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $barColor }} h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-400 w-8 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeCls }}">
                                {{ $r->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t text-xs text-gray-400 flex items-center justify-between">
            <span>Menampilkan {{ $warningRows->count() }} records · TA {{ $ta }}{{ $angkatan ? ' · Angkatan '.$angkatan : '' }}</span>
            <a href="{{ route('obe.early-warning.csv', ['ta'=>$ta,'angkatan'=>$angkatan]) }}"
                class="text-green-600 hover:underline">📥 Download CSV</a>
        </div>
    </div>

    @endif {{-- end if warningRows not empty --}}

</div>
@endsection