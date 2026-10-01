@extends('layouts.dashboard')
@section('title', 'Nilai Saya')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">📊 Nilai Saya</h1>
            <p class="text-sm text-brand-300 mt-1">
                {{ $mahasiswa->nama }} — NIM: {{ $mahasiswa->nim }}
            </p>
        </div>
        <a href="{{ route('mahasiswa.dashboard') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">
            ← Dashboard
        </a>
    </div>

    @if($nilaiRecords->isEmpty())
    <div class="bg-brand-800/40 border border-brand-700 rounded-xl p-10 text-center">
        <div class="text-4xl mb-3">📭</div>
        <p class="text-brand-300">Belum ada data nilai yang tersedia.</p>
        <p class="text-sm text-brand-400 mt-1">Nilai akan muncul setelah dosen menginput nilai untuk mata kuliah Anda.</p>
    </div>
    @else

    @foreach($grouped as $ta => $records)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <h2 class="font-bold text-gray-700 text-sm">📅 Tahun Akademik {{ $ta }}</h2>
            <span class="text-xs text-gray-500">{{ $records->count() }} mata kuliah</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">Mata Kuliah</th>
                        <th class="px-4 py-3 text-center">Tugas</th>
                        <th class="px-4 py-3 text-center">UTS</th>
                        <th class="px-4 py-3 text-center">UAS</th>
                        <th class="px-4 py-3 text-center">Partisipatif</th>
                        <th class="px-4 py-3 text-center">Proyek</th>
                        <th class="px-4 py-3 text-center">Nilai Akhir</th>
                        <th class="px-4 py-3 text-center">Grade</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($records as $nilai)
                    @php
                        $na = $nilai->nilai_akhir ?? 0;
                        $grade = $nilai->grade ?? App\Models\NilaiMahasiswa::toGrade($na);
                        $gradeColor = match($grade) {
                            'A' => 'bg-green-100 text-green-700',
                            'B' => 'bg-blue-100 text-blue-700',
                            'C' => 'bg-yellow-100 text-yellow-700',
                            default => 'bg-red-100 text-red-700',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">{{ $nilai->mataKuliah->nama ?? '—' }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $nilai->mataKuliah->kode ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $nilai->nilai_tugas !== null ? number_format($nilai->nilai_tugas, 1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $nilai->nilai_uts !== null ? number_format($nilai->nilai_uts, 1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $nilai->nilai_uas !== null ? number_format($nilai->nilai_uas, 1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $nilai->nilai_partisipatif !== null ? number_format($nilai->nilai_partisipatif, 1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $nilai->nilai_proyek !== null ? number_format($nilai->nilai_proyek, 1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ $na >= 56 ? 'text-green-700' : 'text-red-600' }}">
                                {{ $na > 0 ? number_format($na, 1) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($na > 0)
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $gradeColor }}">{{ $grade }}</span>
                            @else
                            <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($nilai->lulus)
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">✅ Lulus</span>
                            @elseif($na > 0)
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-100 text-red-700">❌ Tidak Lulus</span>
                            @else
                            <span class="text-xs text-gray-400">Belum</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- TA Summary --}}
        @php
            $lulusCount  = $records->where('lulus', true)->count();
            $avgNilai    = $records->avg('nilai_akhir');
        @endphp
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex gap-6 text-xs text-gray-500">
            <span>✅ Lulus: <strong class="text-gray-700">{{ $lulusCount }}/{{ $records->count() }}</strong></span>
            <span>📊 Rata-rata: <strong class="text-gray-700">{{ $avgNilai ? number_format($avgNilai, 1) : '—' }}</strong></span>
        </div>
    </div>
    @endforeach

    {{-- Overall summary --}}
    @php
        $totalLulus = $nilaiRecords->where('lulus', true)->count();
        $avgAll     = $nilaiRecords->avg('nilai_akhir');
    @endphp
    <div class="bg-brand-800/40 border border-brand-700 rounded-xl px-6 py-4 flex flex-wrap gap-6 text-sm">
        <div class="text-brand-300">
            Total MK: <strong class="text-white">{{ $nilaiRecords->count() }}</strong>
        </div>
        <div class="text-brand-300">
            Lulus: <strong class="text-green-400">{{ $totalLulus }}</strong>
        </div>
        <div class="text-brand-300">
            Tidak Lulus: <strong class="text-red-400">{{ $nilaiRecords->count() - $totalLulus }}</strong>
        </div>
        <div class="text-brand-300">
            Rata-rata Nilai Akhir: <strong class="text-white">{{ $avgAll ? number_format($avgAll, 1) : '—' }}</strong>
        </div>
    </div>
    @endif

</div>
@endsection
