@extends('layouts.dashboard')
@section('title','Dashboard Dekan')

@section('content')
{{-- ═══ HEADER ═══ --}}
<div class="bg-gradient-to-r from-amber-700 via-amber-800 to-orange-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-amber-200 text-sm mb-2">
                    <span class="text-white font-medium">
                        {{ auth()->user()->role === 'wakildekan' ? '🏫 Wakil Dekan' : '🏛 Dekan' }}
                    </span>
                </div>
                <h1 class="text-3xl font-bold font-display">
                    Selamat datang, <span class="text-amber-200">{{ auth()->user()->name }}</span>
                </h1>
                <p class="text-amber-200 mt-1 text-sm">
                    {{ auth()->user()->role === 'wakildekan' ? 'Wakil Dekan' : 'Dekan' }}
                    · {{ config('obe.universitas', 'Universitas Sari Mulia') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.programs.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    🎓 Kelola Prodi
                </a>
                <a href="{{ route('admin.faculties.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    🏛 Kelola Fakultas
                </a>
                <a href="{{ route('users.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-400 rounded-lg text-sm font-semibold shadow transition">
                    👥 Manajemen Pengguna
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- ═══ STAT CARDS UNIVERSITAS ═══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
        $statCards = [
            ['label'=>'Total Prodi',     'value'=>$totalProdi,   'icon'=>'🎓', 'color'=>'amber'],
            ['label'=>'Total MK',        'value'=>$totalMk,      'icon'=>'📖', 'color'=>'blue'],
            ['label'=>'Total SKS',       'value'=>$totalSks,     'icon'=>'⚖️', 'color'=>'indigo'],
            ['label'=>'Total CPL',       'value'=>$totalCpl,     'icon'=>'🎯', 'color'=>'violet'],
            ['label'=>'Total Dosen',     'value'=>$totalDosen,   'icon'=>'👩‍🏫','color'=>'emerald'],
        ];
        $colorMap = [
            'amber'  => ['bg'=>'bg-amber-50', 'icon'=>'bg-amber-100 text-amber-700',   'val'=>'text-amber-700',  'border'=>'border-amber-200'],
            'blue'   => ['bg'=>'bg-blue-50',  'icon'=>'bg-blue-100 text-blue-700',     'val'=>'text-blue-700',   'border'=>'border-blue-200'],
            'indigo' => ['bg'=>'bg-indigo-50','icon'=>'bg-indigo-100 text-indigo-700', 'val'=>'text-indigo-700', 'border'=>'border-indigo-200'],
            'violet' => ['bg'=>'bg-violet-50','icon'=>'bg-violet-100 text-violet-700', 'val'=>'text-violet-700', 'border'=>'border-violet-200'],
            'emerald'=> ['bg'=>'bg-emerald-50','icon'=>'bg-emerald-100 text-emerald-700','val'=>'text-emerald-700','border'=>'border-emerald-200'],
        ];
        @endphp
        @foreach($statCards as $card)
        @php $c = $colorMap[$card['color']]; @endphp
        <div class="{{ $c['bg'] }} rounded-2xl p-5 border {{ $c['border'] }} shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <span class="w-10 h-10 {{ $c['icon'] }} rounded-xl flex items-center justify-center text-xl">{{ $card['icon'] }}</span>
            </div>
            <div class="text-2xl font-bold {{ $c['val'] }}">{{ number_format($card['value']) }}</div>
            <div class="text-xs text-slate-500 mt-1 font-medium">{{ $card['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ═══ STATISTIK PER PROGRAM STUDI ═══ --}}
    @foreach($faculties as $faculty)
    @php $prodiInFakultas = $prodiStats->where('faculty_id', $faculty->id); @endphp
    @if($prodiInFakultas->count())
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h2 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-amber-100 text-amber-700 rounded-lg flex items-center justify-center">🏛</span>
                {{ $faculty->nama }}
                <span class="text-xs font-normal text-slate-400">({{ $faculty->kode ?? '' }})</span>
            </h2>
            <span class="text-xs text-slate-400 font-medium">{{ $prodiInFakultas->count() }} Prodi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-3">Program Studi</th>
                        <th class="px-6 py-3 text-center">Jenjang</th>
                        <th class="px-6 py-3 text-center">Kaprodi</th>
                        <th class="px-6 py-3 text-center">Akreditasi</th>
                        <th class="px-6 py-3 text-center">MK</th>
                        <th class="px-6 py-3 text-center">SKS</th>
                        <th class="px-6 py-3 text-center">CPL</th>
                        <th class="px-6 py-3 text-center">Dosen</th>
                        <th class="px-6 py-3 text-center">Status OBE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($prodiInFakultas as $prodi)
                    @php
                        $tanpaCpl = $mkTanpaCpl[$prodi->id] ?? 0;
                        $obeOk = $tanpaCpl === 0 && $prodi->total_mk > 0 && $prodi->total_cpl > 0;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $prodi->nama }}</div>
                            @if($prodi->kode_prodi)
                            <div class="text-xs text-slate-400 font-mono">{{ $prodi->kode_prodi }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                            $jenjangColor = ['S1'=>'bg-blue-50 text-blue-700','S2'=>'bg-indigo-50 text-indigo-700',
                                'S3'=>'bg-purple-50 text-purple-700','D3'=>'bg-orange-50 text-orange-700',
                                'D4'=>'bg-amber-50 text-amber-700','Profesi'=>'bg-green-50 text-green-700'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $jenjangColor[$prodi->jenjang] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $prodi->jenjang }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-xs text-slate-600">
                            {{ $prodi->kaprodi ? Str::limit($prodi->kaprodi, 25) : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($prodi->akreditasi)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                {{ $prodi->akreditasi }}
                            </span>
                            @else
                            <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-700">{{ $prodi->total_mk }}</td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-700">{{ $prodi->total_sks }}</td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-700">{{ $prodi->total_cpl }}</td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-700">{{ $prodi->total_dosen }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($prodi->total_mk === 0)
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Belum ada MK</span>
                            @elseif($obeOk)
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">✅ OBE Lengkap</span>
                            @else
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">
                                ⚠️ {{ $tanpaCpl }} MK tanpa CPL
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endforeach

    {{-- Prodi tanpa Fakultas --}}
    @php $prodiTanpaFakultas = $prodiStats->whereNull('faculty_id'); @endphp
    @if($prodiTanpaFakultas->count())
    <div class="bg-red-50 border border-red-200 rounded-xl px-6 py-4 text-sm text-red-700">
        ⚠️ {{ $prodiTanpaFakultas->count() }} program studi belum terdaftar di fakultas manapun.
    </div>
    @endif

</div>
@endsection
