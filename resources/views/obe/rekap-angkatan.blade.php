@extends('layouts.dashboard')
@section('title', 'Rekap Angkatan')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🏫 Rekap Evaluasi per Angkatan</h1>
            <p class="text-sm text-gray-500">Capaian CPL per angkatan mahasiswa — Traffic Light OBE</p>
        </div>
        <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard OBE</a>
    </div>

    <form method="GET" class="bg-white border rounded-xl p-4 flex gap-3 items-end">
        <div>
            <label class="text-xs text-gray-500 block mb-1">Tahun Akademik</label>
            <select name="ta" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                @foreach($taList as $t)<option value="{{ $t }}" @selected($t===$ta)>{{ $t }}</option>@endforeach
                @if(!$taList->contains($ta))<option value="{{ $ta }}" selected>{{ $ta }}</option>@endif
            </select>
        </div>
    </form>

    @include('obe._traffic-light', [
    'cohortByAngkatan' => $cohortByAngkatan,
    'cplList' => $cplList,
    'widgetTitle' => 'Traffic Light CPL × Angkatan — TA '.$ta,
    ])

    @if($cohortByAngkatan->isNotEmpty())
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Grid Detail Capaian CPL × Angkatan — TA {{ $ta }}</h2>
            <div class="flex gap-3 text-xs">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-400 inline-block"></span>Tercapai (≥target)</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-400 inline-block"></span>Perlu Monitoring</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-400 inline-block"></span>Belum Tercapai</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600 font-semibold">Angkatan</th>
                        @foreach($cplList as $cpl)
                        <th class="px-3 py-2 text-center text-gray-600" title="{{ $cpl->deskripsi }}">
                            {{ $cpl->kode }}
                        </th>
                        @endforeach
                        <th class="px-3 py-2 text-center text-gray-600">Total Mhs</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($cohortByAngkatan as $angk => $cohortRows)
                    @php $totalMhs = $cohortRows->max('total_mahasiswa') ?? 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-bold text-gray-800">{{ $angk }}</td>
                        @foreach($cplList as $cpl)
                        @php $row = $cohortRows->firstWhere('cpl_id', $cpl->id); @endphp
                        <td class="px-3 py-3 text-center">
                            @if($row)
                            @php
                            $sc = match($row->status_target) {
                            'tercapai' => 'bg-green-100 text-green-700 border-green-200',
                            'perlu_monitoring' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            default => 'bg-red-100 text-red-700 border-red-200'
                            };
                            $icon = match($row->status_target) { 'tercapai'=>'✅', 'perlu_monitoring'=>'⚠️', default=>'❌' };
                            @endphp
                            <div class="inline-block px-2 py-1 rounded border {{ $sc }} text-xs font-medium min-w-16 text-center" title="{{ $row->jumlah_tercapai }}/{{ $row->total_mahasiswa }} mhs tercapai, target {{ $row->target_capaian }}%">
                                {{ $icon }} {{ $row->pct_lulus }}%<br>
                                <span class="text-gray-400" style="font-size:10px">{{ $row->jumlah_tercapai }}/{{ $row->total_mahasiswa }}</span>
                            </div>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-3 py-3 text-center text-gray-500 text-sm">{{ $totalMhs }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-10 bg-white border rounded-xl text-gray-400">
        <div class="text-4xl mb-2">🏫</div>
        Belum ada data evaluasi angkatan untuk TA <strong>{{ $ta }}</strong>.<br>
        <span class="text-sm">Jalankan kalkulasi dari <a href="{{ route('obe.dashboard') }}" class="text-blue-500 hover:underline">Dashboard OBE</a>.</span>
    </div>
    @endif

    @if($trend->isNotEmpty())
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">📈 Tren Capaian CPL per Angkatan (Semua TA)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">CPL</th>
                        <th class="px-4 py-2 text-left">Angkatan</th>
                        <th class="px-4 py-2 text-left">Tahun Akademik</th>
                        <th class="px-4 py-2 text-right">Rata Nilai</th>
                        <th class="px-4 py-2 text-right">% Lulus</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($trend as $cplKode => $trendRows)
                    @foreach($trendRows as $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-1.5 font-medium text-blue-700">{{ $cplKode }}</td>
                        <td class="px-4 py-1.5">{{ $tr->angkatan }}</td>
                        <td class="px-4 py-1.5 text-gray-500 text-xs">{{ $tr->tahun_akademik }}</td>
                        <td class="px-4 py-1.5 text-right font-mono">{{ $tr->rata_nilai_cpl }}</td>
                        <td class="px-4 py-1.5 text-right font-mono">{{ $tr->pct_lulus }}%</td>
                        <td class="px-4 py-1.5 text-center">
                            @php $sc=match($tr->status_target){'tercapai'=>'bg-green-100 text-green-700','perlu_monitoring'=>'bg-yellow-100 text-yellow-700',default=>'bg-red-100 text-red-700'}; @endphp
                            <span class="px-2 py-0.5 rounded text-xs {{ $sc }}">{{ str_replace('_',' ',ucfirst($tr->status_target)) }}</span>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection