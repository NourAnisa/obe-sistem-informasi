@extends('layouts.dashboard')
@section('title', 'Dashboard OBE')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🎯 Dashboard OBE</h1>
            <p class="text-sm text-gray-500">Outcome-Based Education — Capaian CPL & CPMK</p>
        </div>
        <div class="flex items-center gap-3">
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
            <button onclick="hitungSemua()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium">
                ⚡ Hitung Semua MK
            </button>
            <a href="{{ route('obe.export-prodi', ['ta' => $ta]) }}"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium">
                📄 Export Laporan Prodi
            </a>
            <a href="{{ route('obe.laporan-evaluasi-prodi', ['ta' => $ta]) }}"
                class="bg-indigo-700 hover:bg-indigo-800 text-white px-4 py-2 rounded text-sm font-medium">
                📊 Generate Laporan Evaluasi Prodi
            </a>
            <a href="{{ route('obe.laporan-evaluasi-dosen', ['ta' => $ta]) }}"
                class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded text-sm font-medium">
                👨‍🏫 Laporan Evaluasi Dosen
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($metrics['cplStats'] as $cpl)
        @php
        $color = $cpl->pct_achieved >= 80 ? 'green' : ($cpl->pct_achieved >= 60 ? 'yellow' : 'red');
        $bg = ['green'=>'bg-green-50 border-green-200','yellow'=>'bg-yellow-50 border-yellow-200','red'=>'bg-red-50 border-red-200'][$color];
        $text = ['green'=>'text-green-700','yellow'=>'text-yellow-700','red'=>'text-red-700'][$color];
        $badge = ['green'=>'✅','yellow'=>'⚠️','red'=>'❌'][$color];
        @endphp
        <div class="border {{ $bg }} rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="font-bold text-sm {{ $text }}">{{ $cpl->kode }}</span>
                <span>{{ $badge }}</span>
            </div>
            <div class="text-2xl font-bold {{ $text }}">{{ $cpl->pct_achieved }}%</div>
            <div class="text-xs text-gray-500 mt-1">{{ $cpl->tercapai }}/{{ $cpl->total }} mhs tercapai</div>
            <div class="text-xs text-gray-400">Rata: {{ $cpl->rata_nilai }}</div>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $color === 'green' ? 'bg-green-500' : ($color === 'yellow' ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ min($cpl->pct_achieved, 100) }}%"></div>
            </div>
        </div>
        @endforeach
        @if($metrics['cplStats']->isEmpty())
        <div class="col-span-4 text-center py-10 text-gray-400 bg-white border rounded-xl">
            <div class="text-4xl mb-2">📊</div>
            Belum ada data capaian CPL untuk TA <strong>{{ $ta }}</strong>.<br>
            <span class="text-sm">Klik <strong>"⚡ Hitung Semua MK"</strong> untuk memulai kalkulasi OBE.</span>
        </div>
        @endif
    </div>

    {{-- ── Mini CPL Bar Chart ── --}}
    @if($metrics['cplStats']->isNotEmpty())
    <div class="bg-white rounded-xl border shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-gray-700">📊 Grafik Capaian CPL — TA {{ $ta }}</h2>
            <a href="{{ route('obe.grafik-cpl', ['ta' => $ta]) }}" class="text-xs text-blue-600 hover:underline">Lihat detail →</a>
        </div>
        <div style="position:relative;height:260px;">
            <canvas id="dashCplChart"></canvas>
        </div>
        <p class="text-xs text-gray-400 mt-2 text-center">🟢 Skor Maksimal &nbsp; 🔵 Skor Sementara (rata-rata capaian mahasiswa)</p>
    </div>
    @endif

    @if($metrics['cohortByAngkatan']->isNotEmpty() || true)
    @include('obe._traffic-light', [
    'cohortByAngkatan' => $metrics['cohortByAngkatan'],
    'cplList' => $metrics['cplList'],
    'widgetTitle' => 'Traffic Light CPL × Angkatan — TA '.$ta,
    ])
    @endif

    @if($metrics['cpmkStats']->isNotEmpty())
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">📉 Capaian CPMK per Mata Kuliah</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2 text-left">CPL</th>
                        <th class="px-4 py-2 text-left">CPMK</th>
                        <th class="px-4 py-2 text-left">Mata Kuliah</th>
                        <th class="px-4 py-2 text-right">Total</th>
                        <th class="px-4 py-2 text-right">Tercapai</th>
                        <th class="px-4 py-2 text-right">Rata Nilai</th>
                        <th class="px-4 py-2 text-center">% Capaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($metrics['cpmkStats'] as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-xs font-medium text-blue-700">{{ $row->cpl_kode }}</td>
                        <td class="px-4 py-2">
                            <div class="font-medium">{{ $row->kode }}</div>
                            <div class="text-xs text-gray-400 max-w-xs truncate">{{ $row->deskripsi }}</div>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-600">{{ $row->mk_nama }}</td>
                        <td class="px-4 py-2 text-right">{{ $row->total }}</td>
                        <td class="px-4 py-2 text-right text-green-600">{{ $row->tercapai }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ $row->rata_nilai }}</td>
                        <td class="px-4 py-2 text-center">
                            @php $p=$row->pct_achieved; $c=$p>=80?'green':($p>=60?'yellow':'red'); @endphp
                            <span class="px-2 py-0.5 rounded text-xs font-medium {{ $c==='green'?'bg-green-100 text-green-700':($c==='yellow'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') }}">{{ $p }}%</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- ── Early Warning Mini Section ── --}}
@php
$warningCount = \Illuminate\Support\Facades\DB::table('cpl_achievement')
->where('semester_aktif', $ta)
->where('achieved', 0)
->distinct('mahasiswa_id')
->count('mahasiswa_id');
@endphp
@if($warningCount > 0)
<div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="text-3xl">⚠️</div>
            <div>
                <div class="font-semibold text-red-700 text-base">
                    {{ $warningCount }} Mahasiswa Perlu Perhatian
                </div>
                <div class="text-sm text-red-600">
                    Terdapat mahasiswa dengan nilai CPL di bawah threshold untuk TA <strong>{{ $ta }}</strong>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('obe.early-warning', ['ta'=>$ta]) }}"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                🔍 Lihat Early Warning
            </a>
            <a href="{{ route('obe.early-warning.csv', ['ta'=>$ta]) }}"
                class="px-4 py-2 border border-red-300 text-red-700 hover:bg-red-100 rounded-lg text-sm font-medium">
                📥 Export CSV
            </a>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- QUALITY MONITORING WIDGETS                                        --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
@php
// Widget 1: CQI Actions summary
try {
    $cqiOpen   = \DB::table('cqi_actions')->where('tahun_akademik', $ta)->where('status', 'open')->count();
    $cqiInProg = \DB::table('cqi_actions')->where('tahun_akademik', $ta)->where('status', 'in_progress')->count();
    $cqiDone   = \DB::table('cqi_actions')->where('tahun_akademik', $ta)->where('status', 'done')->count();
} catch (\Throwable $e) { $cqiOpen = $cqiInProg = $cqiDone = 0; }
$cqiTotal = $cqiOpen + $cqiInProg + $cqiDone;

// Widget 2: RPS vs BAP consistency (count MKs with both)
$rpsBapOk = 0; $rpsBapWarn = 0;
try {
$mkIdsWithBap = \DB::table('bap')->where('semester_aktif', $ta)->pluck('mata_kuliah_id')->unique();
$mkIdsWithRps = \DB::table('rps_pertemuan')->pluck('mata_kuliah_id')->unique();
$rpsBapTotal = $mkIdsWithBap->intersect($mkIdsWithRps)->count();
} catch (\Throwable $e) { $rpsBapTotal = 0; }

