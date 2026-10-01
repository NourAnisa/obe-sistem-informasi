@extends('layouts.dashboard')
@section('title', 'Deteksi Mata Kuliah Bermasalah')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🔍 Deteksi Mata Kuliah Bermasalah</h1>
            <p class="text-sm text-gray-500 mt-0.5">Identifikasi MK yang memerlukan review berdasarkan 3 kriteria: CPL, Evaluasi Dosen, dan Kelulusan</p>
        </div>
        <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline self-start">← Dashboard OBE</a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white border rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs text-gray-500 block mb-1">Tahun Akademik</label>
            <select name="ta" class="border rounded px-3 py-2 text-sm w-44">
                @foreach($taList as $t)
                <option value="{{ $t }}" @selected($ta===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Angkatan</label>
            <select name="angkatan" class="border rounded px-3 py-2 text-sm w-32">
                <option value="">Semua</option>
                @foreach($angkatanList as $a)
                <option value="{{ $a }}" @selected($angkatan==$a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">🔍 Tampilkan</button>
        @if($angkatan)
        <a href="{{ route('obe.problematic-courses', ['ta' => $ta]) }}" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">✕ Reset</a>
        @endif
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-gray-50 border rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-gray-700">{{ $rows->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Total MK Dianalisis</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-red-600">{{ $perluReview }}</p>
            <p class="text-xs text-gray-500 mt-1">Perlu Review ⚠️</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-green-600">{{ $normal }}</p>
            <p class="text-xs text-gray-500 mt-1">Normal ✅</p>
        </div>
    </div>

    {{-- Criteria Legend --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800">
        <p class="font-semibold mb-2">📌 Kriteria "Perlu Review":</p>
        <div class="flex flex-wrap gap-4">
            <span>🔴 <strong>CPL Rendah</strong> — rata-rata nilai CPMK &lt; 70</span>
            <span>🔴 <strong>Eval Dosen Rendah</strong> — rata-rata evaluasi (pedagogik/profesional/kepribadian/sosial) &lt; 2.0 / 3.0</span>
            <span>🔴 <strong>Kelulusan Rendah</strong> — pass rate &lt; 70%</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Analisis per Mata Kuliah — TA {{ $ta }}</h2>
            <span class="text-xs text-gray-400">{{ $rows->count() }} MK</span>
        </div>

        @if($rows->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <div class="text-4xl mb-3">📊</div>
            <p>Tidak ada data untuk TA <strong>{{ $ta }}</strong>.</p>
            <p class="text-sm mt-1">Pastikan OBE telah dihitung terlebih dahulu.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Kode MK</th>
                        <th class="px-4 py-2 text-left">Nama Mata Kuliah</th>
                        <th class="px-4 py-2 text-center">Rata CPL/CPMK</th>
                        <th class="px-4 py-2 text-center">Eval Dosen</th>
                        <th class="px-4 py-2 text-center">Pass Rate</th>
                        <th class="px-4 py-2 text-center">Isu Terdeteksi</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($rows as $r)
                    @php
                    $cplCls = $r->avg_cpl !== null ? ($r->avg_cpl < 70 ? 'text-red-600 font-bold' : 'text-green-600' ) : 'text-gray-400' ;
                        $evalCls=$r->avg_eval !== null ? ($r->avg_eval < 2.0 ? 'text-red-600 font-bold' : 'text-green-600' ) : 'text-gray-400' ;
                            $passCls=$r->pass_rate!== null ? ($r->pass_rate< 70 ? 'text-red-600 font-bold' : 'text-green-600' ) : 'text-gray-400' ;
                                $rowBg=$r->perlu_review ? 'bg-red-50' : '';
                                @endphp
                                <tr class="hover:bg-gray-50 {{ $rowBg }}">
                                    <td class="px-4 py-2 font-mono font-medium text-blue-700">{{ $r->mk_kode }}</td>
                                    <td class="px-4 py-2 text-gray-800">{{ $r->mk_nama }}</td>
                                    <td class="px-4 py-2 text-center font-mono {{ $cplCls }}">
                                        {{ $r->avg_cpl !== null ? number_format($r->avg_cpl, 1) : '—' }}
                                        @if($r->avg_cpl !== null)
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="{{ $r->avg_cpl >= 70 ? 'bg-green-500' : 'bg-red-400' }} h-1.5 rounded-full" style="width:{{ min(100, $r->avg_cpl) }}%"></div>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center font-mono {{ $evalCls }}">
                                        {{ $r->avg_eval !== null ? number_format($r->avg_eval, 2).' / 3.0' : '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-center font-mono {{ $passCls }}">
                                        {{ $r->pass_rate !== null ? $r->pass_rate.'%' : '—' }}
                                        @if($r->pass_rate !== null)
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                            <div class="{{ $r->pass_rate >= 70 ? 'bg-green-500' : 'bg-red-400' }} h-1.5 rounded-full" style="width:{{ min(100, $r->pass_rate) }}%"></div>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if(count($r->issues) > 0)
                                        <div class="flex flex-wrap gap-1 justify-center">
                                            @foreach($r->issues as $issue)
                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs border border-red-300">{{ $issue }}</span>
                                            @endforeach
                                        </div>
                                        @else
                                        <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($r->perlu_review)
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-300">⚠️ Perlu Review</span>
                                        @else
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-300">✅ Normal</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Quick action to CQI --}}
    @if($perluReview > 0)
    <div class="bg-amber-50 border border-amber-300 rounded-xl px-5 py-4 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <span class="text-2xl">💡</span>
            <div>
                <p class="font-semibold text-amber-800">{{ $perluReview }} MK perlu tindak lanjut</p>
                <p class="text-sm text-amber-700">Buat CQI Action Plan untuk CPL yang bermasalah</p>
            </div>
        </div>
        <a href="{{ route('obe.cqi-monitoring', ['ta' => $ta]) }}"
            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-medium">
            🔄 Ke CQI Monitoring →
        </a>
    </div>
    @endif
</div>
@endsection