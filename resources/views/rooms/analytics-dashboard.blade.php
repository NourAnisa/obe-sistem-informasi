@extends('layouts.dashboard')
@section('title', 'Room Utilization Dashboard')
@section('breadcrumb', 'Rooms Dashboard')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">🏢 Room Utilization Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Monitoring utilisasi ruangan & korelasi capaian OBE</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" class="flex items-center gap-2">
                <label class="text-xs text-gray-500">TA:</label>
                <select name="ta" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-brand-500">
                    @foreach(['2025/2026','2024/2025','2023/2024'] as $year)
                    <option value="{{ $year }}" @selected($year===$ta)>{{ $year }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('rooms.index') }}"
                class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
                🏢 Kelola Ruangan
            </a>
            <a href="{{ route('course-schedules.index') }}"
                class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
                📅 Jadwal Kuliah
            </a>
        </div>
    </div>

    {{-- ── Summary Cards ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach([
        ['Total Ruangan', $summary['total_rooms'] ?? 0, 'text-brand-700', 'bg-brand-50', 'border-brand-100'],
        ['Jadwal Aktif', $summary['total_schedules'] ?? 0, 'text-green-700', 'bg-green-50', 'border-green-100'],
        ['Avg Utilisasi', ($summary['avg_utilization'] ?? 0).'%', 'text-blue-700', 'bg-blue-50', 'border-blue-100'],
        ['Utilisasi Tertinggi', ($summary['max_utilization'] ?? 0).'%', 'text-amber-700', 'bg-amber-50', 'border-amber-100'],
        ['Konflik Jadwal', $summary['conflict_count'] ?? 0, ($summary['conflict_count'] ?? 0) > 0 ? 'text-red-700' : 'text-green-700', ($summary['conflict_count'] ?? 0) > 0 ? 'bg-red-50' : 'bg-green-50', ($summary['conflict_count'] ?? 0) > 0 ? 'border-red-100' : 'border-green-100'],
        ['Tahun Akademik', $ta, 'text-gray-700', 'bg-gray-50', 'border-gray-200'],
        ] as [$label, $val, $textCls, $bgCls, $borderCls])
        <div class="rounded-2xl border {{ $borderCls }} {{ $bgCls }} p-4 text-center">
            <div class="text-2xl font-extrabold {{ $textCls }}">{{ $val }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Auto Scheduler + Conflict ───────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Auto Scheduler --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-brand-600 text-white px-5 py-3 font-semibold text-sm">⚡ Auto Scheduler</div>
            <div class="p-5 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Semester</label>
                        <select id="as-semester"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                            @for($s=1;$s<=8;$s++)
                                <option value="{{ $s }}">Semester {{ $s }}</option>
                                @endfor
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Tahun Akademik</label>
                        <input type="text" id="as-ta" value="{{ $ta }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" id="as-overwrite" class="rounded border-gray-300 text-brand-600">
                    Timpa jadwal yang sudah ada
                </label>
                <div class="flex flex-wrap gap-2">
                    <button onclick="previewSchedule(event)"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-medium transition">
                        👁 Preview
                    </button>
                    <button onclick="generateSchedule(event)"
                        class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                        ⚡ Generate Jadwal
                    </button>
                    <button onclick="syncStudents(event)"
                        class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl text-sm font-medium transition">
                        🔄 Sync Mahasiswa
                    </button>
                </div>
                <div id="as-result"></div>
            </div>
        </div>

        {{-- Conflict Alert --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="{{ ($conflicts['conflict_count'] ?? 0) > 0 ? 'bg-red-600' : 'bg-green-600' }} text-white px-5 py-3 font-semibold text-sm">
                ⚠️ Konflik Jadwal
                <span class="ml-1 bg-white bg-opacity-20 rounded-full px-2 py-0.5 text-xs">
                    {{ $conflicts['conflict_count'] ?? 0 }}
                </span>
            </div>
            <div class="p-4 max-h-56 overflow-y-auto space-y-2">
                @if(($conflicts['conflict_count'] ?? 0) === 0)
                <div class="text-center py-6 text-green-600 font-medium">✅ Tidak ada konflik jadwal</div>
                @else
                @foreach($conflicts['details'] as $c)
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-xs text-red-800">
                    <span class="font-semibold">{{ $c['day'] }}</span> — Room #{{ $c['room_id'] }}<br>
                    <span class="text-gray-500">A:</span> MK #{{ $c['schedule_a']['mk_id'] }} {{ $c['schedule_a']['time'] }}<br>
                    <span class="text-gray-500">B:</span> MK #{{ $c['schedule_b']['mk_id'] }} {{ $c['schedule_b']['time'] }}
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- ── Most Used / Least Used ──────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-green-600 text-white px-5 py-3 font-semibold text-sm">📈 Paling Banyak Digunakan</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-center">Kap.</th>
                            <th class="px-3 py-2 text-center">Jadwal</th>
                            <th class="px-3 py-2 text-left w-32">Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mostUsed as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 font-mono font-semibold text-gray-700">{{ $r['room_code'] }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $r['room_name'] }}</td>
                            <td class="px-3 py-2 text-center text-gray-500">{{ $r['capacity'] }}</td>
                            <td class="px-3 py-2 text-center font-semibold text-green-700">{{ $r['schedule_count'] }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-green-500 transition-all"
                                            style="width:{{ min($r['utilization_pct'],100) }}%"></div>
                                    </div>
                                    <span class="text-gray-600 text-[10px] w-8 text-right">{{ $r['utilization_pct'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada data jadwal</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-amber-500 text-white px-5 py-3 font-semibold text-sm">📉 Paling Jarang Digunakan</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-center">Kap.</th>
                            <th class="px-3 py-2 text-center">Jadwal</th>
                            <th class="px-3 py-2 text-left w-32">Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($leastUsed as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 font-mono font-semibold text-gray-700">{{ $r['room_code'] }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $r['room_name'] }}</td>
                            <td class="px-3 py-2 text-center text-gray-500">{{ $r['capacity'] }}</td>
                            <td class="px-3 py-2 text-center text-gray-500">{{ $r['schedule_count'] }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-amber-400 transition-all"
                                            style="width:{{ min($r['utilization_pct'],100) }}%"></div>
                                    </div>
                                    <span class="text-gray-600 text-[10px] w-8 text-right">{{ $r['utilization_pct'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada data jadwal</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Full Utilization Table ───────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="font-semibold text-gray-700">📊 Utilisasi Semua Ruangan — TA {{ $ta }}</span>
            <span class="text-xs text-gray-400">{{ count($utilization) }} ruangan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-2 text-left">Kode</th>
                        <th class="px-3 py-2 text-left">Nama</th>
                        <th class="px-3 py-2 text-left">Gedung</th>
                        <th class="px-3 py-2 text-center">Tipe</th>
                        <th class="px-3 py-2 text-center">Kap.</th>
                        <th class="px-3 py-2 text-center">Jadwal/Minggu</th>
                        <th class="px-3 py-2 text-center">Menit/Minggu</th>
                        <th class="px-3 py-2 text-left w-36">Utilisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($utilization as $r)
                    @php
                    $pct = min($r['utilization_pct'], 100);
                    $color = $pct >= 70 ? 'bg-green-500' : ($pct >= 40 ? 'bg-blue-400' : 'bg-gray-300');
                    $txtCl = $pct >= 70 ? 'text-green-700' : ($pct >= 40 ? 'text-blue-700' : 'text-gray-500');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono font-semibold text-gray-700">{{ $r['room_code'] }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $r['room_name'] }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $r['building'] ?? '—' }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-600">
                                {{ $r['type'] }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center text-gray-500">{{ $r['capacity'] }}</td>
                        <td class="px-3 py-2 text-center font-semibold {{ $txtCl }}">{{ $r['schedule_count'] }}</td>
                        <td class="px-3 py-2 text-center text-gray-500">{{ $r['scheduled_minutes_per_week'] }}</td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $color }} transition-all" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="{{ $txtCl }} text-[10px] w-8 text-right font-semibold">{{ $r['utilization_pct'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada data ruangan aktif</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── OBE Correlation ─────────────────────────────────────────────── --}}
    @if($obeCorrel->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-indigo-50 flex items-center gap-2">
            <span class="font-semibold text-indigo-800">🔬 Korelasi Ruangan × Capaian OBE (CPL)</span>
            <span class="text-xs text-indigo-500">{{ $obeCorrel->count() }} ruangan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-2 text-left">Kode</th>
                        <th class="px-3 py-2 text-left">Nama</th>
                        <th class="px-3 py-2 text-center">Kapasitas</th>
                        <th class="px-3 py-2 text-center">Jml MK</th>
                        <th class="px-3 py-2 text-center">Jml Mhs</th>
                        <th class="px-3 py-2 text-center">Avg CPMK</th>
                        <th class="px-3 py-2 text-center">Avg CPL</th>
                        <th class="px-3 py-2 text-left w-32">% Capai CPMK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($obeCorrel as $r)
                    @php $cpct = min(round($r->cpmk_achievement_pct ?? 0), 100); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono font-semibold text-gray-700">{{ $r->room_code }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $r->room_name }}</td>
                        <td class="px-3 py-2 text-center text-gray-500">{{ $r->capacity }}</td>
                        <td class="px-3 py-2 text-center font-semibold text-indigo-700">{{ $r->mk_count }}</td>
                        <td class="px-3 py-2 text-center text-gray-600">{{ $r->student_count }}</td>
                        <td class="px-3 py-2 text-center font-semibold {{ ($r->avg_cpmk_score ?? 0) >= 56 ? 'text-green-700' : 'text-red-600' }}">
                            {{ number_format($r->avg_cpmk_score ?? 0, 1) }}
                        </td>
                        <td class="px-3 py-2 text-center font-semibold {{ ($r->avg_cpl_score ?? 0) >= 56 ? 'text-green-700' : 'text-red-600' }}">
                            {{ number_format($r->avg_cpl_score ?? 0, 1) }}
                        </td>
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $cpct >= 60 ? 'bg-green-500' : 'bg-red-400' }} transition-all"
                                        style="width:{{ $cpct }}%"></div>
                                </div>
                                <span class="{{ $cpct >= 60 ? 'text-green-700' : 'text-red-600' }} text-[10px] w-8 text-right font-semibold">{{ $cpct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

    function alertBox(type, msg) {
        const cls = type === 'success' ?
            'bg-green-50 border-green-200 text-green-800' :
            'bg-red-50 border-red-200 text-red-800';
        return `<div class="mt-3 p-3 rounded-xl border text-sm ${cls}">${msg}</div>`;
    }

    async function generateSchedule(e) {
        const btn = e.target;
        btn.disabled = true;
        btn.textContent = '⏳ Generating...';
        try {
            const res = await fetch('{{ route("schedule.auto-generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    semester: document.getElementById('as-semester').value,
                    academic_year: document.getElementById('as-ta').value,
                    overwrite: document.getElementById('as-overwrite').checked,
                }),
            });
            const data = await res.json();
            let extra = '';
            if (data.data?.errors?.length) {
                extra = '<ul class="mt-1 list-disc list-inside text-xs">' + data.data.errors.map(e => `<li>${e}</li>`).join('') + '</ul>';
            }
            document.getElementById('as-result').innerHTML = alertBox(data.success ? 'success' : 'error', (data.message || data.error || 'Error') + extra);
            if (data.success) setTimeout(() => location.reload(), 2000);
        } catch (err) {
            document.getElementById('as-result').innerHTML = alertBox('error', err.message);
        }
        btn.disabled = false;
        btn.textContent = '⚡ Generate Jadwal';
    }

    async function previewSchedule(e) {
        const btn = e.target;
        btn.disabled = true;
        btn.textContent = '⏳...';
        try {
            const res = await fetch('{{ route("schedule.preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    semester: document.getElementById('as-semester').value,
                    academic_year: document.getElementById('as-ta').value,
                }),
            });
            const data = await res.json();
            let html = `<div class="mt-3 p-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm">${data.count} MK akan dijadwalkan</div>`;
            if (data.data?.length) {
                html += '<div class="mt-2 overflow-x-auto"><table class="min-w-full text-xs border border-gray-200 rounded-xl overflow-hidden"><thead class="bg-gray-50 text-gray-500"><tr><th class="px-2 py-1">MK</th><th class="px-2 py-1">SKS</th><th class="px-2 py-1">Hari</th><th class="px-2 py-1">Jam</th><th class="px-2 py-1">Ruang</th><th class="px-2 py-1">Mhs</th><th class="px-2 py-1">Status</th></tr></thead><tbody class="divide-y divide-gray-100">';
                data.data.forEach(r => {
                    html += `<tr class="hover:bg-gray-50"><td class="px-2 py-1 font-mono">${r.mk_kode}</td><td class="px-2 py-1 text-center">${r.sks}</td><td class="px-2 py-1">${r.day_of_week||'-'}</td><td class="px-2 py-1">${r.start_time||'-'}–${r.end_time||'-'}</td><td class="px-2 py-1 font-mono">${r.room_code||'❌'}</td><td class="px-2 py-1 text-center">${r.student_count}</td><td class="px-2 py-1">${r.status==='ok'?'✅':'⚠️ '+r.status}</td></tr>`;
                });
                html += '</tbody></table></div>';
            }
            document.getElementById('as-result').innerHTML = html;
        } catch (err) {
            document.getElementById('as-result').innerHTML = alertBox('error', err.message);
        }
        btn.disabled = false;
        btn.textContent = '👁 Preview';
    }

    async function syncStudents(e) {
        const btn = e.target;
        btn.disabled = true;
        btn.textContent = '⏳...';
        try {
            const res = await fetch('{{ route("schedule.sync-student-count") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    academic_year: document.getElementById('as-ta').value
                }),
            });
            const data = await res.json();
            document.getElementById('as-result').innerHTML = alertBox(data.success ? 'success' : 'error', data.message);
        } catch (err) {
            document.getElementById('as-result').innerHTML = alertBox('error', err.message);
        }
        btn.disabled = false;
        btn.textContent = '🔄 Sync Mahasiswa';
    }
</script>
@endpush
@endsection
