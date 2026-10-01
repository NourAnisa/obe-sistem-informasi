@extends('layouts.dashboard')
@section('title', 'Pemetaan CPL–CPMK–Sub-CPMK')
@section('breadcrumb', 'Pemetaan CPL')

@section('content')
<div class="max-w-5xl mx-auto">

    @include('components.program-switcher', ['currentRoute' => 'pemetaan.index'])

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pemetaan CPL → CPMK → Sub-CPMK</h1>
        <p class="text-sm text-gray-500 mt-1">Hierarki Capaian Pembelajaran Kurikulum OBE {{ $program?->nama ?? auth()->user()->program?->nama ?? config('obe.prodi') }}</p>
    </div>

    <div class="space-y-3" x-data="{}">
        @foreach($cpls as $cpl)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200" x-data="{ open: false }">
            <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 rounded-xl transition">
                <div class="flex items-center space-x-4">
                    <span class="w-20 text-center font-bold text-blue-700 bg-blue-50 rounded-lg py-2 text-sm shrink-0">{{ $cpl->kode }}</span>
                    <div>
                        <span class="badge {{ $cpl->badge_class }} mb-1">{{ $cpl->kategori }}</span>
                        <p class="text-sm text-gray-700 pr-4">{{ Str::limit($cpl->deskripsi, 160) }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3 shrink-0">
                    <span class="text-xs text-gray-400">{{ $cpl->cpmks->count() }} CPMK</span>
                    <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            <div x-show="open" x-cloak class="border-t border-gray-100 p-4 space-y-4">
                @forelse($cpl->cpmks as $cpmk)
                <div class="pl-4 border-l-4 border-orange-300" x-data="{ openSub: false }">
                    <button @click="openSub = !openSub" class="w-full flex items-center justify-between text-left py-1">
                        <div class="flex items-start space-x-3">
                            <span class="badge bg-orange-100 text-orange-700 shrink-0 mt-0.5">{{ $cpmk->kode }}</span>
                            <span class="text-sm text-gray-700">{{ $cpmk->deskripsi }}</span>
                        </div>
                        @if($cpmk->subCpmks->count())
                        <div class="flex items-center space-x-2 ml-2 shrink-0">
                            <span class="text-xs text-gray-400">{{ $cpmk->subCpmks->count() }} Sub</span>
                            <svg :class="openSub ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        @endif
                    </button>

                    @if($cpmk->mataKuliahs->count())
                    <div class="flex flex-wrap gap-1 mt-2">
                        @foreach($cpmk->mataKuliahs->sortBy('semester') as $mk)
                        <a href="{{ route('mata-kuliah.show', ['kode' => $mk->kode, 'program_id' => $program?->id]) }}"
                            class="badge bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition text-xs">
                            {{ $mk->kode }}
                        </a>
                        @endforeach
                    </div>
                    @endif

                    @if($cpmk->subCpmks->count())
                    <div x-show="openSub" x-cloak class="mt-3 pl-4 border-l-2 border-yellow-200 space-y-1">
                        @foreach($cpmk->subCpmks as $sub)
                        <div class="flex items-start space-x-2">
                            <span class="badge bg-yellow-100 text-yellow-700 shrink-0 mt-0.5">{{ $sub->kode }}</span>
                            <span class="text-gray-600 text-xs">{{ $sub->deskripsi }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-sm text-gray-400 italic pl-4">Belum ada CPMK untuk CPL ini.</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection