@extends('layouts.dashboard')
@section('title', 'Konsistensi RPS vs BAP')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 Monitoring Konsistensi RPS vs BAP</h1>
            <p class="text-sm text-gray-500 mt-0.5">Perbandingan materi yang direncanakan (RPS) vs yang dilaksanakan (BAP) per pertemuan</p>
        </div>
        <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline self-start">← Dashboard OBE</a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white border rounded-xl p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs text-gray-500 block mb-1">Tahun Akademik</label>
            <select name="ta" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm w-44">
                @foreach($taList as $t)
                <option value="{{ $t }}" @selected($ta===$t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">🔍 Tampilkan</button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-blue-600">{{ $rows->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Total MK Dipantau</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-green-600">{{ $okCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Konsisten (≥ 80%)</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-red-500">{{ $warningCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Perlu Perhatian (< 80%)</p>
        </div>
    </div>

    {{-- Avg Progress Bar --}}
    @if($rows->where('total', '>', 0)->isNotEmpty())
    <div class="bg-white border rounded-xl p-5">
        <div class="flex justify-between text-sm mb-2">
            <span class="font-semibold text-gray-700">Rata-rata Konsistensi RPS vs BAP</span>
            @php $avgRound = round($avgPct, 1); $avgColor = $avgRound >= 80 ? 'text-green-600' : ($avgRound >= 60 ? 'text-yellow-600' : 'text-red-500'); @endphp
            <span class="font-bold {{ $avgColor }}">{{ $avgRound }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="{{ $avgRound >= 80 ? 'bg-green-500' : ($avgRound >= 60 ? 'bg-yellow-400' : 'bg-red-400') }} h-3 rounded-full transition-all"
                style="width:{{ min(100,$avgRound) }}%"></div>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">Detail per Mata Kuliah — TA {{ $ta }}</h2>
        </div>

        @if($rows->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <div class="text-4xl mb-3">📋</div>
            <p>Tidak ada data RPS & BAP untuk TA <strong>{{ $ta }}</strong>.</p>
            <p class="text-sm mt-1">Pastikan RPS dan BAP sudah diisi untuk tahun akademik ini.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">Kode MK</th>
                        <th class="px-4 py-2 text-left">Nama Mata Kuliah</th>
                        <th class="px-4 py-2 text-center">Total Pertemuan</th>
                        <th class="px-4 py-2 text-center">Sesuai</th>
                        <th class="px-4 py-2 text-center">Persentase</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-left">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($rows as $r)
                    @php
                    $pct = (float)$r->persentase;
                    $barColor = $pct >= 80 ? 'bg-green-500' : ($pct >= 60 ? 'bg-yellow-400' : 'bg-red-400');
                    $statusCls = match($r->status) {
                    'OK' => 'bg-green-100 text-green-700 border border-green-300',
                    'Warning' => 'bg-red-100 text-red-700 border border-red-300',
                    default => 'bg-gray-100 text-gray-500 border border-gray-200',
                    };
                    $statusIcon = match($r->status) {
                    'OK' => '✅',
                    'Warning' => '⚠️',
                    default => '—',
                    };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-mono font-medium text-blue-700">{{ $r->mk_kode }}</td>
                        <td class="px-4 py-2 text-gray-800">{{ $r->mk_nama }}</td>
                        <td class="px-4 py-2 text-center text-gray-600">{{ $r->total }}</td>
                        <td class="px-4 py-2 text-center text-green-600 font-semibold">{{ $r->cocok }}</td>
                        <td class="px-4 py-2 text-center font-mono font-bold {{ $pct >= 80 ? 'text-green-600' : ($pct >= 60 ? 'text-yellow-600' : 'text-red-500') }}">
                            {{ $pct > 0 ? $pct.'%' : '—' }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusCls }}">
                                {{ $statusIcon }} {{ $r->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 min-w-[120px]">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="{{ $barColor }} h-2 rounded-full" style="width:{{ min(100,$pct) }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="bg-gray-50 border rounded-xl p-4 text-xs text-gray-500">
        <p class="font-medium text-gray-600 mb-1">ℹ️ Metode Pencocokan Materi</p>
        <p>Konsistensi dihitung berdasarkan kemiripan teks materi antara RPS dan BAP pada minggu yang sama.
            Dua pertemuan dianggap sesuai jika minimal 20 karakter pertama materi BAP muncul dalam materi RPS atau sebaliknya.
            Persentase ≥ 80% dianggap <strong>OK</strong>.</p>
    </div>
</div>
@endsection