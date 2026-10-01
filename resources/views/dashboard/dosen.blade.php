@extends('layouts.dashboard')
@section('title','Dashboard Dosen')

@section('content')
{{-- ═══ HEADER ═══ --}}
<div class="bg-gradient-to-r from-brand-700 via-brand-800 to-violet-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-brand-200 text-sm mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
                    <span>/</span>
                    <span class="text-white font-medium">Dashboard Dosen</span>
                </div>
                <h1 class="text-3xl font-bold font-display">
                    👩‍🏫 Selamat datang, <span class="text-brand-200">{{ auth()->user()->name }}</span>
                </h1>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-brand-200 text-sm">{{ auth()->user()->jabatan ?? 'Dosen' }}</span>
                    <span class="px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-semibold ring-1 ring-white/30">
                        🎓 Dosen Pengampu
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('rps.index') }}" class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    ⚡ RPS Generator
                </a>
                <a href="{{ route('pemetaan.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 hover:bg-brand-400 rounded-lg text-sm font-semibold shadow transition">
                    🔗 Pemetaan CPL
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- ═══ STAT CARDS ═══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
        $dosenStats = [
            ['label'=>'MK Diampu','value'=>$stats['total_mk_ampu'],'icon'=>'📖','bg'=>'bg-brand-50','iconBg'=>'bg-brand-100 text-brand-700','val'=>'text-brand-700','border'=>'border-brand-200'],
            ['label'=>'Total SKS','value'=>$stats['total_sks_ampu'],'icon'=>'⚖️','bg'=>'bg-indigo-50','iconBg'=>'bg-indigo-100 text-indigo-700','val'=>'text-indigo-700','border'=>'border-indigo-200'],
            ['label'=>'Total CPMK','value'=>$stats['total_cpmk'],'icon'=>'📋','bg'=>'bg-violet-50','iconBg'=>'bg-violet-100 text-violet-700','val'=>'text-violet-700','border'=>'border-violet-200'],
            ['label'=>'Sub CPMK','value'=>$stats['total_subcpmk'],'icon'=>'🔖','bg'=>'bg-sky-50','iconBg'=>'bg-sky-100 text-sky-700','val'=>'text-sky-700','border'=>'border-sky-200'],
        ];
        @endphp
        @foreach($dosenStats as $card)
        <div class="card-hover {{ $card['bg'] }} rounded-2xl p-5 border {{ $card['border'] }} shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <span class="w-10 h-10 {{ $card['iconBg'] }} rounded-xl flex items-center justify-center text-xl">{{ $card['icon'] }}</span>
            </div>
            <p class="text-3xl font-bold {{ $card['val'] }} font-display">{{ $card['value'] }}</p>
            <p class="text-sm font-semibold text-slate-700 mt-1">{{ $card['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ═══ MATA KULIAH YANG DIAMPU ═══ --}}
    <div>
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 bg-brand-100 rounded-lg flex items-center justify-center text-sm">📚</span>
            Mata Kuliah yang Saya Ampu
        </h2>

        @if($mkAmpu->isEmpty())
        <div class="bg-slate-50 rounded-2xl border border-dashed border-slate-300 p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">📭</div>
            <h3 class="text-base font-semibold text-slate-600">Belum Ada Mata Kuliah</h3>
            <p class="text-sm text-slate-400 mt-1">Anda belum diampu sebagai PJMK pada mata kuliah manapun.</p>
            <a href="{{ route('mata-kuliah.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold hover:bg-brand-700 transition">
                Lihat Semua MK
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($mkAmpu as $mk)
            @php
                $bobotTotal = $bobotValidity[$mk->kode] ?? 0;
                $bobotValid = $bobotTotal == 100;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm card-hover overflow-hidden">
                {{-- Card Header --}}
                <div class="px-5 pt-5 pb-3">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex gap-2 flex-wrap">
                            <span class="text-xs bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full font-semibold">
                                Sem {{ $mk->semester }}
                            </span>
                            @if($mk->kategori)
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">{{ $mk->kategori }}</span>
                            @endif
                            @if($mk->is_mbkm)
                            <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-semibold">MBKM</span>
                            @endif
                        </div>
                        <span class="font-mono text-xs text-slate-400 shrink-0">{{ $mk->kode }}</span>
                    </div>
                    <h3 class="font-bold text-slate-800 text-base leading-snug">{{ $mk->nama }}</h3>
                    <p class="text-xs text-slate-500 mt-1">{{ $mk->sks }} SKS · {{ $mk->pjmk ?? '-' }}</p>
                </div>

                {{-- CPL Badges --}}
                @if($mk->cpls->isNotEmpty())
                <div class="px-5 pb-3 flex flex-wrap gap-1">
                    @foreach($mk->cpls->take(5) as $cpl)
                    <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-md font-medium">{{ $cpl->kode }}</span>
                    @endforeach
                    @if($mk->cpls->count() > 5)
                    <span class="text-xs text-slate-400">+{{ $mk->cpls->count() - 5 }} lagi</span>
                    @endif
                </div>
                @endif

                {{-- Stats row --}}
                <div class="px-5 pb-4 flex items-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1">
                        <span class="w-4 h-4 bg-sky-100 rounded text-sky-600 flex items-center justify-center text-[10px]">C</span>
                        {{ $mk->cpmks->count() }} CPMK
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-4 h-4 bg-violet-100 rounded text-violet-600 flex items-center justify-center text-[10px]">S</span>
                        {{ $mk->cpmks->sum(fn($c) => $c->subCpmks->count()) }} SubCPMK
                    </span>
                    <span class="ml-auto flex items-center gap-1 {{ $bobotValid ? 'text-green-600' : ($bobotTotal > 0 ? 'text-red-500' : 'text-slate-400') }}">
                        @if($bobotValid)
                            ✅ Bobot Valid
                        @elseif($bobotTotal > 0)
                            ❌ Bobot {{ $bobotTotal }}%
                        @else
                            ⚪ Belum ada bobot
                        @endif
                    </span>
                </div>

                {{-- Action Buttons --}}
                <div class="border-t border-slate-50 px-5 py-3 flex gap-2 bg-slate-50/50">
                    <a href="{{ route('rps.show', $mk->kode) }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg transition">
                        📄 Preview RPS
                    </a>
                    <a href="{{ route('rps.pdf', $mk->kode) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold rounded-lg transition border border-red-200">
                        📥 PDF
                    </a>
                    <a href="{{ route('rps.docx', $mk->kode) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-sky-50 hover:bg-sky-100 text-sky-600 text-xs font-semibold rounded-lg transition border border-sky-200">
                        📝 Word
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ═══ CPL REFERENCE ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center text-sm">🎯</span>
                Referensi CPL Program Studi
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi CPL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($allCpl as $cpl)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-brand-100 text-brand-700 px-2 py-1 rounded-lg font-bold">{{ $cpl->kode }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $cpl->deskripsi ?? $cpl->nama ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="text-center py-8 text-slate-400">Belum ada data CPL</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
