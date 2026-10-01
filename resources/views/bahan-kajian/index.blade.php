@extends('layouts.public')
@section('title','Bahan Kajian')
@section('content')

@include('components.program-switcher', ['currentRoute' => 'bahan-kajian.index'])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Bahan Kajian</h1>
        <p class="text-gray-500 mt-1">{{ $bahanKajians->count() }} Bahan Kajian &middot; {{ $program?->nama ?? config('obe.prodi') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-12">
        @foreach($bahanKajians as $bk)
        <div class="bg-white rounded-xl shadow border border-gray-100 p-5 hover:shadow-md transition" x-data="{ open: false }">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <span class="w-12 h-12 bg-unism-light rounded-full flex items-center justify-center text-unism-primary font-bold text-sm shrink-0">{{ $bk->kode }}</span>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-sm">{{ $bk->nama }}</h3>
                        <span class="badge {{ $bk->referensi === 'IS2020' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }} mt-1">{{ $bk->referensi }}</span>
                    </div>
                </div>
                <button @click="open=!open" class="text-gray-400 hover:text-gray-600">
                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
            <div x-show="open" x-cloak class="border-t pt-3 mt-1">
                @if($bk->cpls->count())
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">CPL Terkait</p>
                <div class="flex flex-wrap gap-1 mb-3">
                    @foreach($bk->cpls as $cpl)
                    <span class="badge {{ $cpl->badge_class }}">{{ $cpl->kode }}</span>
                    @endforeach
                </div>
                @endif
                @if($bk->mataKuliahs->count())
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Mata Kuliah ({{ $bk->mataKuliahs->count() }})</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($bk->mataKuliahs as $mk)
                    <a href="{{ route('mata-kuliah.show', $mk->kode) }}" class="badge bg-gray-100 text-gray-700 hover:bg-unism-light hover:text-unism-primary transition text-xs">{{ $mk->kode }}</a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Matriks BK vs CPL --}}
    <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Matriks Bahan Kajian × CPL</h2>
        <table class="min-w-full text-xs border-collapse">
            <thead>
                <tr class="bg-unism-primary text-white">
                    <th class="px-3 py-2 text-left font-semibold border border-unism-secondary">BK</th>
                    <th class="px-3 py-2 text-left font-semibold border border-unism-secondary">Nama</th>
                    @foreach($cpls as $cpl)
                    <th class="px-3 py-2 text-center font-semibold border border-unism-secondary">{{ $cpl->kode }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($bahanKajians as $bk)
                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                    <td class="px-3 py-2 font-semibold text-unism-primary border border-gray-200">{{ $bk->kode }}</td>
                    <td class="px-3 py-2 border border-gray-200">{{ $bk->nama }}</td>
                    @foreach($cpls as $cpl)
                    <td class="px-3 py-2 text-center border border-gray-200">
                        @if($bk->cpls->contains('id', $cpl->id))
                        <span class="text-green-600 font-bold">✓</span>
                        @else
                        <span class="text-gray-200">·</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection