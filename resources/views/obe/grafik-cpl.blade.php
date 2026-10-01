@extends('layouts.dashboard')
@section('title', 'Grafik Capaian CPL')

@section('content')
<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📊 Grafik Capaian CPL</h1>
            <p class="text-sm text-gray-500">Perbandingan Skor Maksimal vs Skor Sementara per CPL — TA {{ $ta }}</p>
        </div>
        <div class="flex gap-2 items-center">
            <form method="GET" class="flex gap-2">
                <select name="ta" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                    @foreach($taList as $t)
                    <option value="{{ $t }}" @selected($t===$ta)>{{ $t }}</option>
                    @endforeach
                    @if(!$taList->contains($ta))
                    <option value="{{ $ta }}" selected>{{ $ta }}</option>
                    @endif
                </select>
            </form>
            <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard OBE</a>
        </div>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($chartData as $d)
        @php
        $gap = $d['gap'];
        $pct = $d['pct_achieved'];
        $color = $pct >= 80 ? 'green' : ($pct >= 60 ? 'yellow' : 'red');
        $bgMap = ['green' => 'bg-green-50 border-green-200', 'yellow' => 'bg-yellow-50 border-yellow-200', 'red' => 'bg-red-50 border-red-200'];
        $txtMap = ['green' => 'text-green-700', 'yellow' => 'text-yellow-700', 'red' => 'text-red-700'];
        $icon = ['green' => '✅', 'yellow' => '⚠️', 'red' => '❌'][$color];
        @endphp
        <div class="border {{ $bgMap[$color] }} rounded-xl p-4">
            <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-sm {{ $txtMap[$color] }}">{{ $d['kode'] }}</span>
                <span>{{ $icon }}</span>
            </div>
            <div class="text-xs text-gray-500 mb-2 truncate" title="{{ $d['deskripsi'] }}">{{ $d['kategori'] }}</div>
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Skor</span>
                <span class="font-semibold {{ $txtMap[$color] }}">{{ $d['skor_sementara'] }} / {{ $d['skor_maksimal'] }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                @php $barPct = $d['skor_maksimal'] > 0 ? min(round($d['skor_sementara'] / $d['skor_maksimal'] * 100), 100) : 0; @endphp
                <div class="h-2 rounded-full {{ $color === 'green' ? 'bg-green-500' : ($color === 'yellow' ? 'bg-yellow-500' : 'bg-red-500') }}" style="width:{{ $barPct }}%"></div>
            </div>
            <div class="text-xs text-gray-400">
                Gap: <span class="font-medium {{ $gap > 10 ? 'text-red-600' : 'text-green-600' }}">{{ $gap }}</span>
                &nbsp;|&nbsp; {{ $d['total_mahasiswa'] }} mhs
            </div>
        </div>
        @endforeach
        @if($chartData->isEmpty())
        <div class="col-span-4 text-center py-8 text-gray-400 bg-white border rounded-xl">
            Belum ada data CPL achievement untuk TA <strong>{{ $ta }}</strong>.<br>
            <a href="{{ route('obe.dashboard') }}" class="text-blue-500 text-sm underline">Hitung dulu di Dashboard OBE →</a>
        </div>
        @endif
    </div>

    {{-- ── Bar Chart ── --}}
    @if($chartData->isNotEmpty())
    <div class="bg-white rounded-xl border shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-700 text-lg">📈 Perbandingan Skor Maksimal vs Skor Sementara</h2>
            <div class="flex gap-3 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded" style="background:#22c55e"></span> Skor Maksimal</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded" style="background:#3b82f6"></span> Skor Sementara</span>
            </div>
        </div>
        <div style="position:relative; height:380px;">
            <canvas id="cplBarChart"></canvas>
        </div>
    </div>

    {{-- ── Gap Analysis Table ── --}}
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">📋 Analisis Gap CPL — TA {{ $ta }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">CPL</th>
                        <th class="px-4 py-2 text-left">Kategori</th>
                        <th class="px-4 py-2 text-right">Skor Maks</th>
                        <th class="px-4 py-2 text-right">Skor Sementara</th>
                        <th class="px-4 py-2 text-right">Gap</th>
                        <th class="px-4 py-2 text-center">% Tercapai</th>
                        <th class="px-4 py-2 text-right">Mhs Tercapai</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($chartData as $d)
                    @php
                    $gapClass = $d['gap'] <= 5 ? 'text-green-600' : ($d['gap'] <=15 ? 'text-yellow-600' : 'text-red-600' );
                        $pct=$d['pct_achieved'];
                        $sc=$pct>= 80 ? 'bg-green-100 text-green-700' : ($pct >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                        $status = $pct >= 80 ? '✅ Tercapai' : ($pct >= 60 ? '⚠️ Perlu Perhatian' : '❌ Belum Tercapai');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <div class="font-semibold text-blue-700">{{ $d['kode'] }}</div>
                                <div class="text-xs text-gray-400 max-w-xs truncate" title="{{ $d['deskripsi'] }}">{{ Str::limit($d['deskripsi'], 60) }}</div>
                            </td>
                            <td class="px-4 py-2"><span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ $d['kategori'] }}</span></td>
                            <td class="px-4 py-2 text-right font-mono font-semibold text-green-700">{{ $d['skor_maksimal'] }}</td>
                            <td class="px-4 py-2 text-right font-mono font-semibold text-blue-700">{{ $d['skor_sementara'] }}</td>
                            <td class="px-4 py-2 text-right font-mono font-semibold {{ $gapClass }}">{{ $d['gap'] }}</td>
                            <td class="px-4 py-2 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $sc }}">{{ $pct }}%</span>
                            </td>
                            <td class="px-4 py-2 text-right text-gray-600">{{ $d['jumlah_tercapai'] }} / {{ $d['total_mahasiswa'] }}</td>
                            <td class="px-4 py-2 text-sm">{{ $status }}</td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
    (function() {
        const raw = @json($chartData->values());
        if (!raw.length) return;

        const labels = raw.map(d => d.kode);
        const skorMaks = raw.map(d => d.skor_maksimal);
        const skorSementara = raw.map(d => d.skor_sementara);

        Chart.register(ChartDataLabels);

        const ctx = document.getElementById('cplBarChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        label: 'Skor Maksimal',
                        data: skorMaks,
                        backgroundColor: 'rgba(34, 197, 94, 0.75)', // green-500
                        borderColor: 'rgba(21, 128, 61, 0.9)',
                        borderWidth: 1,
                        borderRadius: 5,
                    },
                    {
                        label: 'Skor Sementara',
                        data: skorSementara,
                        backgroundColor: 'rgba(59, 130, 246, 0.75)', // blue-500
                        borderColor: 'rgba(29, 78, 216, 0.9)',
                        borderWidth: 1,
                        borderRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 13
                            },
                            padding: 20
                        },
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function(items) {
                                const i = items[0].dataIndex;
                                const gap = raw[i].gap;
                                const pct = raw[i].pct_achieved;
                                return [`Gap: ${gap}`, `% Tercapai: ${pct}%`, `Mhs: ${raw[i].jumlah_tercapai}/${raw[i].total_mahasiswa}`];
                            },
                        },
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        font: {
                            size: 11,
                            weight: 'bold'
                        },
                        formatter: (value) => value > 0 ? value : '',
                        color: (ctx) => ctx.datasetIndex === 0 ? '#166534' : '#1e40af',
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        },
                    },
                    y: {
                        beginAtZero: true,
                        max: Math.ceil(Math.max(...skorMaks, 100) * 1.15),
                        grid: {
                            color: 'rgba(0,0,0,0.06)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            callback: (v) => v,
                        },
                        title: {
                            display: true,
                            text: 'Skor',
                            font: {
                                size: 12
                            }
                        },
                    },
                },
            },
        });
    })();
</script>
@endpush