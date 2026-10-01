@extends('layouts.dashboard')
@section('title','Dashboard Kemahasiswaan')

@section('content')
{{-- ═══ HEADER ═══ --}}
<div class="bg-gradient-to-r from-teal-700 via-teal-800 to-purple-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-teal-200 text-sm mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
                    <span>/</span>
                    <span class="text-white font-medium">Dashboard Kemahasiswaan</span>
                </div>
                <h1 class="text-3xl font-bold font-display">
                    🧑‍🎓 Selamat datang, <span class="text-teal-200">{{ auth()->user()->name }}</span>
                </h1>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-teal-200 text-sm">Bagian Kemahasiswaan · S1 Sistem Informasi UNISM</span>
                    <span class="px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-semibold ring-1 ring-white/30">TA 2025/2026</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('mbkm.index') }}" class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    🌐 Info MBKM
                </a>
                <a href="{{ route('cpl.index') }}" class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-400 rounded-lg text-sm font-semibold shadow transition">
                    🎯 Lihat CPL
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- ═══ STAT CARDS ═══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
        $kmStats = [
            ['label'=>'MK MBKM','value'=>$stats['total_mbkm_mk'],'icon'=>'🌐','bg'=>'bg-teal-50','iconBg'=>'bg-teal-100 text-teal-700','val'=>'text-teal-700','border'=>'border-teal-200','sub'=>'Mata kuliah MBKM'],
            ['label'=>'Total SKS MBKM','value'=>$stats['total_mbkm_sks'],'icon'=>'⚖️','bg'=>'bg-violet-50','iconBg'=>'bg-violet-100 text-violet-700','val'=>'text-violet-700','border'=>'border-violet-200','sub'=>'Beban studi MBKM'],
            ['label'=>'Bentuk MBKM','value'=>$stats['total_bentuk_mbkm'],'icon'=>'🎪','bg'=>'bg-amber-50','iconBg'=>'bg-amber-100 text-amber-700','val'=>'text-amber-700','border'=>'border-amber-200','sub'=>'Jenis kegiatan'],
            ['label'=>'Max SKS MBKM','value'=>$stats['max_sks_mbkm'],'icon'=>'🏆','bg'=>'bg-emerald-50','iconBg'=>'bg-emerald-100 text-emerald-700','val'=>'text-emerald-700','border'=>'border-emerald-200','sub'=>'SKS maks per program'],
        ];
        @endphp
        @foreach($kmStats as $card)
        <div class="card-hover {{ $card['bg'] }} rounded-2xl p-5 border {{ $card['border'] }} shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <span class="w-10 h-10 {{ $card['iconBg'] }} rounded-xl flex items-center justify-center text-xl">{{ $card['icon'] }}</span>
            </div>
            <p class="text-3xl font-bold {{ $card['val'] }} font-display">{{ $card['value'] }}</p>
            <p class="text-sm font-semibold text-slate-700 mt-1">{{ $card['label'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $card['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ═══ MATA KULIAH MBKM ═══ --}}
    <div>
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-sm">🌐</span>
            Mata Kuliah MBKM
        </h2>

        @if($mbkmMk->isEmpty())
        <div class="bg-slate-50 rounded-2xl border border-dashed border-slate-300 p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4">🌐</div>
            <h3 class="text-base font-semibold text-slate-600">Belum Ada MK MBKM</h3>
            <p class="text-sm text-slate-400 mt-1">Belum ada mata kuliah yang ditandai sebagai MBKM.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($mbkmMk as $mk)
            <div class="bg-white rounded-2xl border border-teal-100 shadow-sm card-hover overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-teal-400 to-purple-500"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex gap-1.5 flex-wrap">
                            <span class="text-xs bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full font-semibold">
                                Sem {{ $mk->semester }}
                            </span>
                            <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-semibold">MBKM</span>
                            @if($mk->kategori)
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">{{ $mk->kategori }}</span>
                            @endif
                        </div>
                        <span class="font-mono text-xs text-slate-400 shrink-0">{{ $mk->kode }}</span>
                    </div>
                    <h3 class="font-bold text-slate-800 text-base leading-snug">{{ $mk->nama }}</h3>
                    <p class="text-xs text-slate-500 mt-1 mb-3">{{ $mk->sks }} SKS · {{ $mk->pjmk ?? '-' }}</p>

                    {{-- CPL Badges --}}
                    @if($mk->cpls->isNotEmpty())
                    <div class="flex flex-wrap gap-1">
                        @foreach($mk->cpls as $cpl)
                        <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-md font-medium">{{ $cpl->kode }}</span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-slate-400 italic">Belum ada CPL dipetakan</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ═══ BENTUK KEGIATAN MBKM TABLE ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center text-sm">🎪</span>
                Bentuk Kegiatan MBKM/BKP
            </h3>
        </div>
        @if($mbkmActivities->isEmpty())
        <div class="p-8 text-center text-slate-400">Belum ada data program MBKM.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center font-semibold w-12">No</th>
                        <th class="px-4 py-3 text-left font-semibold">Bentuk Kegiatan</th>
                        <th class="px-4 py-3 text-center font-semibold">SKS Reguler</th>
                        <th class="px-4 py-3 text-center font-semibold">SKS MBKM Maks</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                        <th class="px-4 py-3 text-center font-semibold">Konversi MK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($mbkmActivities as $i => $mbkm)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-center text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-slate-800">{{ $mbkm->bentuk_kegiatan ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-slate-600 font-medium">{{ $mbkm->sks_reguler ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg">{{ $mbkm->sks_mbkm_maks ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 max-w-xs">{{ Str::limit($mbkm->deskripsi ?? '-', 80) }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">
                            {{ $mbkm->konversi_mk ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ═══ CPL MONITORING ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center text-sm">🎯</span>
                Monitoring CPL melalui MBKM
            </h3>
            <p class="text-xs text-slate-400 mt-1">CPL yang dapat dicapai melalui program MBKM</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Kode CPL</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                        <th class="px-4 py-3 text-center font-semibold">MK Terkait</th>
                        <th class="px-4 py-3 text-left font-semibold">Coverage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($cplList as $cpl)
                    @php
                        $maxMk = $cplList->max('mata_kuliahs_count') ?: 1;
                        $pct = round(($cpl->mata_kuliahs_count / $maxMk) * 100);
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-violet-100 text-violet-700 px-2 py-1 rounded-lg font-bold">{{ $cpl->kode }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ Str::limit($cpl->deskripsi ?? $cpl->nama ?? '-', 70) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ $cpl->mata_kuliahs_count > 0 ? 'text-teal-700' : 'text-slate-400' }}">{{ $cpl->mata_kuliahs_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-teal-500" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-slate-400 w-8">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-8 text-slate-400">Belum ada data CPL</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ INFO AKADEMIK (Coming Soon) ═══ --}}
    <div>
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center text-sm">🚀</span>
            Fitur Mendatang
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @php
            $comingSoon = [
                ['icon'=>'🏆','title'=>'Prestasi Mahasiswa','desc'=>'Monitoring prestasi akademik dan non-akademik mahasiswa'],
                ['icon'=>'🎭','title'=>'UKM & Organisasi','desc'=>'Data keaktifan Unit Kegiatan Mahasiswa dan organisasi kampus'],
                ['icon'=>'📜','title'=>'Sertifikasi','desc'=>'Tracking sertifikasi kompetensi dan profesi mahasiswa'],
            ];
            @endphp
            @foreach($comingSoon as $feature)
            <div class="bg-slate-50 rounded-2xl border border-dashed border-slate-200 p-6 relative overflow-hidden">
                <div class="absolute top-3 right-3">
                    <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full font-semibold ring-1 ring-purple-200">Coming Soon</span>
                </div>
                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-2xl mb-4">{{ $feature['icon'] }}</div>
                <h4 class="font-bold text-slate-700 mb-1">{{ $feature['title'] }}</h4>
                <p class="text-xs text-slate-400 leading-relaxed">{{ $feature['desc'] }}</p>
                <div class="mt-4 h-1 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-purple-300 to-teal-300 rounded-full" style="width: 20%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-1">Dalam pengembangan...</p>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
