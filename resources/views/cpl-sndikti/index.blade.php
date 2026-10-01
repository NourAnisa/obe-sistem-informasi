@extends('layouts.dashboard')
@section('title', 'CPL SN-DIKTI')
@section('breadcrumb', 'CPL / SN-DIKTI')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">Pemetaan CPL SN-DIKTI → CPL Prodi</h1>
            <p class="text-sm text-gray-500 mt-1">
                Capaian Pembelajaran berdasarkan Standar Nasional Pendidikan Tinggi (SN-DIKTI)
                dan pemetaannya ke CPL Program Studi Sistem Informasi
            </p>
        </div>
        <a href="{{ route('cpl-sndikti.export') }}"
           class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Export Excel
        </a>
    </div>

    {{-- ── Summary badges ───────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
        @php
            $kategoriConfig = [
                'Sikap' => ['label' => 'Sikap (S)',               'bg' => 'bg-rose-50',   'border' => 'border-rose-200',   'text' => 'text-rose-700',   'dot' => 'bg-rose-500'],
                'KU'    => ['label' => 'Keterampilan Umum (KU)',  'bg' => 'bg-sky-50',    'border' => 'border-sky-200',    'text' => 'text-sky-700',    'dot' => 'bg-sky-500'],
                'KK'    => ['label' => 'Keterampilan Khusus (KK)','bg' => 'bg-violet-50', 'border' => 'border-violet-200', 'text' => 'text-violet-700', 'dot' => 'bg-violet-500'],
                'PP'    => ['label' => 'Pengetahuan (PP)',         'bg' => 'bg-amber-50',  'border' => 'border-amber-200',  'text' => 'text-amber-700',  'dot' => 'bg-amber-500'],
            ];
        @endphp
        @foreach ($kategoriConfig as $k => $cfg)
        <div class="flex items-center gap-3 {{ $cfg['bg'] }} border {{ $cfg['border'] }} rounded-xl p-3">
            <span class="w-3 h-3 rounded-full flex-shrink-0 {{ $cfg['dot'] }}"></span>
            <div>
                <p class="text-xs text-gray-500 leading-tight">{{ $cfg['label'] }}</p>
                <p class="text-xl font-bold {{ $cfg['text'] }}">{{ $data[$k]->count() }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Per-kategori sections ────────────────────────────────── --}}
    @foreach ($kategoriConfig as $kategori => $cfg)
        @php $items = $data[$kategori]; @endphp

        <div class="mb-8">
            {{-- Section header --}}
            <div class="flex items-center gap-3 mb-3">
                <span class="w-4 h-4 rounded-full {{ $cfg['dot'] }}"></span>
                <h2 class="text-lg font-bold {{ $cfg['text'] }}">{{ $cfg['label'] }}</h2>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $cfg['bg'] }} {{ $cfg['text'] }} border {{ $cfg['border'] }}">
                    {{ $items->count() }} butir
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-left w-36">Kode SN-DIKTI</th>
                            <th class="px-4 py-3 text-center w-32">CPL Prodi</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($items as $i => $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="font-bold {{ $cfg['text'] }} font-mono text-xs tracking-wide">
                                    {{ $item->kode }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @forelse ($item->cpls as $cpl)
                                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-100 text-brand-700 border border-brand-200 mr-1">
                                        {{ $cpl->kode }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 italic">—</span>
                                @endforelse
                            </td>
                            <td class="px-4 py-3 text-gray-700 leading-relaxed">{{ $item->deskripsi }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">
                                Belum ada data untuk kategori {{ $kategori }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <p class="text-xs text-gray-400 mt-2">
        Total: {{ $data->flatten()->count() }} butir SN-DIKTI
        &bull; Sumber: Permendikbud No. 3 Tahun 2020 tentang Standar Nasional Pendidikan Tinggi
    </p>
</div>
@endsection