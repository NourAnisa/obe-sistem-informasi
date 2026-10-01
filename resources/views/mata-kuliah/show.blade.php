@extends('layouts.public')
@section('title', $mk->nama)
@section('content')

{{-- Page Header --}}
<div class="bg-gradient-to-r from-brand-800 to-brand-900 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3 text-brand-300 text-sm mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
            <span>›</span>
            <a href="{{ route('mata-kuliah.index') }}" class="hover:text-white">Mata Kuliah</a>
            <span>›</span>
            <span class="text-white font-mono">{{ $mk->kode }}</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <div class="flex flex-wrap gap-2 mb-2">
                    <span class="badge bg-white/15 text-white ring-1 ring-white/25">Semester {{ $mk->semester }}</span>
                    <span class="badge bg-white/15 text-white ring-1 ring-white/25">{{ $mk->sks }} SKS</span>
                    <span class="badge {{ $mk->badge_class }}">{{ $mk->kategori }}</span>
                    @if($mk->is_mbkm)<span class="badge bg-green-400/20 text-green-200 ring-1 ring-green-400/30">MBKM</span>@endif
                </div>
                <h1 class="font-display text-2xl md:text-3xl font-bold text-white">{{ $mk->nama }}</h1>
                <p class="text-brand-200 mt-1 text-sm">PJMK: <span class="font-semibold text-white">{{ $mk->pjmk }}</span></p>
            </div>
            {{-- RPS ACTION BUTTONS (dosen & admin only) --}}
            @auth
            @if(in_array(auth()->user()->role, ['dosen', 'admin', 'kaprodi', 'akademik']))
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('distribusi-dosen.index', ['mk_id' => $mk->id]) }}"
                   class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-500/80 text-white rounded-xl font-semibold text-sm hover:bg-indigo-500 transition ring-1 ring-white/20">
                    <span>👥 Dosen Pengampu</span>
                </a>
                @if(in_array(auth()->user()->role, ['dosen', 'admin']))
                <a href="{{ route('rps.show', $mk->kode) }}"
                   class="inline-flex items-center space-x-2 px-5 py-2.5 bg-emerald-500 text-white rounded-xl font-semibold text-sm hover:bg-emerald-400 transition shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Generate RPS</span>
                </a>
                <a href="{{ route('rps.pdf', $mk->kode) }}"
                   class="inline-flex items-center space-x-2 px-4 py-2.5 bg-red-500/80 text-white rounded-xl font-semibold text-sm hover:bg-red-500 transition ring-1 ring-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>PDF</span>
                </a>
                <a href="{{ route('rps.docx', $mk->kode) }}"
                   class="inline-flex items-center space-x-2 px-4 py-2.5 bg-blue-500/80 text-white rounded-xl font-semibold text-sm hover:bg-blue-500 transition ring-1 ring-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Word</span>
                </a>
                @endif
            </div>
            @endif
            @endauth
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detail --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Info SKS --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Informasi Umum</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Kode</dt><dd class="font-mono font-medium text-unism-primary">{{ $mk->kode }}</dd></div>
                    <div><dt class="text-gray-500">SKS</dt><dd class="font-semibold">{{ $mk->sks }} SKS</dd></div>
                    @if($mk->sks_teori)
                    <div><dt class="text-gray-500">SKS Teori</dt><dd>{{ $mk->sks_teori }} SKS</dd></div>
                    @endif
                    @if($mk->sks_praktikum)
                    <div><dt class="text-gray-500">SKS Praktikum</dt><dd>{{ $mk->sks_praktikum }} SKS</dd></div>
                    @endif
                    <div><dt class="text-gray-500">Semester</dt><dd>Semester {{ $mk->semester }}</dd></div>
                    <div><dt class="text-gray-500">Kategori</dt><dd><span class="badge {{ $mk->badge_class }}">{{ $mk->kategori }}</span></dd></div>
                </dl>
            </div>

            {{-- CPMK --}}
            @if($mk->cpmks->count())
            <div class="bg-white rounded-xl shadow p-5">
                <h2 class="text-lg font-bold text-gray-800 mb-4">CPMK & Sub-CPMK</h2>
                <div class="space-y-4">
                    @foreach($mk->cpmks as $cpmk)
                    <div class="border border-gray-100 rounded-lg p-4" x-data="{ open: false }">
                        <button @click="open=!open" class="w-full flex items-start justify-between text-left">
                            <div>
                                <span class="badge bg-orange-100 text-orange-700 mr-2">{{ $cpmk->kode }}</span>
                                <span class="text-sm text-gray-700">{{ $cpmk->deskripsi }}</span>
                            </div>
                            @if($cpmk->subCpmks->count())
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-400 shrink-0 mt-1 ml-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            @endif
                        </button>
                        @if($cpmk->subCpmks->count())
                        <div x-show="open" x-cloak class="mt-3 pl-4 border-l-2 border-orange-200 space-y-2">
                            @foreach($cpmk->subCpmks as $sub)
                            <div class="text-sm">
                                <span class="badge bg-yellow-100 text-yellow-700 mr-2">{{ $sub->kode }}</span>
                                <span class="text-gray-600">{{ $sub->deskripsi }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Bobot Penilaian --}}
            @if($mk->bobotPenilaians->count())
            <div class="bg-white rounded-xl shadow p-5 overflow-x-auto">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Bobot Penilaian</h2>
                <table class="min-w-full text-sm border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left border border-gray-200 font-semibold text-gray-600">CPMK</th>
                            <th class="px-3 py-2 text-center border border-gray-200 font-semibold text-gray-600">Tugas</th>
                            <th class="px-3 py-2 text-center border border-gray-200 font-semibold text-gray-600">UTS</th>
                            <th class="px-3 py-2 text-center border border-gray-200 font-semibold text-gray-600">UAS</th>
                            <th class="px-3 py-2 text-center border border-gray-200 font-semibold text-gray-600">Partisipatif</th>
                            <th class="px-3 py-2 text-center border border-gray-200 font-semibold text-gray-600">Proyek</th>
                            <th class="px-3 py-2 text-center border border-gray-200 font-semibold text-gray-600">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mk->bobotPenilaians as $bp)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="px-3 py-2 border border-gray-200 font-medium text-unism-primary">{{ $bp->cpmk?->kode ?? '-' }}</td>
                            <td class="px-3 py-2 border border-gray-200 text-center">{{ $bp->bobot_tugas }}%</td>
                            <td class="px-3 py-2 border border-gray-200 text-center">{{ $bp->bobot_uts }}%</td>
                            <td class="px-3 py-2 border border-gray-200 text-center">{{ $bp->bobot_uas }}%</td>
                            <td class="px-3 py-2 border border-gray-200 text-center">{{ $bp->bobot_partisipatif }}%</td>
                            <td class="px-3 py-2 border border-gray-200 text-center">{{ $bp->bobot_proyek }}%</td>
                            <td class="px-3 py-2 border border-gray-200 text-center font-bold {{ $bp->total == 100 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $bp->total }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- CPL --}}
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-bold text-gray-800 mb-3">CPL yang Dicakup</h3>
                @forelse($mk->cpls as $cpl)
                <div class="flex items-start space-x-2 mb-2">
                    <span class="badge {{ $cpl->badge_class }} shrink-0">{{ $cpl->kode }}</span>
                    <span class="text-xs text-gray-600">{{ Str::limit($cpl->deskripsi, 70) }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-400">Belum ada CPL terkait.</p>
                @endforelse
            </div>

            {{-- BK --}}
            @if($mk->bahanKajians->count())
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-bold text-gray-800 mb-3">Bahan Kajian</h3>
                @foreach($mk->bahanKajians as $bk)
                <div class="flex items-center space-x-2 mb-2">
                    <span class="badge bg-blue-100 text-blue-700 shrink-0">{{ $bk->kode }}</span>
                    <span class="text-xs text-gray-600">{{ $bk->nama }}</span>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Back --}}
            <a href="{{ route('mata-kuliah.index') }}" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                ← Kembali ke Daftar MK
            </a>
        </div>
    </div>
</div>
@endsection