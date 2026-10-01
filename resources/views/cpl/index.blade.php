@extends('layouts.public')
@section('title','Capaian Pembelajaran Lulusan')
@section('content')

@include('components.program-switcher', ['currentRoute' => 'cpl.index'])

{{-- Page Header --}}
<div class="bg-gradient-to-r from-brand-800 to-indigo-900 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3 text-brand-300 text-sm mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
            <span>›</span><span class="text-white">CPL</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="badge bg-white/15 text-brand-100 ring-1 ring-white/20">Kurikulum OBE</span>
                    <span class="badge bg-white/15 text-brand-100 ring-1 ring-white/20">{{ $program?->jenjang ?? config('obe.jenjang') }}</span>
                </div>
                <h1 class="font-display text-3xl md:text-4xl font-extrabold text-white tracking-tight">Capaian Pembelajaran Lulusan</h1>
                <p class="text-brand-200 mt-2">{{ $cpls->count() }} CPL &middot; Program Studi {{ $program?->jenjang }} {{ $program?->nama ?? config('obe.prodi') }}</p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <span class="bg-white/10 ring-1 ring-white/20 backdrop-blur px-4 py-2 rounded-lg text-white text-sm font-medium">🎯 Sikap</span>
                <span class="bg-white/10 ring-1 ring-white/20 backdrop-blur px-4 py-2 rounded-lg text-white text-sm font-medium">📚 KU</span>
                <span class="bg-white/10 ring-1 ring-white/20 backdrop-blur px-4 py-2 rounded-lg text-white text-sm font-medium">⚙️ KK</span>
            </div>
        </div>
    </div>
</div>


