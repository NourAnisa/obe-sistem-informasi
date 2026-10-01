@extends('layouts.dashboard')
@section('title', 'Rencana Pembelajaran Semester (RPS)')
@section('content')

{{-- Hero Header --}}
<div class="bg-gradient-to-r from-emerald-800 to-teal-900 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3 text-emerald-300 text-sm mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
            <span>›</span><span class="text-white">Rencana Pembelajaran Semester (RPS)</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold text-white">⚡ Rencana Pembelajaran Semester (RPS)</h1>
                <p class="text-emerald-200 mt-2">Buat Rencana Pembelajaran Semester sesuai Template UNISM TA 2025/2026 secara otomatis dari database OBE.</p>
            </div>
            <div class="flex gap-2">
                <span class="bg-white/10 ring-1 ring-white/20 px-4 py-2 rounded-lg text-white text-sm">
                    📄 PDF · 📝 Word · 👁 Preview
                </span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Info Banner --}}
    @if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start space-x-3">
        <span class="text-red-500 text-xl">⚠️</span>
        <div>
            <p class="font-semibold text-red-800">Terjadi Kesalahan</p>
            <p class="text-red-600 text-sm mt-0.5">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- Per Semester --}}
    @foreach($mataKuliahs as $semester => $mks)
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
                <span class="text-white font-black font-display">{{ $semester }}</span>
            </div>
            <div>
                <h2 class="font-display font-bold text-slate-800 text-lg">Semester {{ $semester }}</h2>
                <p class="text-slate-400 text-xs">{{ $mks->count() }} Mata Kuliah</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Kode</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama Mata Kuliah</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide w-16">SKS</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">CPL</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">CPMK</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide w-56">Aksi RPS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($mks as $mk)
                    <tr class="hover:bg-emerald-50/30 transition-colors group">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs font-semibold bg-emerald-50 text-emerald-700 px-2 py-1 rounded-lg">{{ $mk->kode }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('mata-kuliah.show', $mk->kode) }}" class="font-semibold text-slate-700 hover:text-brand-700 transition">
                                {{ $mk->nama }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-bold text-slate-600">{{ $mk->sks }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($mk->cpls_count > 0)
                            <span class="badge bg-blue-100 text-blue-700">{{ $mk->cpls_count }} CPL</span>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($mk->cpmks_count > 0)
                            <span class="badge bg-orange-100 text-orange-700">{{ $mk->cpmks_count }} CPMK</span>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('rps.show', $mk->kode) }}"
                                   class="inline-flex items-center space-x-1 px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Preview</span>
                                </a>
                                <a href="{{ route('rps.pdf', $mk->kode) }}"
                                   class="inline-flex items-center space-x-1 px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span>PDF</span>
                                </a>
                                <a href="{{ route('rps.docx', $mk->kode) }}"
                                   class="inline-flex items-center space-x-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Word</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</div>
@endsection
