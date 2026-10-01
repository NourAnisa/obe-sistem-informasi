@extends('layouts.public')
@section('title','Kurikulum per Semester')
@section('content')

@include('components.program-switcher', ['currentRoute' => 'kurikulum.index'])

{{-- Page Header --}}
<div class="bg-gradient-to-r from-purple-800 to-brand-900 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3 text-purple-300 text-sm mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
            <span>›</span><span class="text-white">Kurikulum</span>
        </div>
        <h1 class="font-display text-3xl font-bold text-white">Kurikulum per Semester</h1>
        <p class="text-purple-200 mt-2">{{ $semesters->sum(fn($d) => $d['totalMk']) }} Mata Kuliah · {{ $semesters->sum(fn($d) => $d['totalSks']) }} SKS · {{ config('obe.tahun_akademik') }} · {{ $program?->nama ?? config('obe.prodi') }}</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Summary row --}}
    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 mb-8">
        @php
        $semColors = [
        1 => 'from-blue-500 to-blue-600',
        2 => 'from-indigo-500 to-indigo-600',
        3 => 'from-violet-500 to-violet-600',
        4 => 'from-purple-500 to-purple-600',
        5 => 'from-pink-500 to-pink-600',
        6 => 'from-rose-500 to-rose-600',
        7 => 'from-orange-500 to-orange-600',
        8 => 'from-amber-500 to-amber-600',
        ];
        @endphp
        @foreach($semesters as $sem => $data)
        <div class="bg-gradient-to-br {{ $semColors[$sem] }} rounded-2xl p-3 text-white text-center shadow-sm">
            <p class="text-2xl font-black font-display">{{ $data['totalSks'] }}</p>
            <p class="text-[11px] text-white/70 mt-0.5">Smt {{ $sem }} SKS</p>
        </div>
        @endforeach
    </div>

    {{-- Semester grids --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        @foreach($semesters as $sem => $data)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden card-hover">
            {{-- Semester header --}}
            <div class="bg-gradient-to-r {{ $semColors[$sem] }} px-5 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-xs font-medium uppercase tracking-wider">Semester</p>
                        <h2 class="font-display text-2xl font-black text-white leading-none">{{ $sem }}</h2>
                    </div>
                    <div class="text-right">
                        <div class="bg-white/20 rounded-xl px-3 py-1.5 text-white text-xs font-medium">
                            <span class="font-bold text-lg">{{ $data['totalSks'] }}</span> SKS
                        </div>
                        <p class="text-white/60 text-[11px] mt-1">{{ $data['totalMk'] }} mata kuliah</p>
                    </div>
                </div>
            </div>

            {{-- MK list --}}
            <div class="p-3">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-slate-400">
                            <th class="text-left py-1.5 px-1 font-semibold uppercase tracking-wide text-[10px]">Mata Kuliah</th>
                            <th class="text-center py-1.5 w-8 font-semibold uppercase tracking-wide text-[10px]">SKS</th>
                            <th class="text-center py-1.5 w-14 font-semibold uppercase tracking-wide text-[10px]">Kat.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($data['mataKuliahs'] as $mk)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-2 px-1">
                                <a href="{{ route('mata-kuliah.show', ['kode' => $mk->kode, 'program_id' => $program?->id]) }}"
                                    class="font-medium text-slate-700 group-hover:text-brand-700 transition-colors line-clamp-1 leading-snug">
                                    {{ $mk->nama }}
                                </a>
                                <span class="text-slate-300 text-[10px]">{{ $mk->kode }}</span>
                            </td>
                            <td class="py-2 text-center">
                                <span class="font-bold text-slate-600">{{ $mk->sks }}</span>
                            </td>
                            <td class="py-2 text-center">
                                <span class="badge text-[10px] {{ $mk->badge_class }}">{{ $mk->kategori }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-100">
                            <td class="py-2 px-1 font-bold text-slate-500 text-right text-[11px]">Total</td>
                            <td class="py-2 text-center font-black text-brand-700">{{ $data['totalSks'] }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Kategori legend --}}
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 px-6 py-4">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2">Keterangan Kategori:</span>
            @foreach(['MKF'=>'bg-purple-100 text-purple-700','MKPU'=>'bg-blue-100 text-blue-700','MKWK'=>'bg-green-100 text-green-700','MKPP'=>'bg-orange-100 text-orange-700','MKP'=>'bg-yellow-100 text-yellow-700','MKKP'=>'bg-slate-100 text-slate-600'] as $kat => $cls)
            <span class="badge {{ $cls }} text-xs">{{ $kat }}</span>
            @endforeach
        </div>
    </div>
</div>
@endsection