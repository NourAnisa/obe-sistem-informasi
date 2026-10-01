@extends('layouts.dashboard')
@section('title', 'Kurikulum per Semester')
@section('breadcrumb', 'Kurikulum')

@section('content')
<div class="max-w-7xl mx-auto">

    @include('components.program-switcher', ['currentRoute' => 'kurikulum.index'])

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Kurikulum per Semester</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $semesters->sum(fn($d) => $d['totalMk']) }} Mata Kuliah · 
            {{ $semesters->sum(fn($d) => $d['totalSks']) }} SKS · 
            8 Semester · 
            TA {{ config('obe.tahun_akademik') }} · 
            <strong class="text-gray-700">{{ $program?->nama ?? config('obe.prodi') }}</strong>
        </p>
    </div>

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

    {{-- Summary SKS per Semester --}}
    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 mb-6">
        @foreach($semesters as $sem => $data)
        <div class="bg-gradient-to-br {{ $semColors[$sem] }} rounded-xl p-3 text-white text-center shadow-sm">
            <p class="text-2xl font-black">{{ $data['totalSks'] }}</p>
            <p class="text-[11px] text-white/75 mt-0.5">Smt {{ $sem }}</p>
        </div>
        @endforeach
    </div>

    {{-- Semester Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($semesters as $sem => $data)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r {{ $semColors[$sem] }} px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 text-[10px] font-semibold uppercase tracking-wider">Semester</p>
                        <h2 class="text-2xl font-black text-white leading-none">{{ $sem }}</h2>
                    </div>
                    <div class="text-right">
                        <div class="bg-white/20 rounded-lg px-2 py-1 text-white text-xs font-medium">
                            <span class="font-bold text-base">{{ $data['totalSks'] }}</span> SKS
                        </div>
                        <p class="text-white/65 text-[10px] mt-1">{{ $data['totalMk'] }} mata kuliah</p>
                    </div>
                </div>
            </div>
            <div class="p-3">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-gray-400">
                            <th class="text-left py-1 px-1 font-semibold uppercase tracking-wide text-[10px]">Mata Kuliah</th>
                            <th class="text-center py-1 w-8 font-semibold uppercase tracking-wide text-[10px]">SKS</th>
                            <th class="text-center py-1 w-14 font-semibold uppercase tracking-wide text-[10px]">Kat.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($data['mataKuliahs'] as $mk)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="py-1.5 px-1">
                                <a href="{{ route('mata-kuliah.show', ['kode' => $mk->kode, 'program_id' => $program?->id]) }}"
                                    class="font-medium text-gray-700 group-hover:text-blue-600 transition line-clamp-1 leading-snug">
                                    {{ $mk->nama }}
                                </a>
                                <span class="text-gray-300 text-[10px]">{{ $mk->kode }}</span>
                            </td>
                            <td class="py-1.5 text-center font-bold text-gray-600">{{ $mk->sks }}</td>
                            <td class="py-1.5 text-center">
                                <span class="badge text-[10px] {{ $mk->badge_class }}">{{ $mk->kategori }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-100">
                            <td class="py-1.5 px-1 font-bold text-gray-400 text-right text-[11px]">Total</td>
                            <td class="py-1.5 text-center font-black text-blue-700">{{ $data['totalSks'] }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Kategori legend --}}
    <div class="mt-5 bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2">Keterangan:</span>
            @foreach([
            'MKF' => 'bg-purple-100 text-purple-700',
            'MKPU' => 'bg-blue-100 text-blue-700',
            'MKWK' => 'bg-green-100 text-green-700',
            'MKPP' => 'bg-orange-100 text-orange-700',
            'MKP' => 'bg-yellow-100 text-yellow-700',
            'MKKP' => 'bg-gray-100 text-gray-600',
            ] as $kat => $cls)
            <span class="badge {{ $cls }} text-xs">{{ $kat }}</span>
            @endforeach
        </div>
    </div>
</div>
@endsection