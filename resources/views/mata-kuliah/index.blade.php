@extends('layouts.public')
@section('title','Mata Kuliah')
@section('content')

@include('components.program-switcher', ['currentRoute' => 'mata-kuliah.index'])

{{-- Page Header --}}
<div class="bg-gradient-to-r from-brand-800 to-brand-900 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3 text-brand-300 text-sm mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
            <span>›</span><span class="text-white">Mata Kuliah</span>
        </div>
        <h1 class="font-display text-3xl font-bold text-white">Daftar Mata Kuliah</h1>
        <p class="text-brand-200 mt-2">{{ $mataKuliahs->total() }} Mata Kuliah · {{ $program?->nama ?? config('obe.prodi') }}</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Filter Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
        <form method="GET" action="{{ route('mata-kuliah.index') }}" class="flex flex-wrap gap-4 items-end">
            @if($program)<input type="hidden" name="program_id" value="{{ $program->id }}">@endif
            <div class="min-w-36">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Semester</label>
                <select name="semester" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                    <option value="">Semua Semester</option>
                    @for($i=1;$i<=8;$i++)
                        <option value="{{ $i }}" {{ request('semester')==$i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                </select>
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Kategori</label>
                <select name="kategori" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                    <option value="{{ $kat }}" {{ request('kategori')==$kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-56">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">Cari Mata Kuliah</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau kode MK..."
                        class="w-full pl-9 pr-4 border border-slate-200 rounded-xl py-2.5 text-sm bg-slate-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                </div>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 transition shadow-sm hover:shadow">
                    Filter
                </button>
                @if(request()->hasAny(['semester','kategori','search']))
                <a href="{{ route('mata-kuliah.index') }}" class="px-4 py-2.5 border border-slate-200 text-slate-500 rounded-xl text-sm hover:bg-slate-50 transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Results count --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500">
            Menampilkan <span class="font-semibold text-slate-700">{{ $mataKuliahs->total() }}</span> mata kuliah
        </p>
        <div class="flex gap-2">
            @php $katBadge = ['MKF'=>'bg-purple-100 text-purple-700','MKPU'=>'bg-blue-100 text-blue-700','MKWK'=>'bg-green-100 text-green-700','MKPP'=>'bg-orange-100 text-orange-700','MKP'=>'bg-yellow-100 text-yellow-700','MKKP'=>'bg-slate-100 text-slate-700']; @endphp
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-10">#</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Kode</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama Mata Kuliah</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide w-16">SKS</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide w-16">Smt</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Kategori</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">CPL</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">PJMK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($mataKuliahs as $mk)
                    <tr class="hover:bg-brand-50/40 transition-colors group">
                        <td class="px-5 py-3.5 text-slate-400 text-xs">{{ $mataKuliahs->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('mata-kuliah.show', $mk->kode) }}"
                                class="font-mono text-xs font-semibold text-brand-600 hover:text-brand-800 bg-brand-50 group-hover:bg-brand-100 px-2 py-1 rounded-lg transition">
                                {{ $mk->kode }}
                            </a>
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('mata-kuliah.show', $mk->kode) }}"
                                class="font-semibold text-slate-800 hover:text-brand-700 transition">
                                {{ $mk->nama }}
                            </a>
                            @if($mk->sks_teori && $mk->sks_praktikum)
                            <span class="ml-2 text-xs text-slate-400">({{ $mk->sks_teori }}T + {{ $mk->sks_praktikum }}P)</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 bg-brand-50 text-brand-700 font-bold text-sm rounded-lg">{{ $mk->sks }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 bg-slate-100 text-slate-600 font-semibold text-xs rounded-full">{{ $mk->semester }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="badge {{ $mk->badge_class }}">{{ $mk->kategori }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex flex-wrap gap-1">
                                @foreach($mk->cpls as $cpl)
                                <span class="badge {{ $cpl->badge_class }}">{{ $cpl->kode }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs whitespace-nowrap">
                            {{ Str::before($mk->pjmk, ',') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="text-5xl mb-3">🔍</div>
                            <p class="text-slate-500 font-medium">Tidak ada mata kuliah ditemukan</p>
                            <a href="{{ route('mata-kuliah.index') }}" class="text-brand-600 text-sm hover:underline mt-2 inline-block">Reset filter</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mataKuliahs->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $mataKuliahs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection