@extends('layouts.public')

@section('title', 'Beranda — OBE ' . ($prodiInfo['nama'] ?? config('obe.prodi')) . ' ' . config('obe.universitas_singkat'))

@section('content')

{{-- ═══════ HERO ═══════ --}}
<section class="relative bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-brand-300 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-400 rounded-full blur-3xl opacity-20"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-brand-200 text-sm font-medium ring-1 ring-white/20 mb-6">
                    🎓 Permendikbudristek No. 53 Tahun 2023 · IAPS 4.0
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black font-display leading-tight mb-6">
                    Kurikulum OBE<br>
                    <span class="text-brand-300">{{ $prodiInfo['nama'] ?? config('obe.prodi') }}</span>
                </h1>
                <p class="text-brand-100 text-lg sm:text-xl leading-relaxed mb-8 max-w-2xl">
                    Platform digital kurikulum berbasis <strong>Outcome-Based Education</strong>
                    Program Studi {{ $prodiInfo['nama'] ?? config('obe.prodi') }} — transparan, terstandarisasi, dan terintegrasi.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('profil-lulusan.index') }}{{ $programQuery }}"
                        class="px-6 py-3 rounded-xl bg-white text-brand-800 font-semibold hover:bg-brand-50 transition shadow-lg">
                        Profil Lulusan →
                    </a>
                    <a href="{{ route('cpl.index') }}{{ $programQuery }}"
                        class="px-6 py-3 rounded-xl bg-white/10 text-white font-semibold hover:bg-white/20 transition ring-1 ring-white/30">
                        Lihat CPL
                    </a>
                    <a href="{{ route('kurikulum.index') }}{{ $programQuery }}"
                        class="px-6 py-3 rounded-xl bg-white/10 text-white font-semibold hover:bg-white/20 transition ring-1 ring-white/30">
                        Kurikulum
                    </a>
                </div>

                {{-- Program Switcher --}}
                @if($allPrograms->count() > 1)
                <div class="mt-6" x-data="{ open: false }">
                    <p class="text-brand-300 text-xs mb-2 font-medium">🔀 Lihat program studi lain:</p>
                    <div class="relative inline-block">
                        <button @click="open = !open"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 text-white text-sm font-medium hover:bg-white/20 transition ring-1 ring-white/20">
                            <span>📚 {{ $prodiInfo['nama'] }}</span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                            class="absolute left-0 mt-2 w-72 rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 overflow-hidden">
                            @foreach($allPrograms->groupBy(fn($p) => $p->faculty?->nama ?? 'Lainnya') as $fakultas => $prodis)
                            <div class="px-3 py-2 bg-slate-50 border-b border-slate-100">
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $fakultas }}</span>
                            </div>
                            @foreach($prodis as $p)
                            <a href="{{ route('home') }}?program_id={{ $p->id }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition
                                    {{ ($program && $program->id === $p->id) ? 'bg-brand-50 text-brand-700 font-semibold' : '' }}">
                                @if($program && $program->id === $p->id)
                                    <span class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-slate-200 flex-shrink-0"></span>
                                @endif
                                <span>{{ $p->jenjang }} {{ $p->nama }}</span>
                            </a>
                            @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>
            {{-- Hero Right: Quick Stats --}}
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['📖', $stats['total_mk'], 'Mata Kuliah', 'from-white/10 to-white/5'],
                    ['⚖️', $stats['total_sks'].' SKS', 'Beban Studi', 'from-white/10 to-white/5'],
                    ['🎯', $stats['total_cpl'], 'Capaian Lulusan', 'from-brand-500/20 to-brand-600/10'],
                    ['📋', $stats['total_cpmk'], 'CPMK', 'from-indigo-500/20 to-indigo-600/10'],
                    ['🔬', $stats['total_sub'], 'Sub-CPMK', 'from-purple-500/20 to-purple-600/10'],
                    ['📚', $stats['total_bk'], 'Bahan Kajian', 'from-emerald-500/20 to-emerald-600/10'],
                ] as [$icon, $val, $label, $grad])
                <div class="p-4 rounded-2xl bg-gradient-to-br {{ $grad }} ring-1 ring-white/10 text-center">
                    <div class="text-2xl mb-1">{{ $icon }}</div>
                    <div class="text-2xl font-black text-white font-display">{{ $val }}</div>
                    <div class="text-xs text-brand-200 mt-0.5">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════ STATS BAR ═══════ --}}
<section class="bg-white border-b border-slate-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-4 divide-x divide-slate-100">
            @foreach([
                ['total_mk',   'Mata Kuliah', '📖', 'text-brand-700'],
                ['total_sks',  'Total SKS',   '⚖️', 'text-indigo-700'],
                ['total_cpl',  'CPL',         '🎯', 'text-green-700'],
                ['total_cpmk', 'CPMK',        '📋', 'text-amber-700'],
                ['total_sub',  'Sub-CPMK',    '🔬', 'text-purple-700'],
                ['total_bk',   'Bahan Kajian','📚', 'text-rose-700'],
            ] as [$key, $label, $icon, $color])
            <div class="flex flex-col items-center text-center px-2">
                <span class="text-xl mb-1">{{ $icon }}</span>
                <span class="text-2xl font-black {{ $color }} font-display">{{ $stats[$key] ?? 0 }}</span>
                <span class="text-xs text-slate-400 mt-0.5">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════ CHART: SKS PER SEMESTER ═══════ --}}
@if($sksBySemester->count())
<section class="bg-gradient-to-r from-slate-50 to-brand-50 py-16 border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-black font-display text-slate-900 mb-2">Distribusi SKS per Semester</h2>
            <p class="text-slate-500 text-sm">Sebaran beban studi dan jumlah mata kuliah di setiap semester</p>
        </div>
        <div class="grid lg:grid-cols-3 gap-6 items-start">
            {{-- Chart --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <canvas id="sksSemesterChart" height="100"></canvas>
            </div>
            {{-- Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 text-sm">Ringkasan per Semester</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 uppercase">Semester</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 uppercase">MK</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-slate-500 uppercase">SKS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($sksBySemester as $row)
                        <tr class="hover:bg-brand-50/40 transition">
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 text-xs font-bold flex items-center justify-center">{{ $row->semester }}</span>
                                    <span class="text-slate-600">Semester {{ $row->semester }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-center font-semibold text-slate-700">{{ $row->total_mk }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 font-bold text-xs">{{ $row->total_sks }} SKS</span>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="bg-slate-50 font-bold">
                            <td class="px-4 py-2.5 text-slate-700">Total</td>
                            <td class="px-4 py-2.5 text-center text-slate-700">{{ $sksBySemester->sum('total_mk') }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full bg-brand-600 text-white font-bold text-xs">{{ $sksBySemester->sum('total_sks') }} SKS</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ═══════ PROFIL LULUSAN ═══════ --}}
@if($profilLulusans->count())
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold ring-1 ring-green-200 mb-4">
                🎓 Profil Lulusan
            </span>
            <h2 class="text-3xl font-black font-display text-slate-900 mb-3">Profil Lulusan</h2>
            <p class="text-slate-500 max-w-2xl mx-auto">
                    Peran yang diharapkan dapat diemban lulusan Program Studi {{ $prodiInfo['nama'] ?? config('obe.prodi') }}
                    {{ config('obe.universitas_singkat') }} di dunia kerja
                </p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @php
            $plColors = ['brand','green','purple','amber','rose','indigo','teal','sky'];
            @endphp
            @foreach($profilLulusans as $i => $pl)
            @php $c = $plColors[$i % count($plColors)]; @endphp
            <div class="group card-hover rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden hover:shadow-md hover:border-{{ $c }}-200 transition">
                <div class="h-2 bg-gradient-to-r from-{{ $c }}-400 to-{{ $c }}-600"></div>
                <div class="p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-{{ $c }}-50 flex items-center justify-center flex-shrink-0">
                            <span class="text-{{ $c }}-700 font-black text-sm font-mono">{{ $pl->kode }}</span>
                        </div>
                        <h3 class="font-bold text-slate-900 group-hover:text-{{ $c }}-700 transition leading-tight">
                            {{ $pl->deskripsi }}
                        </h3>
                    </div>
                    @if($pl->cpls->count())
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        @foreach($pl->cpls->take(4) as $cpl)
                        <span class="inline-flex px-2 py-0.5 rounded-full bg-{{ $c }}-50 text-{{ $c }}-700 text-xs font-semibold ring-1 ring-{{ $c }}-100">
                            {{ $cpl->kode }}
                        </span>
                        @endforeach
                        @if($pl->cpls->count() > 4)
                        <span class="text-xs text-slate-400">+{{ $pl->cpls->count() - 4 }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-8">
            <a href="{{ route('profil-lulusan.index') }}"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand-600 text-white font-semibold hover:bg-brand-700 transition shadow">
                Lihat Detail Profil Lulusan →
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════ TENTANG OBE ═══════ --}}
<section class="bg-gradient-to-br from-slate-50 to-brand-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-black font-display text-slate-900 mb-3">Tentang Kurikulum OBE</h2>
            <p class="text-slate-500 max-w-2xl mx-auto">
                Kurikulum dirancang sesuai standar nasional dan internasional untuk menghasilkan lulusan yang kompeten dan berdaya saing.
            </p>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['🎯', 'Berbasis Capaian Lulusan', 'Setiap mata kuliah dirancang untuk mendukung capaian pembelajaran lulusan (CPL) yang telah ditetapkan secara jelas dan terukur.', 'brand'],
                ['📐', 'Terstandarisasi IAPS 4.0', 'Struktur kurikulum mengacu pada instrumen akreditasi program studi IAPS 4.0 LAM-INFOKOM untuk menjamin kualitas akademik.', 'purple'],
                ['🔗', 'Terintegrasi Data', 'Semua komponen kurikulum — CPL, CPMK, bahan kajian, bobot penilaian — terhubung dalam satu sistem terpadu.', 'green'],
            ] as [$icon, $title, $desc, $color])
            <div class="card-hover p-6 rounded-2xl bg-white border border-slate-100 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-{{ $color }}-50 flex items-center justify-center mb-4">
                    <span class="text-2xl">{{ $icon }}</span>
                </div>
                <h3 class="font-bold font-display text-slate-900 mb-2">{{ $title }}</h3>
                <p class="text-sm text-slate-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════ CPL HIGHLIGHT ═══════ --}}
@if($cpls->count())
<section class="bg-brand-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-brand-200 text-xs font-semibold ring-1 ring-white/20 mb-4">
                🎯 CPL
            </span>
            <h2 class="text-2xl font-black font-display text-white mb-2">Capaian Pembelajaran Lulusan</h2>
            <p class="text-brand-300 text-sm">Kompetensi yang diharapkan dari setiap lulusan Program Studi {{ $prodiInfo['nama'] ?? config('obe.prodi') }}</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach($cpls as $cpl)
            <div class="p-4 rounded-2xl bg-white/8 ring-1 ring-white/10 hover:bg-white/15 transition group">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex px-2 py-0.5 rounded-lg bg-brand-500/30 text-brand-200 font-black text-xs font-mono">{{ $cpl->kode }}</span>
                </div>
                <p class="text-brand-100 text-xs leading-relaxed line-clamp-4 group-hover:line-clamp-none transition-all">{{ $cpl->deskripsi }}</p>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-8">
            <a href="{{ route('cpl.index') }}"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-brand-800 font-semibold hover:bg-brand-50 transition">
                Lihat Semua CPL →
            </a>
        </div>
    </div>
</section>
@endif

{{-- ═══════ QUICK LINKS ═══════ --}}
<section class="bg-white py-14 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-black font-display text-slate-900 mb-6 text-center">Akses Cepat</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
            @foreach([
                ['📑', 'CPL', route('cpl.index') . $programQuery, 'brand'],
                ['📚', 'Mata Kuliah', route('mata-kuliah.index') . $programQuery, 'purple'],
                ['🗺️', 'Kurikulum', route('kurikulum.index') . $programQuery, 'green'],
                ['🔍', 'Bahan Kajian', route('bahan-kajian.index') . $programQuery, 'amber'],
                ['🎓', 'Profil Lulusan', route('profil-lulusan.index') . $programQuery, 'rose'],
                ['🔗', 'MBKM', route('mbkm.index') . $programQuery, 'teal'],
            ] as [$icon, $title, $url, $color])
            <a href="{{ $url }}"
                class="flex flex-col items-center text-center p-4 rounded-2xl bg-{{ $color }}-50 border border-{{ $color }}-100 hover:border-{{ $color }}-300 hover:shadow-sm transition">
                <span class="text-2xl mb-2">{{ $icon }}</span>
                <span class="font-semibold text-{{ $color }}-800 text-sm">{{ $title }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════ MATA KULIAH TERBARU ═══════ --}}
@if($recentMk->count())
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-black font-display text-slate-900">Mata Kuliah Semester Awal</h2>
        <a href="{{ route('mata-kuliah.index') }}{{ $programQuery }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
            Lihat semua →
        </a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($recentMk as $mk)
        <a href="{{ route('mata-kuliah.show', $mk->kode) }}"
            class="card-hover p-5 rounded-2xl bg-white border border-slate-100 shadow-sm hover:border-brand-200 hover:shadow-md group">
            <div class="flex items-start justify-between mb-3">
                <span class="inline-flex px-2.5 py-1 rounded-lg bg-brand-50 text-brand-700 text-xs font-bold font-mono">
                    {{ $mk->kode }}
                </span>
                <div class="flex gap-1.5">
                    <span class="badge bg-slate-100 text-slate-600">{{ $mk->sks }} SKS</span>
                    <span class="badge bg-brand-50 text-brand-700">Sem {{ $mk->semester }}</span>
                </div>
            </div>
            <h3 class="font-semibold text-slate-900 group-hover:text-brand-700 transition leading-tight line-clamp-2">
                {{ $mk->nama }}
            </h3>
        </a>
        @endforeach
    </div>
</section>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const labels  = @json($sksBySemester->pluck('semester')->map(fn($s) => 'Semester '.$s));
    const dataSks = @json($sksBySemester->pluck('total_sks'));
    const dataMk  = @json($sksBySemester->pluck('total_mk'));

    new Chart(document.getElementById('sksSemesterChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Total SKS',
                    data: dataSks,
                    backgroundColor: 'rgba(29,78,216,0.7)',
                    borderColor: 'rgba(29,78,216,1)',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    yAxisID: 'y',
                },
                {
                    label: 'Jumlah MK',
                    data: dataMk,
                    type: 'line',
                    borderColor: 'rgba(16,185,129,1)',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: 'rgba(16,185,129,1)',
                    pointRadius: 5,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y2',
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { font: { size: 12, weight: '600' } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label === 'Total SKS'
                            ? ` ${ctx.parsed.y} SKS`
                            : ` ${ctx.parsed.y} Mata Kuliah`,
                    },
                },
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { callback: v => v + ' SKS', font: { size: 11 } },
                    title: { display: true, text: 'Total SKS', font: { size: 11 } },
                },
                y2: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: { callback: v => v + ' MK', font: { size: 11 } },
                    title: { display: true, text: 'Jumlah MK', font: { size: 11 } },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } },
                },
            },
        },
    });
})();
</script>
@endpush
