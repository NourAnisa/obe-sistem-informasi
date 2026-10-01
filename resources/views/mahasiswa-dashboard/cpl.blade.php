@extends('layouts.dashboard')
@section('title', 'Evaluasi CPL & CPMK Saya')

@push('styles')
<style>
    .cpl-card {
        background: #fff;
        border-radius: 14px;
        border: 1.5px solid #e5e7eb;
        padding: 20px 22px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        border: 1.5px solid #e5e7eb;
        padding: 18px;
        text-align: center;
    }

    .stat-val {
        font-size: 30px;
        font-weight: 800;
        line-height: 1.1;
    }

    .stat-lbl {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }

    .badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        padding: 3px 10px;
    }

    .badge-tercapai {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-cukup {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-belum {
        background: #fee2e2;
        color: #991b1b;
    }

    .progress-track {
        background: #f3f4f6;
        border-radius: 99px;
        height: 10px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 99px;
        transition: width .4s;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e3a5f;
        margin: 0 0 14px;
    }

    .mk-chart-wrap {
        position: relative;
        height: 160px;
    }
</style>
@endpush

@section('content')
@php
function cplStatus($nilai) {
if ($nilai >= 80) return ['label' => 'Tercapai', 'class' => 'badge-tercapai'];
if ($nilai >= 60) return ['label' => 'Cukup', 'class' => 'badge-cukup'];
return ['label' => 'Belum', 'class' => 'badge-belum'];
}
// defaults for script (overwritten in @if block below)
$cplLabels = '[]'; $cplValues = '[]'; $cplThresh = '[]';
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('mahasiswa.dashboard') }}" class="text-sm text-blue-500 hover:underline">← Dashboard</a>
            <h1 class="text-2xl font-bold text-brand-900 mt-1">🎯 Evaluasi CPL & CPMK Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Halo, <strong>{{ $mahasiswa->nama }}</strong> — berikut capaian pembelajaran Anda berdasarkan data OBE.</p>
        </div>
        {{-- Export / Import buttons --}}
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('mahasiswa.cpl.export') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 3v12"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('mahasiswa.cpl.import.template') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 14l5-5 5 5M12 9v10"/>
                </svg>
                Template Import
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         SECTION 1 — SUMMARY CARDS
    ════════════════════════════════════════ --}}
    @php
    $overallStatus = $totalCpl === 0 ? '—'
    : ($cplTercapai === $totalCpl ? 'Semua Tercapai'
    : ($cplTercapai >= $totalCpl * 0.6 ? 'Sebagian Tercapai' : 'Perlu Peningkatan'));
    $overallColor = $totalCpl === 0 ? '#6b7280'
    : ($cplTercapai === $totalCpl ? '#065f46'
    : ($cplTercapai >= $totalCpl * 0.6 ? '#92400e' : '#991b1b'));
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-val" style="color:#1d4ed8">{{ $totalCpl }}</div>
            <div class="stat-lbl">Total CPL</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:#15803d">{{ $cplTercapai }}</div>
            <div class="stat-lbl">CPL Tercapai</div>
        </div>
        <div class="stat-card">
            <div class="stat-val" style="color:#7c3aed">{{ $avgCpl > 0 ? $avgCpl : '—' }}</div>
            <div class="stat-lbl">Rata-rata Nilai CPL</div>
        </div>
        <div class="stat-card">
            <div class="stat-val text-xl font-bold" style="color:{{ $overallColor }}">{{ $overallStatus }}</div>
            <div class="stat-lbl">Status Keseluruhan</div>
        </div>
    </div>

    @if($cplAchievements->isEmpty() && $cpmkGrouped->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl px-6 py-8 text-center">
        <div class="text-3xl mb-2">📊</div>
        <p class="font-semibold">Belum ada data capaian CPL/CPMK.</p>
        <p class="text-sm mt-1">Data akan muncul setelah dosen menginput nilai dan OBE pipeline dijalankan.</p>
    </div>
    @else

    {{-- ════════════════════════════════════════
         SECTION 2 & 3 — GRAFIK CPL (RADAR + BAR)
    ════════════════════════════════════════ --}}
    @if($cplAchievements->isNotEmpty())
    @php
    $cplLabels = $cplAchievements->pluck('kode')->toJson();
    $cplValues = $cplAchievements->pluck('nilai_cpl')->map(fn($v) => round($v ?? 0, 1))->toJson();
    $cplThresh = $cplAchievements->pluck('threshold')->map(fn($v) => round($v ?? 60, 1))->toJson();
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Radar --}}
        <div class="cpl-card">
            <p class="section-title">📡 Radar CPL — Nilai Pribadi</p>
            <div style="position:relative;height:280px">
                <canvas id="chartCplRadar"></canvas>
            </div>
        </div>
        {{-- Bar --}}
        <div class="cpl-card">
            <p class="section-title">📊 Bar Chart CPL</p>
            <div style="position:relative;height:280px">
                <canvas id="chartCplBar"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         SECTION 6 — PROGRESS BAR MATA KULIAH
    ════════════════════════════════════════ --}}
    @if($cpmkGrouped->isNotEmpty())
    <div class="cpl-card">
        <p class="section-title">📈 Progress Mata Kuliah (Rata-rata CPMK)</p>
        <div class="space-y-4">
            @foreach($cpmkGrouped as $mkKode => $cpmks)
            @php
            $avgMk = round($cpmks->avg('nilai_cpmk') ?? 0, 1);
            $pct = min(100, $avgMk);
            $barColor = $avgMk >= 80 ? '#16a34a' : ($avgMk >= 60 ? '#d97706' : '#dc2626');
            $mkNama = $cpmks->first()->mk_nama;
            $mkStatus = cplStatus($avgMk);
            @endphp
            <div>
                <div class="flex justify-between items-center mb-1">
                    <div>
                        <span class="font-bold text-sm text-gray-800">{{ $mkKode }}</span>
                        <span class="text-gray-500 text-xs ml-2">{{ $mkNama }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm" style="color:{{ $barColor }}">{{ $avgMk }}</span>
                        <span class="badge {{ $mkStatus['class'] }}">{{ $mkStatus['label'] }}</span>
                    </div>
                </div>
                <div class="progress-track">
                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         SECTION 4 — CPMK PER MATA KULIAH (cards + charts)
    ════════════════════════════════════════ --}}
    @if($cpmkGrouped->isNotEmpty())
    <div>
        <p class="section-title">📚 CPMK per Mata Kuliah</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($cpmkGrouped as $mkKode => $cpmks)
            @php
            $avgMk = round($cpmks->avg('nilai_cpmk') ?? 0, 1);
            $mkStatus = cplStatus($avgMk);
            $mkNama = $cpmks->first()->mk_nama;
            $chartId = 'chart_' . Str::slug($mkKode);
            $labels = $cpmks->pluck('cpmk_kode')->toJson();
            $vals = $cpmks->pluck('nilai_cpmk')->map(fn($v) => round($v ?? 0, 1))->toJson();
            $achieved = $cpmks->where('achieved', true)->count();
            @endphp
            <div class="cpl-card" data-chart="{{ $chartId }}" data-labels="{{ $labels }}" data-vals="{{ $vals }}">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <span class="font-bold text-base text-gray-900">{{ $mkKode }}</span>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $mkNama }}</p>
                    </div>
                    <div class="text-right">
                        <span class="badge {{ $mkStatus['class'] }}">{{ $mkStatus['label'] }}</span>
                        <p class="text-xs text-gray-400 mt-1">{{ $achieved }}/{{ $cpmks->count() }} CPMK tercapai</p>
                    </div>
                </div>
                <div class="mk-chart-wrap">
                    <canvas id="{{ $chartId }}"></canvas>
                </div>
                {{-- CPMK mini badges --}}
                <div class="flex flex-wrap gap-1 mt-3">
                    @foreach($cpmks as $c)
                    @php
                        $cs = cplStatus($c->nilai_cpmk ?? 0);
                        $nv2 = (float)($c->nilai_cpmk ?? 0);
                        $rlbl = $nv2 >= 80 ? 'Proficient' : ($nv2 >= 60 ? 'Developing' : 'Novice');
                        $rcls = $nv2 >= 80 ? 'bg-green-100 text-green-700' : ($nv2 >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                    @endphp
                    <span class="badge {{ $cs['class'] }}" title="{{ $c->cpmk_deskripsi }} | {{ $rlbl }}">
                        {{ $c->cpmk_kode }}: {{ round($c->nilai_cpmk ?? 0, 1) }}
                        <span class="px-1 py-0.5 ml-1 rounded text-xs {{ $rcls }}" style="font-size:10px">{{ $rlbl }}</span>
                    </span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         SECTION 5 — TABEL DETAIL CPMK
    ════════════════════════════════════════ --}}
    @if($cpmkGrouped->isNotEmpty())
    <div class="cpl-card overflow-x-auto">
        <p class="section-title">📋 Tabel Detail CPMK</p>
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:10px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">Mata Kuliah</th>
                    <th style="padding:10px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">CPMK</th>
                    <th style="padding:10px 12px;text-align:left;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">Deskripsi</th>
                    <th style="padding:10px 12px;text-align:center;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">Tugas</th>
                    <th style="padding:10px 12px;text-align:center;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">UTS</th>
                    <th style="padding:10px 12px;text-align:center;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">UAS</th>
                    <th style="padding:10px 12px;text-align:center;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">Nilai CPMK</th>
                    <th style="padding:10px 12px;text-align:center;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">Status</th>
                    <th style="padding:10px 12px;text-align:center;color:#6b7280;font-weight:600;border-bottom:2px solid #e5e7eb">Rubrik</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cpmkGrouped as $mkKode => $cpmks)
                @foreach($cpmks as $i => $c)
                @php
                    $cs = cplStatus($c->nilai_cpmk ?? 0);
                    $nv = (float)($c->nilai_cpmk ?? 0);
                    $rubrikLabel = $nv >= 80 ? 'Proficient' : ($nv >= 60 ? 'Developing' : 'Novice');
                    $rubrikStyle = $nv >= 80 ? 'background:#dcfce7;color:#065f46' : ($nv >= 60 ? 'background:#fef9c3;color:#713f12' : 'background:#fee2e2;color:#991b1b');
                @endphp
                <tr style="border-bottom:1px solid #f1f5f9;{{ $loop->even ? 'background:#fafafa' : '' }}">
                    @if($loop->first)
                    <td rowspan="{{ $cpmks->count() }}" style="padding:10px 12px;font-weight:700;color:#1e3a5f;vertical-align:top;border-right:2px solid #e5e7eb">
                        {{ $mkKode }}<br>
                        <span style="color:#9ca3af;font-weight:400;font-size:11px">{{ $c->mk_nama }}</span>
                    </td>
                    @endif
                    <td style="padding:10px 12px;font-weight:600;color:#7c3aed">{{ $c->cpmk_kode }}</td>
                    <td style="padding:10px 12px;color:#374151;max-width:220px">{{ Str::limit($c->cpmk_deskripsi, 80) }}</td>
                    <td style="padding:10px 12px;text-align:center">{{ $c->nilai_tugas !== null ? round($c->nilai_tugas, 1) : '—' }}</td>
                    <td style="padding:10px 12px;text-align:center">{{ $c->nilai_uts !== null ? round($c->nilai_uts, 1) : '—' }}</td>
                    <td style="padding:10px 12px;text-align:center">{{ $c->nilai_uas !== null ? round($c->nilai_uas, 1) : '—' }}</td>
                    <td style="padding:10px 12px;text-align:center;font-weight:700;font-size:14px">
                        {{ $c->nilai_cpmk !== null ? round($c->nilai_cpmk, 1) : '—' }}
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                        <span class="badge {{ $cs['class'] }}">{{ $cs['label'] }}</span>
                    </td>
                    <td style="padding:10px 12px;text-align:center">
                        <span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:600;{{ $rubrikStyle }}">{{ $rubrikLabel }}</span>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         CPL DETAIL TABLE
    ════════════════════════════════════════ --}}
    @if($cplAchievements->isNotEmpty())
    <div class="cpl-card">
        <p class="section-title">🏆 Detail Capaian CPL</p>
        <div class="space-y-3">
            @foreach($cplAchievements as $cpl)
            @php
            $cs = cplStatus($cpl->nilai_cpl ?? 0);
            $pct = min(100, round($cpl->nilai_cpl ?? 0, 1));
            $barColor = ($cpl->nilai_cpl ?? 0) >= 80 ? '#16a34a' : (($cpl->nilai_cpl ?? 0) >= 60 ? '#d97706' : '#dc2626');
            @endphp
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-start gap-3">
                        <span class="badge" style="background:#ede9fe;color:#5b21b6;font-size:12px">{{ $cpl->kode }}</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ Str::limit($cpl->deskripsi, 120) }}</p>
                            @if($cpl->deskripsi_en)
                            <p class="text-xs text-gray-400 italic mt-0.5">{{ Str::limit($cpl->deskripsi_en, 100) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-4">
                        <span class="badge {{ $cs['class'] }}">{{ $cs['label'] }}</span>
                        <p class="text-xs text-gray-500 mt-1">{{ round($cpl->nilai_cpl ?? 0, 1) }} / {{ $cpl->threshold ?? 60 }}</p>
                    </div>
                </div>
                <div class="progress-track">
                    <div class="progress-bar" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                </div>
                @if($cpl->jumlah_cpmk)
                <p class="text-xs text-gray-400 mt-2">
                    CPMK: {{ $cpl->jumlah_achieved ?? 0 }}/{{ $cpl->jumlah_cpmk }} tercapai
                </p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif {{-- end if not empty --}}

</div>
@endsection

@push('scripts')
<script>
(function () {
    var CPL_LABELS = <?php echo $cplLabels; ?>;
    var CPL_VALUES = <?php echo $cplValues; ?>;
    var CPL_THRESH = <?php echo $cplThresh; ?>;

    document.addEventListener('DOMContentLoaded', function () {

        // ── Radar CPL ──────────────────────────────────────────────
        var radarEl = document.getElementById('chartCplRadar');
        if (radarEl && CPL_LABELS.length) {
            new Chart(radarEl, {
                type: 'radar',
                data: {
                    labels: CPL_LABELS,
                    datasets: [
                        {
                            label: 'Nilai CPL',
                            data: CPL_VALUES,
                            backgroundColor: 'rgba(124,58,237,.15)',
                            borderColor: '#7c3aed',
                            pointBackgroundColor: '#7c3aed',
                            pointRadius: 4,
                            borderWidth: 2,
                        },
                        {
                            label: 'Threshold',
                            data: CPL_THRESH,
                            backgroundColor: 'rgba(220,38,38,.08)',
                            borderColor: '#dc2626',
                            borderDash: [5, 5],
                            pointRadius: 0,
                            borderWidth: 1.5,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        r: {
                            beginAtZero: true, max: 100,
                            ticks: { stepSize: 20, font: { size: 10 } },
                            pointLabels: { font: { size: 11, weight: 'bold' } },
                        }
                    },
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
                }
            });
        }

        // ── Bar CPL ────────────────────────────────────────────────
        var barEl = document.getElementById('chartCplBar');
        if (barEl && CPL_LABELS.length) {
            var barColors = CPL_VALUES.map(function (v) {
                return v >= 80 ? '#16a34a' : (v >= 60 ? '#d97706' : '#dc2626');
            });
            new Chart(barEl, {
                type: 'bar',
                data: {
                    labels: CPL_LABELS,
                    datasets: [{ label: 'Nilai CPL', data: CPL_VALUES, backgroundColor: barColors, borderRadius: 6 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { font: { size: 10 } } },
                        x: { ticks: { font: { size: 11, weight: 'bold' } } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // ── Bar per MK (CPMK) ──────────────────────────────────────
        document.querySelectorAll('[data-chart]').forEach(function (card) {
            var id     = card.dataset.chart;
            var labels = JSON.parse(card.dataset.labels || '[]');
            var vals   = JSON.parse(card.dataset.vals   || '[]');
            var el     = document.getElementById(id);
            if (!el || !labels.length) return;
            var cols = vals.map(function (v) {
                return v >= 80 ? '#16a34a' : (v >= 60 ? '#d97706' : '#dc2626');
            });
            new Chart(el, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: 'Nilai CPMK', data: vals, backgroundColor: cols, borderRadius: 5 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { font: { size: 9 } } },
                        x: { ticks: { font: { size: 10 } } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        });

    });
})();
</script>
@endpush