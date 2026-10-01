@extends('layouts.dashboard')
@section('title', 'Evaluasi CPL Mahasiswa')

@section('content')
<div class="space-y-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🎓 Evaluasi CPL Mahasiswa</h1>
            <p class="text-sm text-gray-500 mt-1">Capaian Pembelajaran Lulusan per mahasiswa berdasarkan semester aktif</p>
        </div>
        <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard OBE</a>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         BAGIAN 1 — FORM PENCARIAN
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-4">🔍 Cari Mahasiswa</h2>
        <form method="GET" action="{{ route('obe.student-evaluasi') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">NIM Mahasiswa</label>
                <input type="text" name="nim" value="{{ $nim ?? '' }}"
                    placeholder="Contoh: 2021001"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none" />
            </div>
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Angkatan</label>
                <select name="angkatan" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 focus:outline-none">
                    <option value="">-- Pilih --</option>
                    @foreach($angkatanList as $ang)
                    <option value="{{ $ang }}" {{ $angkatan == $ang ? 'selected' : '' }}>{{ $ang }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                🔍 Cari
            </button>
            @if($nim)
            <a href="{{ route('obe.student-evaluasi') }}"
                class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Reset</a>
            @endif
        </form>

        {{-- Mahasiswa info strip --}}
        @if($mahasiswa)
        <div class="mt-4 flex flex-wrap gap-4 bg-blue-50 rounded-lg px-4 py-3 text-sm">
            <div><span class="text-gray-500">NIM:</span> <strong>{{ $mahasiswa->nim }}</strong></div>
            <div><span class="text-gray-500">Nama:</span> <strong>{{ $mahasiswa->nama }}</strong></div>
            @if(isset($mahasiswa->angkatan))<div><span class="text-gray-500">Angkatan:</span> <strong>{{ $mahasiswa->angkatan }}</strong></div>@endif
            @if(isset($mahasiswa->program_studi))<div><span class="text-gray-500">Prodi:</span> <strong>{{ $mahasiswa->program_studi }}</strong></div>@endif
        </div>
        @elseif($nim)
        <div class="mt-3 text-sm text-red-600 bg-red-50 rounded-lg px-4 py-2">
            ⚠️ Mahasiswa dengan NIM <strong>{{ $nim }}</strong> tidak ditemukan.
        </div>
        @endif
    </div>

    @if($nim && $mahasiswa && $cplData->isEmpty())
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-yellow-800">
        ⚠️ Belum ada data CPL achievement untuk NIM <strong>{{ $nim }}</strong> angkatan <strong>{{ $angkatan }}</strong>.
        Pastikan kalkulasi OBE sudah dijalankan terlebih dahulu melalui
        <a href="{{ route('obe.dashboard') }}" class="underline font-medium">Dashboard OBE → Hitung Semua MK</a>.
    </div>
    @endif

    @if($cplData->isNotEmpty())

    {{-- ══════════════════════════════════════════════════════════
         BAGIAN 2 — RADAR CHART
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-4">📡 Radar Chart — Capaian CPL</h2>
        <div class="flex justify-center">
            <div style="position:relative;width:100%;max-width:480px;height:360px;">
                <canvas id="cplRadarChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         BAGIAN 3 — SUMMARY CARDS
    ══════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center">
            <div class="text-3xl font-bold text-blue-600">{{ $totalCpl }}</div>
            <div class="text-xs text-gray-500 mt-1">Total CPL</div>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center">
            <div class="text-3xl font-bold text-green-600">{{ $tercapai }}</div>
            <div class="text-xs text-gray-500 mt-1">CPL Tercapai</div>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center">
            <div class="text-3xl font-bold text-red-500">{{ $belumTercapai }}</div>
            <div class="text-xs text-gray-500 mt-1">Belum Tercapai</div>
        </div>
        <div class="bg-white rounded-xl border shadow-sm p-4 text-center">
            <div class="text-3xl font-bold text-purple-600">{{ $rataRata }}</div>
            <div class="text-xs text-gray-500 mt-1">Rata-rata Nilai CPL</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         BAGIAN 4 — TABEL EVALUASI CPL
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b">
            <h2 class="font-semibold text-gray-700">📋 Tabel Evaluasi CPL</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">CPL</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-center">Nilai</th>
                        <th class="px-4 py-3 text-center">Threshold</th>
                        <th class="px-4 py-3 text-left w-36">Progress</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Tercapai</th>
                        <th class="px-4 py-3 text-left">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($cplData as $row)
                    @php
                    $barColor = match($row->status_color) {
                    'green' => 'bg-green-500',
                    'yellow' => 'bg-yellow-400',
                    default => 'bg-red-500',
                    };
                    $badgeCls = match($row->status_color) {
                    'green' => 'bg-green-100 text-green-700',
                    'yellow' => 'bg-yellow-100 text-yellow-700',
                    default => 'bg-red-100 text-red-700',
                    };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-bold text-gray-800">{{ $row->kode }}</span>
                            @if($row->kategori)
                            <div class="text-xs text-gray-400">{{ $row->kategori }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs">
                            <div class="line-clamp-2 text-xs">{{ $row->deskripsi }}</div>
                        </td>
                        <td class="px-4 py-3 text-center font-mono font-semibold text-gray-800">
                            {{ number_format((float)$row->nilai_cpl, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500 font-mono text-xs">
                            {{ number_format((float)$row->threshold, 0) }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all"
                                        style="width: {{ $row->progress_pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8 text-right">{{ $row->progress_pct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeCls }}">
                                {{ $row->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($row->achieved)
                            <span class="text-green-600 text-lg">✅</span>
                            @else
                            <span class="text-red-500 text-lg">❌</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 max-w-xs">
                            {{ $row->rekomendasi }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         BAGIAN 5 — REKOMENDASI OTOMATIS
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <h2 class="font-semibold text-gray-700 mb-4">💡 Rekomendasi Otomatis</h2>

        @php
        $perluIntervensi = $cplData->where('status', 'Perlu Intervensi');
        $perluPeningkatan = $cplData->where('status', 'Perlu Peningkatan');
        $sangatBaik = $cplData->where('status', 'Sangat Baik');
        @endphp

        @if($perluIntervensi->isNotEmpty())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center gap-2 font-semibold text-red-700 mb-2">
                🚨 Perlu Intervensi ({{ $perluIntervensi->count() }} CPL)
            </div>
            <ul class="space-y-1">
                @foreach($perluIntervensi as $r)
                <li class="text-sm text-red-700">
                    <strong>{{ $r->kode }}</strong> ({{ number_format((float)$r->nilai_cpl, 2) }}) —
                    <span class="text-red-600">{{ $r->rekomendasi }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($perluPeningkatan->isNotEmpty())
        <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center gap-2 font-semibold text-yellow-800 mb-2">
                ⚠️ Perlu Peningkatan ({{ $perluPeningkatan->count() }} CPL)
            </div>
            <ul class="space-y-1">
                @foreach($perluPeningkatan as $r)
                <li class="text-sm text-yellow-800">
                    <strong>{{ $r->kode }}</strong> ({{ number_format((float)$r->nilai_cpl, 2) }}) —
                    {{ $r->rekomendasi }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($sangatBaik->isNotEmpty())
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center gap-2 font-semibold text-green-700 mb-2">
                ✅ Sangat Baik ({{ $sangatBaik->count() }} CPL)
            </div>
            <ul class="space-y-1">
                @foreach($sangatBaik as $r)
                <li class="text-sm text-green-700">
                    <strong>{{ $r->kode }}</strong> ({{ number_format((float)$r->nilai_cpl, 2) }}) —
                    {{ $r->rekomendasi }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    @endif {{-- end if cplData not empty --}}

</div>

{{-- ── Chart.js ── --}}
@if($cplData->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    (function() {
        const labels = @json($chartLabels);
        const values = @json($chartValues);
        const maxVals = @json($chartMax);
        const threshold = {
            {
                $cplData - > avg('threshold') ?? 56
            }
        };

        const ctx = document.getElementById('cplRadarChart').getContext('2d');
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: labels,
                datasets: [{
                        label: 'Nilai CPL',
                        data: values,
                        backgroundColor: 'rgba(59,130,246,0.2)',
                        borderColor: 'rgba(59,130,246,0.9)',
                        borderWidth: 2,
                        pointBackgroundColor: values.map(v =>
                            v >= 80 ? 'rgba(34,197,94,0.9)' :
                            v >= 60 ? 'rgba(234,179,8,0.9)' :
                            'rgba(239,68,68,0.9)'
                        ),
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    },
                    {
                        label: 'Threshold',
                        data: labels.map(() => threshold),
                        backgroundColor: 'rgba(239,68,68,0.05)',
                        borderColor: 'rgba(239,68,68,0.5)',
                        borderWidth: 1,
                        borderDash: [5, 5],
                        pointRadius: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            font: {
                                size: 10
                            },
                            color: '#6b7280',
                        },
                        pointLabels: {
                            font: {
                                size: 12,
                                weight: '700'
                            },
                            color: '#374151',
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.08)'
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11
                            },
                            padding: 16
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw}`,
                        },
                    },
                },
            },
        });
    })();
</script>
@endif

@endsection