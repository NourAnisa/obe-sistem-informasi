@extends('layouts.dashboard')
@section('title', 'Input Nilai SubCPMK - '.$mk->nama)
@section('breadcrumb', 'Input Nilai SubCPMK')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6 flex-wrap">
        <a href="{{ route('nilai-mahasiswa.show', ['mk' => $mk->id, 'ta' => $ta]) }}"
            class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">←</a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-800 font-display">{{ $mk->kode }} — {{ $mk->nama }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Input Nilai Sub-CPMK · TA {{ $ta }} · Semester {{ $mk->semester }} · {{ $mk->sks }} SKS
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('nilai-mahasiswa.show', ['mk' => $mk->id, 'ta' => $ta]) }}"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-medium transition">
                📝 Nilai Komponen
            </a>
            <span class="px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium">
                📐 Nilai Sub-CPMK
            </span>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    @if($cpmksWithSubs->isEmpty())
    {{-- No CPMK defined --}}
    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
        <div class="text-5xl mb-3">📋</div>
        <p class="font-semibold text-gray-600">Belum ada CPMK yang dipetakan ke mata kuliah ini</p>
        <p class="text-sm text-gray-400 mt-1">Pemetaan CPMK-MK dilakukan di menu Kurikulum → Pemetaan CPMK</p>
    </div>

    @elseif($enrollments->isEmpty())
    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-300">
        <div class="text-5xl mb-3">👥</div>
        <p class="font-semibold text-gray-600">Belum ada mahasiswa terdaftar</p>
    </div>

    @else

    {{-- CPMK Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-{{ min(4, $cpmksWithSubs->count()) }} gap-3 mb-6">
        @foreach($cpmksWithSubs as $cpmk)
        <div class="bg-white border rounded-xl p-3">
            <p class="text-xs font-bold text-purple-700">{{ $cpmk->kode }}</p>
            <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-2">{{ $cpmk->deskripsi }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $cpmk->sub_cpmks->count() }} Sub-CPMK</p>
        </div>
        @endforeach
    </div>

    {{-- No SubCPMK defined --}}
    @if($totalSubCols === 0)
    <div class="text-center py-12 bg-amber-50 border border-amber-200 rounded-2xl">
        <div class="text-4xl mb-3">⚠️</div>
        <p class="font-semibold text-amber-700">CPMK sudah dipetakan tetapi belum memiliki Sub-CPMK</p>
        <p class="text-sm text-amber-600 mt-1">Tambahkan Sub-CPMK di menu Kurikulum terlebih dahulu</p>
    </div>

    @else

    <form method="POST" action="{{ route('nilai-mahasiswa.cpmk.save', ['mk' => $mk->id, 'ta' => $ta]) }}" id="cpmkForm">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse" id="cpmkTable">
                    <thead>
                        {{-- Row 1: CPMK group headers --}}
                        <tr class="bg-purple-700 text-white">
                            <th class="px-4 py-3 text-left font-semibold border-r border-purple-600 sticky left-0 bg-purple-700 z-20" rowspan="2">NIM</th>
                            <th class="px-4 py-3 text-left font-semibold border-r border-purple-600 sticky left-20 bg-purple-700 z-20" rowspan="2">Nama Mahasiswa</th>
                            @foreach($cpmksWithSubs as $cpmk)
                            @if($cpmk->sub_cpmks->count() > 0)
                            <th class="px-3 py-2 text-center font-semibold border-r border-purple-500"
                                colspan="{{ $cpmk->sub_cpmks->count() + 1 }}">
                                {{ $cpmk->kode }}
                                <br><span class="text-[10px] font-normal opacity-75">{{ $cpmk->sub_cpmks->count() }} Sub</span>
                            </th>
                            @endif
                            @endforeach
                        </tr>
                        {{-- Row 2: SubCPMK columns + CPMK total --}}
                        <tr class="bg-purple-600 text-white text-xs">
                            @foreach($cpmksWithSubs as $cpmk)
                            @foreach($cpmk->sub_cpmks as $sub)
                            <th class="px-2 py-2 text-center border-r border-purple-500 min-w-[80px]">
                                {{ $sub->kode }}<br>
                                <span class="text-[10px] opacity-75 font-normal block truncate max-w-[75px]" title="{{ $sub->deskripsi }}">
                                    {{ Str::limit($sub->deskripsi, 20) }}
                                </span>
                            </th>
                            @endforeach
                            @if($cpmk->sub_cpmks->count() > 0)
                            <th class="px-2 py-2 text-center border-r border-purple-400 bg-purple-800 min-w-[70px]">
                                Rata<br><span class="text-[10px] font-normal">CPMK</span>
                            </th>
                            @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="cpmkTableBody">
                        @foreach($enrollments as $enr)
                        <tr class="hover:bg-gray-50 cpmk-row" data-mhs="{{ $enr->mahasiswa_id }}">
                            <td class="px-4 py-2 font-mono text-xs text-gray-600 sticky left-0 bg-white z-10 border-r border-gray-100">
                                {{ $enr->nim }}
                                @if($enr->is_pjmk)
                                <span class="ml-1 text-[9px] bg-purple-100 text-purple-700 px-1 py-0.5 rounded-full">PJMK</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 font-medium text-gray-800 sticky left-20 bg-white z-10 border-r border-gray-100">
                                {{ $enr->nama }}
                            </td>

                            @foreach($cpmksWithSubs as $cpmk)
                            @foreach($cpmk->sub_cpmks as $sub)
                            @php $existingNilai = $nilaiSubMap[$enr->mahasiswa_id][$sub->id] ?? null; @endphp
                            <td class="px-1 py-1.5 border-r border-gray-50">
                                <input type="number"
                                    name="nilai[{{ $enr->mahasiswa_id }}][{{ $sub->id }}]"
                                    value="{{ $existingNilai !== null ? number_format($existingNilai, 1, '.', '') : '' }}"
                                    placeholder="0"
                                    min="0" max="100" step="0.5"
                                    class="sub-input w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-center text-sm focus:ring-1 focus:ring-purple-400 focus:border-purple-400"
                                    data-cpmk="{{ $cpmk->id }}"
                                    data-sub="{{ $sub->id }}">
                            </td>
                            @endforeach

                            @if($cpmk->sub_cpmks->count() > 0)
                            @php
                            // Pre-calculate existing CPMK avg for display
                            $achRow = ($cpmkAchMap[$enr->mahasiswa_id] ?? collect())->firstWhere('cpmk_id', $cpmk->id);
                            $existingCpmkVal = $achRow ? $achRow->nilai_cpmk : null;
                            $hasExistingSubs = isset($nilaiSubMap[$enr->mahasiswa_id]);
                            // Live-calculate if we have sub values
                            $subVals = collect($cpmk->sub_cpmks)->map(fn($s) => $nilaiSubMap[$enr->mahasiswa_id][$s->id] ?? null)->filter(fn($v) => $v !== null);
                            $calcVal = $subVals->count() > 0 ? round($subVals->avg(), 2) : $existingCpmkVal;
                            @endphp
                            <td class="px-2 py-1.5 text-center border-r border-gray-100 bg-purple-50"
                                data-cpmk-total="{{ $cpmk->id }}" data-mhs="{{ $enr->mahasiswa_id }}">
                                @if($calcVal !== null)
                                @php
                                $rubric = $calcVal >= 80 ? ['Proficient','green'] : ($calcVal >= 60 ? ['Developing','yellow'] : ['Novice','red']);
                                @endphp
                                <span class="font-bold text-sm {{ $calcVal >= 70 ? 'text-green-600' : ($calcVal >= 56 ? 'text-amber-600' : 'text-red-500') }}">
                                    {{ number_format($calcVal, 1) }}
                                </span>
                                <br>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium
                                        {{ $rubric[1] === 'green' ? 'bg-green-100 text-green-700' : ($rubric[1] === 'yellow' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}
                                        cpmk-badge" data-cpmk-badge="{{ $cpmk->id }}-{{ $enr->mahasiswa_id }}">
                                    {{ $rubric[0] }}
                                </span>
                                @else
                                <span class="text-gray-300 text-xs cpmk-avg-display" data-cpmk-avg="{{ $cpmk->id }}-{{ $enr->mahasiswa_id }}">—</span>
                                @endif
                                {{-- Progress bar --}}
                                <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                    <div class="h-1 rounded-full {{ ($calcVal ?? 0) >= 80 ? 'bg-green-500' : (($calcVal ?? 0) >= 60 ? 'bg-amber-400' : 'bg-red-400') }} cpmk-bar"
                                        data-cpmk-bar="{{ $cpmk->id }}-{{ $enr->mahasiswa_id }}"
                                        style="width:{{ min(100, $calcVal ?? 0) }}%"></div>
                                </div>
                            </td>
                            @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Column Averages --}}
        <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 mb-5 text-xs text-gray-500" id="colAvgSection">
            <p class="font-semibold text-gray-600 mb-2">Rata-rata Kelas per Sub-CPMK:</p>
            <div class="flex flex-wrap gap-3" id="colAvgList">
                @foreach($cpmksWithSubs as $cpmk)
                @foreach($cpmk->sub_cpmks as $sub)
                <div class="bg-white border rounded-lg px-3 py-1.5">
                    <span class="font-medium text-purple-700">{{ $sub->kode }}</span>:
                    <strong id="avg-sub-{{ $sub->id }}">—</strong>
                </div>
                @endforeach
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 flex-wrap">
            <button type="submit"
                class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl shadow transition">
                💾 Simpan Nilai SubCPMK
            </button>
            <button type="button"
                onclick="hitungObe({{ $mk->id }})"
                id="btnHitungObe"
                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow transition">
                ⚡ Hitung CPL
            </button>
            <a href="{{ route('obe.cpmk-evaluasi') }}?mk_id={{ $mk->id }}&ta={{ urlencode($ta) }}"
                class="px-5 py-2.5 bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium rounded-xl transition">
                📊 Lihat Capaian CPMK
            </a>
            <a href="{{ route('nilai-mahasiswa.index', ['ta' => $ta]) }}"
                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                Kembali ke Daftar MK
            </a>
        </div>
    </form>

    @endif {{-- end totalSubCols > 0 --}}
    @endif {{-- end enrollments not empty --}}
</div>

<script>
    (function() {
        const table = document.getElementById('cpmkTableBody');
        if (!table) return;

        // Group sub inputs by CPMK
        function getCpmkSubInputs(row, cpmkId) {
            return Array.from(row.querySelectorAll(`.sub-input[data-cpmk="${cpmkId}"]`));
        }

        function calcCpmkAvg(inputs) {
            const vals = inputs.map(i => parseFloat(i.value)).filter(v => !isNaN(v));
            if (!vals.length) return null;
            return Math.round(vals.reduce((a, b) => a + b, 0) / vals.length * 100) / 100;
        }

        function rubricLabel(v) {
            return v >= 80 ? 'Proficient' : v >= 60 ? 'Developing' : 'Novice';
        }

        function rubricClass(v) {
            return v >= 80 ?
                'bg-green-100 text-green-700' :
                v >= 60 ?
                'bg-amber-100 text-amber-700' :
                'bg-red-100 text-red-700';
        }

        function valueClass(v) {
            return v >= 70 ? 'text-green-600' : v >= 56 ? 'text-amber-600' : 'text-red-500';
        }

        function barClass(v) {
            return v >= 80 ? 'bg-green-500' : v >= 60 ? 'bg-amber-400' : 'bg-red-400';
        }

        function updateCpmkCell(row, cpmkId, mhsId) {
            const inputs = getCpmkSubInputs(row, cpmkId);
            const avg = calcCpmkAvg(inputs);
            const cellKey = `${cpmkId}-${mhsId}`;

            const avgDisplay = document.querySelector(`[data-cpmk-avg="${cellKey}"]`);
            const badge = document.querySelector(`[data-cpmk-badge="${cellKey}"]`);
            const bar = document.querySelector(`[data-cpmk-bar="${cellKey}"]`);
            const totalCell = document.querySelector(`[data-cpmk-total="${cpmkId}"][data-mhs="${mhsId}"]`);

            if (avg === null) {
                if (avgDisplay) avgDisplay.textContent = '—';
                if (badge) badge.textContent = '—';
                if (bar) bar.style.width = '0%';
                return;
            }

            // Find or create value span in cell
            if (totalCell) {
                let valSpan = totalCell.querySelector('.cpmk-val-span');
                if (!valSpan) {
                    totalCell.innerHTML = `<span class="font-bold text-sm cpmk-val-span"></span><br><span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium cpmk-badge"></span><div class="w-full bg-gray-200 rounded-full h-1 mt-1"><div class="h-1 rounded-full cpmk-bar" style="width:0%"></div></div>`;
                }
                valSpan = totalCell.querySelector('.cpmk-val-span');
                const bdg = totalCell.querySelector('.cpmk-badge');
                const br = totalCell.querySelector('.cpmk-bar');

                if (valSpan) {
                    valSpan.textContent = avg.toFixed(1);
                    valSpan.className = `font-bold text-sm cpmk-val-span ${valueClass(avg)}`;
                }
                if (bdg) {
                    bdg.textContent = rubricLabel(avg);
                    bdg.className = `text-[10px] px-1.5 py-0.5 rounded-full font-medium cpmk-badge ${rubricClass(avg)}`;
                }
                if (br) {
                    br.style.width = Math.min(100, avg) + '%';
                    br.className = `h-1 rounded-full cpmk-bar ${barClass(avg)}`;
                }
            }
        }

        function updateColAverages() {
            // For each sub_cpmk column, compute avg across all rows
            const rows = Array.from(table.querySelectorAll('.cpmk-row'));
            const subMap = {};

            rows.forEach(row => {
                row.querySelectorAll('.sub-input').forEach(inp => {
                    const sid = inp.dataset.sub;
                    if (!subMap[sid]) subMap[sid] = [];
                    const v = parseFloat(inp.value);
                    if (!isNaN(v)) subMap[sid].push(v);
                });
            });

            Object.entries(subMap).forEach(([sid, vals]) => {
                const el = document.getElementById(`avg-sub-${sid}`);
                if (el) {
                    const avg = vals.length ? (vals.reduce((a, b) => a + b, 0) / vals.length).toFixed(1) : '—';
                    el.textContent = avg;
                }
            });
        }

        // Attach event listeners
        table.querySelectorAll('.cpmk-row').forEach(row => {
            const mhsId = row.dataset.mhs;
            row.querySelectorAll('.sub-input').forEach(inp => {
                // Initial render
                updateCpmkCell(row, inp.dataset.cpmk, mhsId);

                inp.addEventListener('input', () => {
                    updateCpmkCell(row, inp.dataset.cpmk, mhsId);
                    updateColAverages();
                });
            });
        });

        updateColAverages();
    })();

    function hitungObe(mkId) {
        const ta = '{{ $ta }}';
        const btn = document.getElementById('btnHitungObe');
        if (!confirm('Hitung CPL achievement untuk MK ini (TA ' + ta + ')?')) return;
        btn.disabled = true;
        btn.innerHTML = '⏳ Menghitung...';
        fetch(`/api/obe/hitung/${mkId}?ta=${encodeURIComponent(ta)}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(r => r.json()).then(d => {
            btn.disabled = false;
            btn.innerHTML = '⚡ Hitung CPL';
            if (d.status === 'ok') {
                alert(`✅ Selesai!\nCPMK: ${d.cpmk_records} records\nCPL: ${d.cpl_records} records`);
            } else {
                alert('Error: ' + (d.message || JSON.stringify(d)));
            }
        }).catch(e => {
            btn.disabled = false;
            btn.innerHTML = '⚡ Hitung CPL';
            alert('Error: ' + e);
        });
    }
</script>
@endsection