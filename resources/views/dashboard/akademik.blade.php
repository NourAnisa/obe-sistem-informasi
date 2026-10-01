@extends('layouts.dashboard')
@section('title','Dashboard Akademik')

@section('content')
{{-- ═══ HEADER ═══ --}}
<div class="bg-gradient-to-r from-slate-800 via-brand-900 to-brand-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-brand-200 text-sm mb-2">
                    <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
                    <span>/</span>
                    <span class="text-white font-medium">Dashboard Akademik</span>
                </div>
                <h1 class="text-3xl font-bold font-display">
                    🏛 Selamat datang, <span class="text-brand-200">{{ auth()->user()->name }}</span>
                </h1>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-brand-200 text-sm">Bagian Akademik · S1 Sistem Informasi UNISM</span>
                    <span class="px-2.5 py-0.5 bg-white/20 rounded-full text-xs font-semibold ring-1 ring-white/30">TA 2025/2026</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('kurikulum.index') }}" class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    📚 Kurikulum
                </a>
                <a href="{{ route('mbkm.index') }}" class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 rounded-lg text-sm font-semibold ring-1 ring-white/30 transition">
                    🌐 MBKM
                </a>
                <a href="{{ route('pemetaan.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-500 hover:bg-brand-400 rounded-lg text-sm font-semibold shadow transition">
                    🔗 Pemetaan CPL
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- ═══ STAT CARDS - ROW 1 ═══ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
        $akStats1 = [
            ['label'=>'Total Mata Kuliah','value'=>$stats['total_mk'],'icon'=>'📖','bg'=>'bg-brand-50','iconBg'=>'bg-brand-100 text-brand-700','val'=>'text-brand-700','border'=>'border-brand-200'],
            ['label'=>'Total SKS','value'=>$stats['total_sks'],'icon'=>'⚖️','bg'=>'bg-indigo-50','iconBg'=>'bg-indigo-100 text-indigo-700','val'=>'text-indigo-700','border'=>'border-indigo-200'],
            ['label'=>'Total CPL','value'=>$stats['total_cpl'],'icon'=>'🎯','bg'=>'bg-violet-50','iconBg'=>'bg-violet-100 text-violet-700','val'=>'text-violet-700','border'=>'border-violet-200'],
            ['label'=>'Total CPMK','value'=>$stats['total_cpmk'],'icon'=>'📋','bg'=>'bg-sky-50','iconBg'=>'bg-sky-100 text-sky-700','val'=>'text-sky-700','border'=>'border-sky-200'],
        ];
        $akStats2 = [
            ['label'=>'Sub CPMK','value'=>$stats['total_subcpmk'],'icon'=>'🔖','bg'=>'bg-teal-50','iconBg'=>'bg-teal-100 text-teal-700','val'=>'text-teal-700','border'=>'border-teal-200'],
            ['label'=>'MK Wajib','value'=>$stats['mk_wajib'],'icon'=>'✅','bg'=>'bg-emerald-50','iconBg'=>'bg-emerald-100 text-emerald-700','val'=>'text-emerald-700','border'=>'border-emerald-200'],
            ['label'=>'MK MBKM','value'=>$stats['mk_mbkm'],'icon'=>'🌐','bg'=>'bg-amber-50','iconBg'=>'bg-amber-100 text-amber-700','val'=>'text-amber-700','border'=>'border-amber-200'],
            ['label'=>'Total Dosen','value'=>$stats['total_dosen'],'icon'=>'👩‍🏫','bg'=>'bg-rose-50','iconBg'=>'bg-rose-100 text-rose-700','val'=>'text-rose-700','border'=>'border-rose-200'],
        ];
        @endphp
        @foreach($akStats1 as $card)
        <div class="card-hover {{ $card['bg'] }} rounded-2xl p-5 border {{ $card['border'] }} shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <span class="w-10 h-10 {{ $card['iconBg'] }} rounded-xl flex items-center justify-center text-xl">{{ $card['icon'] }}</span>
            </div>
            <p class="text-3xl font-bold {{ $card['val'] }} font-display">{{ $card['value'] }}</p>
            <p class="text-sm font-semibold text-slate-700 mt-1">{{ $card['label'] }}</p>
        </div>
        @endforeach
    </div>
    {{-- STAT CARDS - ROW 2 --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach($akStats2 as $card)
        <div class="card-hover {{ $card['bg'] }} rounded-2xl p-5 border {{ $card['border'] }} shadow-sm">
            <div class="flex items-start justify-between mb-3">
                <span class="w-10 h-10 {{ $card['iconBg'] }} rounded-xl flex items-center justify-center text-xl">{{ $card['icon'] }}</span>
            </div>
            <p class="text-3xl font-bold {{ $card['val'] }} font-display">{{ $card['value'] }}</p>
            <p class="text-sm font-semibold text-slate-700 mt-1">{{ $card['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ═══ CHARTS ═══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-brand-100 rounded-lg flex items-center justify-center text-sm">📊</span>
                SKS per Semester
            </h3>
            <div class="relative h-56">
                <canvas id="akSksSemChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center text-sm">🥧</span>
                MK per Kategori
            </h3>
            <div class="relative h-56">
                <canvas id="akKatChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ═══ DOKUMEN RPS MATRIX ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-brand-100 rounded-lg flex items-center justify-center text-sm">📄</span>
                Matriks Dokumen RPS per Semester
            </h3>
        </div>
        @foreach($mkBySemester as $semester => $mks)
        <div class="border-b border-slate-50 last:border-0">
            <div class="px-6 py-2 bg-brand-50">
                <h4 class="text-sm font-bold text-brand-800 flex items-center gap-2">
                    📅 Semester {{ $semester }}
                    <span class="text-xs font-normal text-brand-600 bg-brand-100 px-2 py-0.5 rounded-full">{{ $mks->count() }} MK · {{ $mks->sum('sks') }} SKS</span>
                </h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Kode</th>
                            <th class="px-4 py-2 text-left font-semibold">Nama Mata Kuliah</th>
                            <th class="px-4 py-2 text-center font-semibold">SKS</th>
                            <th class="px-4 py-2 text-center font-semibold">CPMK</th>
                            <th class="px-4 py-2 text-center font-semibold">CPL</th>
                            <th class="px-4 py-2 text-center font-semibold">Status RPS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($mks as $mk)
                        @php
                            $hasCpl = $mk->cpls->isNotEmpty();
                            $hasCpmk = $mk->cpmks->isNotEmpty();
                            $isComplete = $hasCpl && $hasCpmk;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-2.5">
                                <span class="font-mono text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ $mk->kode }}</span>
                            </td>
                            <td class="px-4 py-2.5 font-medium text-slate-800">{{ $mk->nama }}</td>
                            <td class="px-4 py-2.5 text-center text-sky-700 font-bold">{{ $mk->sks }}</td>
                            <td class="px-4 py-2.5 text-center text-slate-600">{{ $mk->cpmks->count() }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex flex-wrap justify-center gap-1">
                                    @foreach($mk->cpls->take(3) as $cpl)
                                    <span class="text-xs bg-violet-100 text-violet-700 px-1.5 py-0.5 rounded font-medium">{{ $cpl->kode }}</span>
                                    @endforeach
                                    @if($mk->cpls->count() > 3)
                                    <span class="text-xs text-slate-400">+{{ $mk->cpls->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if($isComplete)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">✅ Tersedia</span>
                                @else
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-semibold">⚠️ Perlu Lengkap</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ MBKM SECTION ═══ --}}
    @if($mbkmActivities->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center text-sm">🌐</span>
                Program MBKM/BKP
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Bentuk Kegiatan</th>
                        <th class="px-4 py-3 text-center font-semibold">SKS Reguler</th>
                        <th class="px-4 py-3 text-center font-semibold">SKS MBKM Maks</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($mbkmActivities as $mbkm)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $mbkm->bentuk_kegiatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-slate-600">{{ $mbkm->sks_reguler ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-bold text-amber-700">{{ $mbkm->sks_mbkm_maks ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 max-w-xs">{{ Str::limit($mbkm->deskripsi ?? '-', 80) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ═══ CPL OVERVIEW ═══ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center text-sm">🎯</span>
                Overview Capaian Pembelajaran Lulusan (CPL)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold">Deskripsi</th>
                        <th class="px-4 py-3 text-center font-semibold">Jumlah MK</th>
                        <th class="px-4 py-3 text-center font-semibold">Jumlah CPMK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($cplList as $cpl)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-brand-100 text-brand-700 px-2 py-1 rounded-lg font-bold">{{ $cpl->kode }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ Str::limit($cpl->deskripsi ?? $cpl->nama ?? '-', 80) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-brand-700">{{ $cpl->mata_kuliahs_count }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold text-violet-700">{{ $cpl->cpmks_count }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-8 text-slate-400">Belum ada data CPL</td></tr>
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
    const semSks    = @json($sksBySemester->pluck('total_sks'));
    const semMk     = @json($sksBySemester->pluck('total_mk'));

    new Chart(document.getElementById('akSksSemChart'), {
        type: 'bar',
        data: {
            labels: semLabels,
            datasets: [
                { label: 'Total SKS', data: semSks, backgroundColor: 'rgba(30,64,175,0.75)', borderColor: '#1d4ed8', borderWidth: 1.5, borderRadius: 6 },
                { label: 'Jumlah MK', data: semMk, type: 'line', borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.1)', pointBackgroundColor: '#059669', pointRadius: 5, fill: true, tension: 0.4, yAxisID: 'y2' }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 11 } } },
                y2: { position: 'right', beginAtZero: true, ticks: { font: { size: 11 } }, grid: { drawOnChartArea: false } },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });

    const katLabels = @json($mkByKategori->pluck('kategori')->map(fn($k) => $k ?? 'Lainnya'));
    const katData   = @json($mkByKategori->pluck('total'));
    const chartColors = ['#1d4ed8','#7c3aed','#059669','#d97706','#dc2626','#0891b2'];

    new Chart(document.getElementById('akKatChart'), {
        type: 'doughnut',
        data: {
            labels: katLabels,
            datasets: [{ data: katData, backgroundColor: chartColors.slice(0, katLabels.length), borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 }, padding: 10 } }
            }
        }
    });
})();
</script>
@endpush