// Widget 3: Problematic courses count
try {
$cplByMkCount = \DB::table('cpmk_achievement')->where('semester_aktif', $ta)
->selectRaw('mata_kuliah_id, AVG(nilai_cpmk) as avg_val')
->groupBy('mata_kuliah_id')->get()
->where('avg_val', '<', 70)->count();
    $passLowCount = \DB::table('nilai_mahasiswa')->where('semester_aktif', $ta)
    ->selectRaw('mata_kuliah_id, ROUND(SUM(lulus)/COUNT(*)*100,1) as pass_rate')
    ->groupBy('mata_kuliah_id')->get()
    ->where('pass_rate', '<', 70)->count();
        $problematicCount = max($cplByMkCount, $passLowCount);
        } catch (\Throwable $e) { $problematicCount = 0; }
        @endphp

        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- CQI Widget --}}
            <div class="bg-white border rounded-xl p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 text-sm">🔄 CQI Actions — {{ $ta }}</h3>
                    <a href="{{ route('obe.cqi-monitoring', ['ta' => $ta]) }}" class="text-xs text-blue-600 hover:underline">Lihat →</a>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-red-50 rounded-lg p-2">
                        <p class="text-xl font-bold text-red-600">{{ $cqiOpen }}</p>
                        <p class="text-xs text-gray-500">Open</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-2">
                        <p class="text-xl font-bold text-blue-600">{{ $cqiInProg }}</p>
                        <p class="text-xs text-gray-500">Proses</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-2">
                        <p class="text-xl font-bold text-green-600">{{ $cqiDone }}</p>
                        <p class="text-xs text-gray-500">Selesai</p>
                    </div>
                </div>
                @if($cqiTotal === 0)
                <p class="text-xs text-gray-400 text-center">Belum ada action plan. Klik untuk auto-generate.</p>
                @else
                <div>
                    @php $donePct = $cqiTotal > 0 ? round($cqiDone / $cqiTotal * 100) : 0; @endphp
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Progress</span><span>{{ $donePct }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width:{{ $donePct }}%"></div>
                    </div>
                </div>
                @endif
            </div>

            {{-- RPS vs BAP Widget --}}
            <div class="bg-white border rounded-xl p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 text-sm">📋 Konsistensi RPS vs BAP</h3>
                    <a href="{{ route('obe.rps-bap-consistency', ['ta' => $ta]) }}" class="text-xs text-blue-600 hover:underline">Lihat →</a>
                </div>
                <div class="text-center py-2">
                    <p class="text-3xl font-extrabold text-blue-600">{{ $rpsBapTotal }}</p>
                    <p class="text-xs text-gray-500 mt-1">MK memiliki data RPS & BAP</p>
                </div>
                <p class="text-xs text-gray-400 text-center">
                    Klik untuk lihat persentase konsistensi materi per pertemuan
                </p>
            </div>

            {{-- Problematic Courses Widget --}}
            <div class="bg-white border rounded-xl p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700 text-sm">🔍 MK Bermasalah</h3>
                    <a href="{{ route('obe.problematic-courses', ['ta' => $ta]) }}" class="text-xs text-blue-600 hover:underline">Lihat →</a>
                </div>
                <div class="text-center py-2">
                    @if($problematicCount > 0)
                    <p class="text-3xl font-extrabold text-red-600">{{ $problematicCount }}</p>
                    <p class="text-xs text-gray-500 mt-1">MK terindikasi perlu review</p>
                    @else
                    <p class="text-3xl font-extrabold text-green-600">✓</p>
                    <p class="text-xs text-gray-500 mt-1">Semua MK dalam kondisi normal</p>
                    @endif
                </div>
                <p class="text-xs text-gray-400 text-center">
                    Berdasarkan CPL, Evaluasi Dosen & Pass Rate
                </p>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            function hitungSemua() {
                if (!confirm('Hitung ulang semua CPMK & CPL achievement untuk TA {{ $ta }}?')) return;
                const btn = event.target;
                btn.disabled = true;
                btn.textContent = '⏳ Menghitung...';
                fetch('{{ route("api.obe.hitung-semua") }}?ta={{ urlencode($ta) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    }).then(r => r.json()).then(d => {
                        alert('Pipeline selesai. diproses: ' + d.total_records + ' record' +
                            ' (CPMK: ' + d.cpmk_records + ', CPL: ' + d.cpl_records + ')');
                        location.reload();
                    })
                    .catch(e => {
                        alert('Error: ' + e);
                        btn.disabled = false;
                        btn.textContent = '⚡ Hitung Semua MK';
                    });
            }

            // ── Mini CPL Bar Chart ────────────────────────────────────────
            (function() {
                const canvas = document.getElementById('dashCplChart');
                if (!canvas) return;
                const raw = @json($cplChartData);
                if (!raw.length) return;

                new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: raw.map(d => d.kode),
                        datasets: [{
                                label: 'Skor Maksimal',
                                data: raw.map(d => d.skor_maksimal),
                                backgroundColor: 'rgba(34,197,94,0.75)',
                                borderColor: 'rgba(21,128,61,0.9)',
                                borderWidth: 1,
                                borderRadius: 4,
                            },
                            {
                                label: 'Skor Sementara',
                                data: raw.map(d => d.skor_sementara),
                                backgroundColor: 'rgba(59,130,246,0.75)',
                                borderColor: 'rgba(29,78,216,0.9)',
                                borderWidth: 1,
                                borderRadius: 4,
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
                                        size: 11
                                    },
                                    padding: 16
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    afterBody: (items) => {
                                        const i = items[0].dataIndex;
                                        const gap = (raw[i].skor_maksimal - raw[i].skor_sementara).toFixed(2);
                                        return [`Gap: ${gap}`];
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11,
                                        weight: '600'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                },
                            },
                        },
                    },
                });
            })();
        </script>
        @endsection