<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Legend badges --}}
    <div class="flex flex-wrap gap-3 mb-8">
        @foreach(['Sikap'=>'bg-red-100 text-red-700 border border-red-200','KU'=>'bg-green-100 text-green-700 border border-green-200','KK'=>'bg-blue-100 text-blue-700 border border-blue-200','Sikap_KU'=>'bg-purple-100 text-purple-700 border border-purple-200'] as $kat => $cls)
        <span class="badge {{ $cls }} px-3 py-1 text-xs font-semibold">{{ $kat }}</span>
        @endforeach
        <span class="ml-auto text-sm text-gray-500 flex items-center gap-1">💡 Klik CPL untuk detail</span>
    </div>

    {{-- CPL Accordion --}}
    <div class="space-y-3 mb-14">
        @php
        $cplKatConfig = [
        'Sikap' => ['border-red-300','bg-red-50','text-red-700','bg-red-100 text-red-700'],
        'KU' => ['border-green-300','bg-green-50','text-green-700','bg-green-100 text-green-700'],
        'KK' => ['border-blue-300','bg-blue-50','text-blue-700','bg-blue-100 text-blue-700'],
        'Sikap_KU'=> ['border-purple-300','bg-purple-50','text-purple-700','bg-purple-100 text-purple-700'],
        ];
        @endphp
        @foreach($cpls as $cpl)
        @php $cfg = $cplKatConfig[$cpl->kategori] ?? ['border-gray-300','bg-gray-50','text-gray-700','bg-gray-100 text-gray-600']; @endphp
        <div id="{{ $cpl->kode }}" class="bg-white rounded-2xl shadow-sm border-l-4 {{ $cfg[0] }} border border-gray-100 hover:shadow-md transition-shadow"
            x-data="{ open: false }">
            <button @click="open = !open" class="w-full flex items-center gap-5 p-5 text-left group">
                <div class="w-16 h-16 {{ $cfg[1] }} rounded-xl flex flex-col items-center justify-center shrink-0 border {{ $cfg[0] }}">
                    <span class="font-black text-sm {{ $cfg[2] }}">{{ $cpl->kode }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="badge {{ $cfg[3] }} text-xs">{{ $cpl->kategori }}</span>
                        <span class="text-xs text-gray-400 font-medium">{{ $cpl->mataKuliahs->count() }} MK · {{ $cpl->cpmks->count() }} CPMK</span>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed pr-4 line-clamp-2 group-hover:line-clamp-none transition-all">{{ $cpl->deskripsi }}</p>
                </div>
                <div class="shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-gray-500 transition-transform duration-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="border-t border-gray-100 px-5 pb-5 pt-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- CPMK --}}
                    @if($cpl->cpmks->count())
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 bg-orange-100 rounded text-orange-600 flex items-center justify-center text-xs">C</span>
                            CPMK Turunan ({{ $cpl->cpmks->count() }})
                        </h4>
                        <div class="space-y-2">
                            @foreach($cpl->cpmks as $cpmk)
                            <div class="flex flex-col gap-1 p-2.5 bg-orange-50/50 rounded-lg border border-orange-100">
                                <div class="flex items-start gap-3">
                                    <span class="badge bg-orange-100 text-orange-700 shrink-0 mt-0.5 text-xs">{{ $cpmk->kode }}</span>
                                    <span class="text-xs text-gray-600 leading-relaxed">{{ $cpmk->deskripsi }}</span>
                                </div>
                                @if($cpmk->subCpmks->count())
                                <div class="ml-4 mt-1 space-y-1 pl-2 border-l-2 border-orange-200">
                                    @foreach($cpmk->subCpmks as $sub)
                                    <div class="flex items-start gap-2">
                                        <span class="text-[10px] font-mono bg-orange-50 text-orange-500 border border-orange-200 rounded px-1.5 py-0.5 shrink-0 leading-tight">{{ $sub->kode }}</span>
                                        <span class="text-[11px] text-gray-500 leading-relaxed">{{ $sub->deskripsi }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- MK --}}
                    @if($cpl->mataKuliahs->count())
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-5 h-5 bg-blue-100 rounded text-blue-600 flex items-center justify-center text-xs">M</span>
                            Mata Kuliah ({{ $cpl->mataKuliahs->count() }})
                        </h4>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($cpl->mataKuliahs->sortBy('semester') as $mk)
                            <a href="{{ route('mata-kuliah.show', $mk->kode) }}"
                                class="badge bg-gray-100 text-gray-700 hover:bg-blue-100 hover:text-blue-700 transition-colors border border-gray-200 text-xs py-1">
                                <span class="mr-1 text-gray-400">{{ $mk->semester }}</span>{{ $mk->kode }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ PIE CHART: Skor Maks CPL ═══ --}}
    @php
    $totalSkor = $cpls->sum('total_skor_maks');
    $useFallback = $totalSkor <= 0;
        $katColors=[ 'Sikap'=> '#ef4444',
        'KU' => '#22c55e',
        'KK' => '#3b82f6',
        'PP' => '#f59e0b',
        'Sikap_KU' => '#a855f7',
        ];
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Distribusi Skor Maksimal CPL</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if($useFallback)
                    Proporsi distribusi tiap CPL (skor maks belum diisi, ditampilkan merata)
                    @else
                    Proporsi skor maksimal masing-masing CPL — Total: <strong>{{ $totalSkor }}</strong>
                    @endif
                </p>
            </div>
            <div class="grid md:grid-cols-2 gap-0 items-center">
                {{-- Pie Chart --}}
                <div class="flex items-center justify-center p-8">
                    <div class="relative w-full max-w-sm">
                        <canvas id="cplPieChart" height="320"></canvas>
                    </div>
                </div>
                {{-- Legend Table --}}
                <div class="border-l border-gray-100 overflow-y-auto max-h-96">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">CPL</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kategori</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Skor Maks</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @php
                            $each = $useFallback ? round(100 / $cpls->count(), 1) : 0;
                            @endphp
                            @foreach($cpls as $cpl)
                            @php
                            $pct = $useFallback
                            ? $each
                            : ($totalSkor > 0 ? round($cpl->total_skor_maks / $totalSkor * 100, 1) : 0);
                            $dotColor = $katColors[$cpl->kategori] ?? '#6b7280';
                            $cfg = $cplKatConfig[$cpl->kategori] ?? ['','','','bg-gray-100 text-gray-600'];
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $dotColor }}"></span>
                                        <span class="font-bold text-gray-800 font-mono text-xs">{{ $cpl->kode }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="badge {{ $cfg[3] }} text-xs">{{ $cpl->kategori }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-700">
                                    {{ $useFallback ? '—' : $cpl->total_skor_maks }}
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="font-bold text-gray-800">{{ $pct }}%</span>
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                            @if(!$useFallback)
                            <tr class="bg-gray-50 font-bold">
                                <td class="px-4 py-2.5 text-gray-700" colspan="2">Total</td>
                                <td class="px-4 py-2.5 text-right text-brand-700">{{ $totalSkor }}</td>
                                <td class="px-4 py-2.5 text-right text-brand-700">100%</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- CPL vs Semester Matrix --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Matriks Pemenuhan CPL per Semester</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Distribusi ketercakupan CPL di setiap semester</p>
                </div>
                <span class="badge bg-blue-100 text-blue-700">{{ $cpls->count() }} CPL × 8 Semester</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="bg-gradient-to-r from-primary-700 to-primary-900 text-white">
                            <th class="px-4 py-3 text-left font-semibold whitespace-nowrap w-20">CPL</th>
                            @foreach($semesters as $sem)
                            <th class="px-3 py-3 text-center font-semibold whitespace-nowrap">Smt {{ $sem }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cpls as $cpl)
                        @php $cfg = $cplKatConfig[$cpl->kategori] ?? ['','','','bg-gray-100 text-gray-600']; @endphp
                        <tr class="{{ $loop->even ? 'bg-gray-50/50' : 'bg-white' }} hover:bg-blue-50/30 transition-colors">
                            <td class="px-4 py-2.5 font-bold whitespace-nowrap">
                                <span class="badge {{ $cfg[3] }} text-xs">{{ $cpl->kode }}</span>
                            </td>
                            @foreach($semesters as $sem)
                            <td class="px-2 py-2.5 text-center border-l border-gray-100">
                                @if(!empty($matrix[$cpl->kode][$sem]))
                                <div class="flex flex-wrap gap-0.5 justify-center">
                                    @foreach($matrix[$cpl->kode][$sem] as $kodeMk)
                                    <span class="inline-block bg-primary-100 text-primary-700 rounded px-1 py-0.5 text-[9px] font-medium leading-tight">
                                        {{ Str::afterLast($kodeMk, '.SI') ?: Str::afterLast($kodeMk, '.') }}
                                    </span>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-gray-200 text-base">·</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function() {
        @php
        $cplChartData = $cpls->map(function($c) {
            return [
                'kode' => $c->kode,
                'kategori' => $c->kategori,
                'total_skor_maks' => (float)($c->total_skor_maks ?? 0),
            ];
        })->values();
        @endphp
        const cpls = @json($cplChartData);

        const katColors = {
            'Sikap': '#ef4444',
            'KU': '#22c55e',
            'KK': '#3b82f6',
            'PP': '#f59e0b',
            'Sikap_KU': '#a855f7',
        };

        const total = cpls.reduce(function(s, c) {
            return s + c.total_skor_maks;
        }, 0);
        const useFallback = total <= 0;

        const labels = cpls.map(function(c) {
            return c.kode;
        });
        const data = useFallback ? cpls.map(function() {
            return 1;
        }) : cpls.map(function(c) {
            return c.total_skor_maks;
        });
        const colors = cpls.map(function(c) {
            return (katColors[c.kategori] || '#6b7280') + 'cc';
        });
        const borders = cpls.map(function(c) {
            return katColors[c.kategori] || '#6b7280';
        });

        new Chart(document.getElementById('cplPieChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: borders,
                    borderWidth: 2,
                    hoverOffset: 14,
                }]
            },
            options: {
                responsive: true,
                cutout: '52%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                var sum = ctx.dataset.data.reduce(function(a, b) {
                                    return a + b;
                                }, 0);
                                var pct = ((ctx.parsed / sum) * 100).toFixed(1);
                                var skor = useFallback ? 'merata' : ctx.parsed;
                                return [
                                    ' ' + ctx.label,
                                    ' Skor Maks: ' + skor,
                                    ' Persentase: ' + pct + '%',
                                ];
                            }
                        },
                        padding: 12,
                        boxPadding: 4,
                    },
                },
            },
        });
    })();
</script>
@endpush