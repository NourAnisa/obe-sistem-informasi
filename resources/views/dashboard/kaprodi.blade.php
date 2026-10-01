@extends('layouts.dashboard')
@section('title','Dashboard Kaprodi')

@section('content')
{{-- ═══ HEADER ═══ --}}
<div class="bg-gradient-to-r from-brand-800 via-brand-900 to-indigo-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-brand-200 text-sm mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
                    <span>/</span>
                    <span class="text-white font-medium">Dashboard Kaprodi</span>
                </div>
                <h1 class="text-3xl font-bold font-display">
                    🎓 Selamat datang, <span class="text-brand-200">{{ auth()->user()->name }}</span>
                </h1>
                <p class="text-brand-200 mt-1 text-sm">
                    Ketua Program Studi · {{ auth()->user()->program->jenjang ?? config('obe.jenjang', 'S1') }} {{ auth()->user()->program->nama ?? config('obe.prodi', 'Program Studi') }} {{ auth()->user()->program->faculty->university->singkatan ?? config('obe.universitas_singkat', config('obe.universitas')) }} · TA {{ config('obe.tahun_akademik', '2025/2026') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('kurikulum.index') }}" class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    📚 Lihat Kurikulum
                </a>
                <a href="{{ route('cpl.index') }}" class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    🎯 Lihat CPL
                </a>
                <a href="{{ route('rps.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 hover:bg-brand-400 rounded-lg text-sm font-semibold shadow transition">
                    ⚡ Generate RPS
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- ═══ STAT CARDS ═══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
        $statCards = [
        ['label'=>'Total Mata Kuliah','value'=>$stats['total_mk'],'icon'=>'📖','color'=>'brand','sub'=>'Mata kuliah aktif'],
        ['label'=>'Total SKS','value'=>$stats['total_sks'],'icon'=>'⚖️','color'=>'indigo','sub'=>'Beban studi'],
        ['label'=>'Total CPL','value'=>$stats['total_cpl'],'icon'=>'🎯','color'=>'violet','sub'=>'Capaian Lulusan'],
        ['label'=>'Total CPMK','value'=>$stats['total_cpmk'],'icon'=>'📋','color'=>'sky','sub'=>'Capaian MK'],
        ['label'=>'Total Dosen','value'=>$stats['total_dosen'],'icon'=>'👩‍🏫','color'=>'emerald','sub'=>'Pengampu MK'],
        ];
        $colorMap = [
        'brand' => ['bg'=>'bg-brand-50','icon'=>'bg-brand-100 text-brand-700','val'=>'text-brand-700','border'=>'border-brand-200'],
        'indigo' => ['bg'=>'bg-indigo-50','icon'=>'bg-indigo-100 text-indigo-700','val'=>'text-indigo-700','border'=>'border-indigo-200'],
        'violet' => ['bg'=>'bg-violet-50','icon'=>'bg-violet-100 text-violet-700','val'=>'text-violet-700','border'=>'border-violet-200'],
        'sky' => ['bg'=>'bg-sky-50','icon'=>'bg-sky-100 text-sky-700','val'=>'text-sky-700','border'=>'border-sky-200'],
        'emerald'=> ['bg'=>'bg-emerald-50','icon'=>'bg-emerald-100 text-emerald-700','val'=>'text-emerald-700','border'=>'border-emerald-200'],
        ];
        @endphp
        @foreach($statCards as $card)
        @php $c = $colorMap[$card['color']]; @endphp
        <div class="card-hover {{ $c['bg'] }} rounded-2xl p-5 border {{ $c['border'] }} shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <span class="w-10 h-10 {{ $c['icon'] }} rounded-xl flex items-center justify-center text-xl">{{ $card['icon'] }}</span>
            </div>
            <p class="text-3xl font-bold {{ $c['val'] }} font-display">{{ $card['value'] }}</p>
            <p class="text-sm font-semibold text-slate-700 mt-1">{{ $card['label'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $card['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ═══ OBE HEALTH ═══ --}}
    <div>
        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
            <span class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center text-sm">🏥</span>
            Status Kesehatan OBE
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl border p-5 {{ $obeHealth['mk_tanpa_cpl'] > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold {{ $obeHealth['mk_tanpa_cpl'] > 0 ? 'text-red-700' : 'text-green-700' }}">MK Tanpa CPL</p>
                        <p class="text-3xl font-bold {{ $obeHealth['mk_tanpa_cpl'] > 0 ? 'text-red-700' : 'text-green-700' }} mt-1">{{ $obeHealth['mk_tanpa_cpl'] }}</p>
                        <p class="text-xs {{ $obeHealth['mk_tanpa_cpl'] > 0 ? 'text-red-500' : 'text-green-500' }} mt-1">
                            {{ $obeHealth['mk_tanpa_cpl'] > 0 ? 'Perlu dipetakan ke CPL' : '✅ Semua MK sudah dipetakan' }}
                        </p>
                    </div>
                    <span class="text-3xl">{{ $obeHealth['mk_tanpa_cpl'] > 0 ? '🔴' : '✅' }}</span>
                </div>
                @if($obeHealth['mk_tanpa_cpl'] > 0)
                <a href="{{ route('pemetaan.index') }}" class="mt-3 inline-flex items-center text-xs text-red-600 hover:text-red-800 font-semibold">→ Perbaiki Pemetaan</a>
                @endif
            </div>
            <div class="rounded-2xl border p-5 {{ $obeHealth['mk_tanpa_cpmk'] > 0 ? 'bg-orange-50 border-orange-200' : 'bg-green-50 border-green-200' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold {{ $obeHealth['mk_tanpa_cpmk'] > 0 ? 'text-orange-700' : 'text-green-700' }}">MK Tanpa CPMK</p>
                        <p class="text-3xl font-bold {{ $obeHealth['mk_tanpa_cpmk'] > 0 ? 'text-orange-700' : 'text-green-700' }} mt-1">{{ $obeHealth['mk_tanpa_cpmk'] }}</p>
                        <p class="text-xs {{ $obeHealth['mk_tanpa_cpmk'] > 0 ? 'text-orange-500' : 'text-green-500' }} mt-1">
                            {{ $obeHealth['mk_tanpa_cpmk'] > 0 ? 'Perlu tambah CPMK' : '✅ Semua MK punya CPMK' }}
                        </p>
                    </div>
                    <span class="text-3xl">{{ $obeHealth['mk_tanpa_cpmk'] > 0 ? '🟠' : '✅' }}</span>
                </div>
                @if($obeHealth['mk_tanpa_cpmk'] > 0)
                <a href="{{ route('mata-kuliah.index') }}" class="mt-3 inline-flex items-center text-xs text-orange-600 hover:text-orange-800 font-semibold">→ Lihat Mata Kuliah</a>
                @endif
            </div>
            <div class="rounded-2xl border p-5 {{ $obeHealth['bobot_invalid'] > 0 ? 'bg-yellow-50 border-yellow-200' : 'bg-green-50 border-green-200' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold {{ $obeHealth['bobot_invalid'] > 0 ? 'text-yellow-700' : 'text-green-700' }}">Bobot Tidak Valid</p>
                        <p class="text-3xl font-bold {{ $obeHealth['bobot_invalid'] > 0 ? 'text-yellow-700' : 'text-green-700' }} mt-1">{{ $obeHealth['bobot_invalid'] }}</p>
                        <p class="text-xs {{ $obeHealth['bobot_invalid'] > 0 ? 'text-yellow-500' : 'text-green-500' }} mt-1">
                            {{ $obeHealth['bobot_invalid'] > 0 ? 'Total bobot ≠ 100%' : '✅ Semua bobot valid' }}
                        </p>
                    </div>
                    <span class="text-3xl">{{ $obeHealth['bobot_invalid'] > 0 ? '⚠️' : '✅' }}</span>
                </div>
                @if($obeHealth['bobot_invalid'] > 0)
                <a href="{{ route('bobot-penilaian.index') }}" class="mt-3 inline-flex items-center text-xs text-yellow-600 hover:text-yellow-800 font-semibold">→ Periksa Bobot</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ CHARTS ROW ═══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-brand-100 rounded-lg flex items-center justify-center text-sm">📊</span>
                Distribusi SKS per Semester
            </h3>
            <div class="relative h-56">
                <canvas id="sksSemesterChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center text-sm">🥧</span>
                Distribusi MK per Kategori
            </h3>
            <div class="relative h-56">
                <canvas id="mkKategoriChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══ CPL COVERAGE TABLE ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center text-sm">🎯</span>
                Coverage CPL terhadap Mata Kuliah
            </h3>
            @if($cplTidakTercover > 0)
            <span class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full font-semibold">{{ $cplTidakTercover }} CPL belum tercover</span>
            @else
            <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">✅ Semua CPL tercover</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi CPL</th>
                        <th class="px-4 py-3 text-center font-semibold">Jumlah MK</th>
                        <th class="px-4 py-3 text-left font-semibold w-48">Coverage</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($cplCoverage as $cpl)
                    @php
                    $maxMk = $cplCoverage->max('mata_kuliahs_count') ?: 1;
                    $pct = round(($cpl->mata_kuliahs_count / $maxMk) * 100);
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-brand-100 text-brand-700 px-2 py-1 rounded-lg font-bold">{{ $cpl->kode }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700 max-w-xs">{{ Str::limit($cpl->deskripsi ?? $cpl->nama ?? '-', 70) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ $cpl->mata_kuliahs_count > 0 ? 'text-brand-700' : 'text-red-500' }}">{{ $cpl->mata_kuliahs_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $cpl->mata_kuliahs_count > 0 ? 'bg-brand-500' : 'bg-red-300' }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500 w-8 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-slate-400">Belum ada data CPL</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ DOSEN WORKLOAD ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center text-sm">👩‍🏫</span>
                Distribusi Beban Mengajar Dosen
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Nama Dosen (PJMK)</th>
                        <th class="px-4 py-3 text-center font-semibold">Jumlah MK</th>
                        <th class="px-4 py-3 text-center font-semibold">Total SKS</th>
                        <th class="px-4 py-3 text-left font-semibold w-48">Beban Relatif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($dosenWorkload as $dosen)
                    @php
                    $maxMk2 = $dosenWorkload->max('total_mk') ?: 1;
                    $pct2 = round(($dosen->total_mk / $maxMk2) * 100);
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $dosen->pjmk ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-lg">{{ $dosen->total_mk }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $dosen->total_sks }} SKS</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $pct2 }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500 w-8">{{ $pct2 }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-slate-400">Belum ada data dosen</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ SKS PER SEMESTER TABLE ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-sky-100 rounded-lg flex items-center justify-center text-sm">📅</span>
                Ringkasan Kurikulum per Semester
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center font-semibold">Semester</th>
                        <th class="px-4 py-3 text-center font-semibold">Jumlah MK</th>
                        <th class="px-4 py-3 text-center font-semibold">Total SKS</th>
                        <th class="px-4 py-3 text-left font-semibold">Proporsi SKS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($sksBySemester as $sem)
                    @php
                    $maxSks = $sksBySemester->max('total_sks') ?: 1;
                    $pctSks = round(($sem->total_sks / $maxSks) * 100);
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg">Semester {{ $sem->semester }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $sem->total_mk }} MK</td>
                        <td class="px-4 py-3 text-center font-bold text-sky-700">{{ $sem->total_sks }} SKS</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full bg-sky-500" style="width: {{ $pctSks }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500 w-8">{{ $sem->total_sks }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-slate-400">Belum ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    (function() {
        const semLabels = @json($sksBySemester->pluck('semester')->map(fn($s) => 'Sem '.$s));
        const semSks = @json($sksBySemester->pluck('total_sks'));
        const semMk = @json($sksBySemester->pluck('total_mk'));

        new Chart(document.getElementById('sksSemesterChart'), {
            type: 'bar',
            data: {
                labels: semLabels,
                datasets: [{
                        label: 'Total SKS',
                        data: semSks,
                        backgroundColor: 'rgba(37,99,235,0.75)',
                        borderColor: '#1d4ed8',
                        borderWidth: 1.5,
                        borderRadius: 6
                    },
                    {
                        label: 'Jumlah MK',
                        data: semMk,
                        type: 'line',
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124,58,237,0.1)',
                        pointBackgroundColor: '#7c3aed',
                        pointRadius: 5,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'SKS',
                            font: {
                                size: 11
                            }
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y2: {
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jml MK',
                            font: {
                                size: 11
                            }
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        const katLabels = @json($mkByKategori->pluck('kategori')->map(fn($k) => $k ?? 'Lainnya'));
        const katData = @json($mkByKategori->pluck('total'));
        const chartColors = ['#2563eb', '#7c3aed', '#059669', '#d97706', '#dc2626', '#0891b2', '#9333ea', '#14b8a6'];

        new Chart(document.getElementById('mkKategoriChart'), {
            type: 'doughnut',
            data: {
                labels: katLabels,
                datasets: [{
                    data: katData,
                    backgroundColor: chartColors.slice(0, katLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            },
                            padding: 12
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} MK`
                        }
                    }
                }
            }
        });
    })();
</script>
@endpush