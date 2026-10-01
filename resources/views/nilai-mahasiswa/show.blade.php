@extends('layouts.dashboard')
@section('title', 'Input Nilai - '.$mk->nama)
@section('breadcrumb', 'Input Nilai Mahasiswa')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('nilai-mahasiswa.index', ['ta' => $ta]) }}"
            class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">←</a>
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-800 font-display">{{ $mk->kode }} — {{ $mk->nama }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                @if($hasSubCpmk)
                Input nilai SubCPMK · OBE Grading ·
                @else
                Input nilai per komponen ·
                @endif
                TA {{ $ta }} · Semester {{ $mk->semester }} · {{ $mk->sks }} SKS
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Export Excel --}}
            <a href="{{ route('nilai-mahasiswa.export', ['mk' => $mk->id, 'ta' => $ta]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 3v12"/>
                </svg>
                Export Excel
            </a>
            {{-- Import Excel --}}
            <button type="button" onclick="document.getElementById('modalImport').classList.remove('hidden')"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 14l5-5 5 5M12 9v10"/>
                </svg>
                Import Excel
            </button>
            <a href="{{ route('bobot-penilaian.show', $mk->id) }}"
                class="inline-flex items-center gap-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition border border-gray-300">
                ⚙ Atur Bobot
            </a>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="modalImport" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">📥 Import Nilai dari Excel/CSV</h3>
                <button onclick="document.getElementById('modalImport').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 space-y-1">
                <p class="font-semibold">Format kolom yang dibutuhkan:</p>
                @if($hasSubCpmk)
                <p class="font-mono text-xs">nim | sub_cpmk_kode | tugas | uts | uas | partisipatif | proyek</p>
                <p class="text-xs text-blue-600">Satu baris per NIM per Sub-CPMK.</p>
                @else
                <p class="font-mono text-xs">nim | tugas | uts | uas | partisipatif | proyek</p>
                <p class="text-xs text-blue-600">Satu baris per NIM.</p>
                @endif
                <a href="{{ route('nilai-mahasiswa.export-template', ['mk' => $mk->id, 'ta' => $ta]) }}"
                   class="inline-flex items-center gap-1 text-blue-600 underline text-xs font-semibold">
                    ⬇ Download Template CSV
                </a>
            </div>
            <form method="POST" action="{{ route('nilai-mahasiswa.import', ['mk' => $mk->id, 'ta' => $ta]) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih file Excel / CSV</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                        class="block w-full text-sm text-gray-700 border border-gray-300 rounded-xl px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100">
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('modalImport').classList.add('hidden')"
                        class="px-4 py-2 text-sm rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">
                        Upload &amp; Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    @if(session('warning'))
    <div class="mb-5 p-4 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-xl text-sm flex items-start gap-2">
        <span>⚠</span> {{ session('warning') }}
    </div>
    @endif

    @if($errors->has('bobot'))
    <div class="mb-5 p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl text-sm flex items-start gap-2">
        <span class="mt-0.5">❌</span>
        <div>
            <p class="font-semibold">Validasi Bobot Gagal</p>
            <p>{{ $errors->first('bobot') }}</p>
            <p class="mt-1 text-xs text-red-600">Atur bobot SubCPMK di halaman Manajemen SubCPMK sebelum menyimpan nilai.</p>
        </div>
    </div>
    @endif

    {{-- Bobot Info --}}
    <div class="bg-brand-50 border border-brand-100 rounded-2xl p-4 mb-6">
        <p class="text-xs font-semibold text-brand-700 mb-2 uppercase tracking-wide">Bobot Komponen Penilaian</p>
        <div class="flex flex-wrap gap-3">
            @foreach([['Tugas',$bobot['tugas']],['UTS',$bobot['uts']],['UAS',$bobot['uas']],['Partisipatif',$bobot['partisipatif']],['Proyek',$bobot['proyek']]] as [$label,$bv])
            <div class="flex items-center gap-1.5 bg-white border border-brand-200 rounded-xl px-3 py-1.5">
                <span class="text-xs text-gray-600">{{ $label }}</span>
                <span class="text-sm font-bold text-brand-700">{{ $bv }}%</span>
            </div>
            @endforeach
            <div class="flex items-center gap-1.5 bg-brand-600 rounded-xl px-3 py-1.5 ml-auto">
                <span class="text-xs text-brand-100">Total</span>
                <span class="text-sm font-bold text-white">{{ array_sum($bobot) }}%</span>
            </div>
        </div>
        @if($hasSubCpmk)
        <p class="text-xs text-gray-400 mt-2">
            Pipeline OBE: <strong>Komponen → Nilai SubCPMK → Nilai CPMK → Nilai Akhir</strong>
            · Lulus ≥ 56 · A≥80, B≥70, C≥56, D≥40, E&lt;40
        </p>
        @else
        <p class="text-xs text-gray-400 mt-2">Nilai Akhir dihitung otomatis · Lulus jika ≥ 56 · A≥80, B≥70, C≥56, D≥40, E&lt;40</p>
        @endif
    </div>

    @if($enrollments->isEmpty())
    <div class="text-center py-16 text-gray-400 bg-white rounded-2xl border border-gray-100">
        <div class="text-5xl mb-3">👥</div>
        <p class="font-medium">Belum ada mahasiswa yang terdaftar</p>
        <p class="text-sm mt-1">Mahasiswa harus mengambil KRS dan disetujui PA terlebih dahulu</p>
    </div>
    @else
    <form method="POST" action="{{ route('nilai-mahasiswa.save', ['mk' => $mk->id, 'ta' => $ta]) }}">
        @csrf
        <input type="hidden" name="mode" value="{{ $hasSubCpmk ? 'subcpmk' : 'komponen' }}">

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- MODE A: SubCPMK-based unified grading (OBE compliant)          --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        @if($hasSubCpmk)

        {{-- CPMK legend --}}
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($cpmksWithSubs as $cpmk)
            <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-3 py-1.5 text-xs">
                <span class="w-3 h-3 rounded-full" style="background:{{ ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6'][$loop->index % 6] }}"></span>
                <span class="font-semibold text-gray-700">{{ $cpmk->kode }}</span>
                <span class="text-gray-400">{{ $cpmk->sub_cpmks->count() }} Sub</span>
            </div>
            @endforeach
        </div>

        {{-- Per-student blocks --}}
        @foreach($enrollments as $enr)
        @php
        $mhsId = $enr->mahasiswa_id;
        $nilaiMhs = $nilaiMap[$mhsId] ?? null;
        $totalSubs = $cpmksWithSubs->sum(fn($c) => $c->sub_cpmks->count());
        $palette = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#8b5cf6'];
        @endphp
        <div class="student-block mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
            data-mhs="{{ $mhsId }}">

            {{-- Student header --}}
            <div class="flex items-center gap-4 px-5 py-3 bg-gray-50 border-b border-gray-100">
                <div class="flex-1 min-w-0">
                    <span class="font-mono text-xs text-gray-500">{{ $enr->nim }}</span>
                    @if($enr->is_pjmk)
                    <span class="ml-1.5 text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">PJMK</span>
                    @endif
                    <span class="ml-3 font-semibold text-gray-800 text-sm">{{ $enr->nama }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <span class="text-gray-400 text-xs">Nilai Akhir:</span>
                    <span class="student-nilai-akhir font-bold text-lg
                        {{ $nilaiMhs && $nilaiMhs->nilai_akhir >= 70 ? 'text-green-600' : ($nilaiMhs && $nilaiMhs->nilai_akhir >= 56 ? 'text-amber-600' : 'text-red-500') }}">
                        {{ $nilaiMhs ? number_format($nilaiMhs->nilai_akhir, 2) : '—' }}
                    </span>
                    <span class="student-grade-badge inline-flex items-center justify-center w-9 h-9 rounded-xl font-bold text-sm
                        {{ $nilaiMhs ? match(substr($nilaiMhs->grade,0,1)){'A'=>'bg-green-100 text-green-700','B'=>'bg-blue-100 text-blue-700','C'=>'bg-amber-100 text-amber-700','D'=>'bg-orange-100 text-orange-700',default=>'bg-red-100 text-red-700'} : 'bg-gray-100 text-gray-300' }}">
                        {{ $nilaiMhs ? $nilaiMhs->grade : '—' }}
                    </span>
                    @if($nilaiMhs)
                    @if($nilaiMhs->lulus)
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Lulus</span>
                    @else
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Tidak Lulus</span>
                    @endif
                    @endif
                </div>
            </div>

            {{-- SubCPMK table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="bg-gray-100 text-gray-500 uppercase tracking-wider">
                            <th class="px-3 py-2 text-left w-28">CPMK</th>
                            <th class="px-3 py-2 text-left w-32">Sub-CPMK</th>
                            <th class="px-3 py-2 text-center w-24">Tugas<br><span class="normal-case font-normal">({{ $bobot['tugas'] }}%)</span></th>
                            <th class="px-3 py-2 text-center w-24">UTS<br><span class="normal-case font-normal">({{ $bobot['uts'] }}%)</span></th>
                            <th class="px-3 py-2 text-center w-24">UAS<br><span class="normal-case font-normal">({{ $bobot['uas'] }}%)</span></th>
                            <th class="px-3 py-2 text-center w-28">Partisipatif<br><span class="normal-case font-normal">({{ $bobot['partisipatif'] }}%)</span></th>
                            <th class="px-3 py-2 text-center w-24">Proyek<br><span class="normal-case font-normal">({{ $bobot['proyek'] }}%)</span></th>
                            <th class="px-3 py-2 text-center w-28 bg-indigo-50 text-indigo-600">Nilai Sub-CPMK</th>
                            <th class="px-3 py-2 text-center w-28 bg-blue-50 text-blue-600">Nilai CPMK</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($cpmksWithSubs as $ci => $cpmk)
                        @php $color = $palette[$ci % count($palette)]; @endphp
                        @foreach($cpmk->sub_cpmks as $si => $sub)
                        @php
                        $existing = $nilaiSubMap[$mhsId][$sub->id] ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50 subcpmk-row"
                            data-mhs="{{ $mhsId }}"
                            data-sub="{{ $sub->id }}"
                            data-cpmk="{{ $cpmk->id }}"
                            @php $sb=$subBobotMap[$sub->id] ?? null; @endphp
                            data-bobot='{{ json_encode([
                                "tugas"        => $sb ? $sb->bobot_tugas        : $bobot["tugas"],
                                "uts"          => $sb ? $sb->bobot_uts          : $bobot["uts"],
                                "uas"          => $sb ? $sb->bobot_uas          : $bobot["uas"],
                                "partisipatif" => $sb ? $sb->bobot_partisipatif : $bobot["partisipatif"],
                                "proyek"       => $sb ? $sb->bobot_proyek       : $bobot["proyek"],
                            ]) }}'>

                            {{-- CPMK cell: only on first subcpmk of group --}}
                            @if($si === 0)
                            <td class="px-3 py-2 align-top font-semibold text-white rounded-bl-none"
                                rowspan="{{ $cpmk->sub_cpmks->count() }}"
                                style="background:{{ $color }}20; border-left:3px solid {{ $color }};">
                                <div class="font-bold text-gray-700">{{ $cpmk->kode }}</div>
                                <div class="cpmk-avg text-xs font-semibold mt-1" style="color:{{ $color }}">—</div>
                                <div class="w-full bg-gray-100 rounded-full h-1 mt-1">
                                    <div class="cpmk-bar h-1 rounded-full transition-all duration-300" style="width:0%;background:{{ $color }}"></div>
                                </div>
                            </td>
                            @endif

                            <td class="px-3 py-2 text-gray-600 font-mono">{{ $sub->kode }}</td>

                            {{-- Komponen inputs --}}
                            @foreach(['tugas','uts','uas','partisipatif','proyek'] as $k)
                            <td class="px-1 py-1.5">
                                <input type="number"
                                    name="nilai[{{ $mhsId }}][{{ $sub->id }}][{{ $k }}]"
                                    value="{{ $existing ? ($existing->{$k} ?? 0) : 0 }}"
                                    min="0" max="100" step="0.5"
                                    class="sub-inp w-20 border border-gray-200 rounded-lg px-2 py-1 text-center text-sm focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400"
                                    data-komponen="{{ $k }}">
                            </td>
                            @endforeach

                            <td class="px-3 py-2 text-center bg-indigo-50">
                                <span class="sub-nilai font-semibold text-indigo-700">
                                    {{ $existing ? number_format($existing->nilai_subcpmk ?? 0, 2) : '—' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center bg-blue-50" rowspan="{{ $si === 0 ? $cpmk->sub_cpmks->count() : '' }}">
                                @if($si === 0)
                                <span class="cpmk-nilai-cell font-semibold text-blue-700">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- MODE B: Simple komponen-only grading (no SubCPMK defined)     --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        @else

        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-4 text-sm text-amber-800 flex items-center gap-2">
            <span>ℹ️</span>
            <span>Sub-CPMK belum didefinisikan untuk MK ini. Input nilai per komponen langsung.</span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-600 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold sticky left-0 bg-brand-600 z-10">NIM</th>
                            <th class="px-4 py-3 text-left font-semibold">Nama</th>
                            <th class="px-4 py-3 text-center font-semibold">Tugas<br><span class="text-xs font-normal opacity-80">({{ $bobot['tugas'] }}%)</span></th>
                            <th class="px-4 py-3 text-center font-semibold">UTS<br><span class="text-xs font-normal opacity-80">({{ $bobot['uts'] }}%)</span></th>
                            <th class="px-4 py-3 text-center font-semibold">UAS<br><span class="text-xs font-normal opacity-80">({{ $bobot['uas'] }}%)</span></th>
                            <th class="px-4 py-3 text-center font-semibold">Partisipatif<br><span class="text-xs font-normal opacity-80">({{ $bobot['partisipatif'] }}%)</span></th>
                            <th class="px-4 py-3 text-center font-semibold">Proyek<br><span class="text-xs font-normal opacity-80">({{ $bobot['proyek'] }}%)</span></th>
                            <th class="px-4 py-3 text-center font-semibold bg-brand-700">Nilai Akhir</th>
                            <th class="px-4 py-3 text-center font-semibold bg-brand-700">Grade</th>
                            <th class="px-4 py-3 text-center font-semibold bg-brand-700">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="nilaiTable" data-bobot='{{ json_encode($bobot) }}'>
                        @foreach($enrollments as $enr)
                        @php $nm = $nilaiMap[$enr->mahasiswa_id] ?? null; @endphp
                        <tr class="hover:bg-gray-50 nilai-row" data-mhs="{{ $enr->mahasiswa_id }}">
                            <td class="px-4 py-2 font-mono text-xs text-gray-600 sticky left-0 bg-white z-10">
                                {{ $enr->nim }}
                                @if($enr->is_pjmk)
                                <span class="ml-1 text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">PJMK</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $enr->nama }}</td>
                            @foreach(['tugas','uts','uas','partisipatif','proyek'] as $komponen)
                            <td class="px-2 py-2">
                                <input type="number"
                                    name="nilai[{{ $enr->mahasiswa_id }}][{{ $komponen }}]"
                                    value="{{ $nm ? $nm->{'nilai_'.$komponen} : 0 }}"
                                    min="0" max="100" step="0.5"
                                    class="nilai-input w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-center text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500"
                                    data-komponen="{{ $komponen }}">
                            </td>
                            @endforeach
                            <td class="px-3 py-2 text-center">
                                <span class="nilai-akhir font-bold text-base {{ $nm && $nm->nilai_akhir >= 70 ? 'text-green-600' : ($nm && $nm->nilai_akhir >= 56 ? 'text-amber-600' : 'text-red-500') }}">
                                    {{ $nm ? number_format($nm->nilai_akhir,2) : '—' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="grade-badge inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-sm
                                    {{ $nm ? match(substr($nm->grade,0,1)){'A'=>'bg-green-100 text-green-700','B'=>'bg-blue-100 text-blue-700','C'=>'bg-amber-100 text-amber-700','D'=>'bg-orange-100 text-orange-700',default=>'bg-red-100 text-red-700'} : 'bg-gray-100 text-gray-400' }}">
                                    {{ $nm ? $nm->grade : '—' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($nm)
                                @if($nm->lulus)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Lulus</span>
                                @else
                                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Tidak Lulus</span>
                                @endif
                                @else
                                <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Action buttons --}}
        <div class="flex gap-3 flex-wrap mt-4">
            <button type="submit"
                class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow transition">
                💾 Simpan Nilai
            </button>
            <button type="button" id="btnSyncObe"
                onclick="syncNilaiObe({{ $mk->id }})"
                class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl shadow transition flex items-center gap-2">
                🔄 Sinkronkan Nilai OBE
            </button>
            <button type="button" id="btnHitungObe"
                onclick="hitungObe({{ $mk->id }})"
                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow transition flex items-center gap-2">
                ⚡ Hitung CPMK & CPL
            </button>
            <a href="{{ route('obe.cpmk-evaluasi') }}?mk_id={{ $mk->id }}&ta={{ urlencode($ta) }}"
                class="px-5 py-2.5 bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium rounded-xl transition">
                📊 Lihat Capaian CPMK
            </a>
            <a href="{{ route('nilai-mahasiswa.index', ['ta' => $ta]) }}"
                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                Kembali
            </a>
        </div>
    </form>
    @endif

</div>

<script>
    // ── SubCPMK mode JS ────────────────────────────────────────────────────────
    (function() {
        const blocks = document.querySelectorAll('.student-block');
        if (!blocks.length) return;

        function gradeMap(n) {
            return n >= 80 ? 'A' : n >= 70 ? 'B+' : n >= 65 ? 'B' : n >= 60 ? 'C+' : n >= 56 ? 'C' : n >= 40 ? 'D' : 'E';
        }

        function gradeClass(g) {
            const c = {
                A: 'bg-green-100 text-green-700',
                'B+': 'bg-blue-100 text-blue-700',
                B: 'bg-blue-100 text-blue-700',
                'C+': 'bg-amber-100 text-amber-700',
                C: 'bg-amber-100 text-amber-700',
                D: 'bg-orange-100 text-orange-700',
                E: 'bg-red-100 text-red-700'
            };
            return c[g] || 'bg-gray-100 text-gray-400';
        }

        function calcBlock(block) {
            // Group rows by cpmk_id
            const cpmkMap = {}; // cpmk_id → [nilai_sub, ...]

            block.querySelectorAll('.subcpmk-row').forEach(row => {
                const cpmkId = row.dataset.cpmk;
                // Each row carries its own bobot (inherited from parent CPMK's bobot_penilaian)
                const rowBobot = JSON.parse(row.dataset.bobot);
                const rowTotal = Math.max(1, Object.values(rowBobot).reduce((s, v) => s + v, 0));

                let w = 0;
                row.querySelectorAll('.sub-inp').forEach(inp => {
                    w += (parseFloat(inp.value) || 0) * (rowBobot[inp.dataset.komponen] || 0);
                });
                const nilaiSub = Math.round(w / rowTotal * 100) / 100;

                const subEl = row.querySelector('.sub-nilai');
                if (subEl) {
                    subEl.textContent = nilaiSub.toFixed(2);
                    subEl.className = `sub-nilai font-semibold ${nilaiSub >= 56 ? 'text-indigo-700' : 'text-red-500'}`;
                }

                if (!cpmkMap[cpmkId]) cpmkMap[cpmkId] = [];
                cpmkMap[cpmkId].push(nilaiSub);
            });

            // Aggregate CPMK averages
            const cpmkAvgs = [];
            Object.entries(cpmkMap).forEach(([cpmkId, vals]) => {
                const avg = Math.round(vals.reduce((s, v) => s + v, 0) / vals.length * 100) / 100;
                cpmkAvgs.push(avg);

                // Find CPMK cell for this cpmk
                const cpmkCell = block.querySelector(`.subcpmk-row[data-cpmk="${cpmkId}"] td[rowspan]`);
                if (cpmkCell) {
                    const avgEl = cpmkCell.querySelector('.cpmk-avg');
                    const barEl = cpmkCell.querySelector('.cpmk-bar');
                    if (avgEl) avgEl.textContent = avg.toFixed(2);
                    if (barEl) barEl.style.width = Math.min(100, avg) + '%';
                }

                // Also update the cpmk-nilai-cell (first row of group)
                const firstRow = block.querySelector(`.subcpmk-row[data-cpmk="${cpmkId}"]`);
                if (firstRow) {
                    const cpmkNilaEl = firstRow.querySelector('.cpmk-nilai-cell');
                    if (cpmkNilaEl) cpmkNilaEl.textContent = avg.toFixed(2);
                }
            });

            // Nilai akhir = avg of CPMK averages
            if (cpmkAvgs.length > 0) {
                const akhir = Math.round(cpmkAvgs.reduce((s, v) => s + v, 0) / cpmkAvgs.length * 100) / 100;
                const grade = gradeMap(akhir);

                const naEl = block.querySelector('.student-nilai-akhir');
                const grEl = block.querySelector('.student-grade-badge');
                if (naEl) {
                    naEl.textContent = akhir.toFixed(2);
                    naEl.className = `student-nilai-akhir font-bold text-lg ${akhir >= 70 ? 'text-green-600' : akhir >= 56 ? 'text-amber-600' : 'text-red-500'}`;
                }
                if (grEl) {
                    grEl.textContent = grade;
                    grEl.className = `student-grade-badge inline-flex items-center justify-center w-9 h-9 rounded-xl font-bold text-sm ${gradeClass(grade)}`;
                }
            }
        }

        blocks.forEach(block => {
            calcBlock(block); // initial calc
            block.querySelectorAll('.sub-inp').forEach(inp => {
                inp.addEventListener('input', () => calcBlock(block));
            });
        });
    })();

    // ── Komponen mode JS ──────────────────────────────────────────────────────
    (function() {
        const table = document.getElementById('nilaiTable');
        if (!table) return;
        const bobot = JSON.parse(table.dataset.bobot);
        const total = Math.max(1, Object.values(bobot).reduce((s, v) => s + v, 0));

        const gradeMap = n => n >= 80 ? 'A' : n >= 70 ? 'B+' : n >= 65 ? 'B' : n >= 60 ? 'C+' : n >= 56 ? 'C' : n >= 40 ? 'D' : 'E';
        const gradeClass = g => ({
            A: 'bg-green-100 text-green-700',
            'B+': 'bg-blue-100 text-blue-700',
            B: 'bg-blue-100 text-blue-700',
            'C+': 'bg-amber-100 text-amber-700',
            C: 'bg-amber-100 text-amber-700',
            D: 'bg-orange-100 text-orange-700',
            E: 'bg-red-100 text-red-700'
        } [g] || 'bg-gray-100 text-gray-400');

        function calcRow(row) {
            let w = 0;
            row.querySelectorAll('.nilai-input').forEach(inp => {
                w += (parseFloat(inp.value) || 0) * (bobot[inp.dataset.komponen] || 0);
            });
            const akhir = Math.round(w / total * 100) / 100;
            const grade = gradeMap(akhir);

            const naEl = row.querySelector('.nilai-akhir');
            const grEl = row.querySelector('.grade-badge');
            if (naEl) {
                naEl.textContent = akhir.toFixed(2);
                naEl.className = `nilai-akhir font-bold text-base ${akhir>=70?'text-green-600':akhir>=56?'text-amber-600':'text-red-500'}`;
            }
            if (grEl) {
                grEl.textContent = grade;
                grEl.className = `grade-badge inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-sm ${gradeClass(grade)}`;
            }
        }

        table.querySelectorAll('.nilai-row').forEach(row => {
            calcRow(row);
            row.querySelectorAll('.nilai-input').forEach(inp => inp.addEventListener('input', () => calcRow(row)));
        });
    })();
</script>
<script>
    function hitungObe(mkId) {
        const ta = '{{ $ta }}';
        const btn = document.getElementById('btnHitungObe');
        if (!confirm('Hitung CPMK & CPL achievement untuk MK ini (TA ' + ta + ')?')) return;
        btn.disabled = true;
        btn.innerHTML = '⏳ Menghitung...';
        fetch(`/api/obe/hitung/${mkId}?ta=${encodeURIComponent(ta)}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(r => r.json()).then(d => {
            if (d.status === 'ok') {
                alert(`✅ Selesai!\n${d.cpmk_records} CPMK records\n${d.cpl_records} CPL records\nMahasiswa: ${d.mahasiswa_count}`);
            } else {
                alert('Error: ' + d.message);
            }
            btn.disabled = false;
            btn.innerHTML = '⚡ Hitung CPMK & CPL';
        }).catch(e => {
            alert('Error: ' + e);
            btn.disabled = false;
            btn.innerHTML = '⚡ Hitung CPMK & CPL';
        });
    }
</script>
<script>
    function syncNilaiObe(mkId) {
        const ta  = '{{ $ta }}';
        const btn = document.getElementById('btnSyncObe');
        if (!confirm('Sinkronkan nilai dari nilai_sub_cpmk → CPMK → CPL untuk MK ini (TA ' + ta + ')?\n\nProses ini akan:\n1. Hitung ulang nilai_subcpmk dari komponen\n2. Agregasi ke nilai_mahasiswa\n3. Hitung CPMK & CPL achievement')) return;
        btn.disabled = true;
        btn.innerHTML = '⏳ Menyinkronkan...';
        fetch(`/api/obe/sync-obe/${mkId}?ta=${encodeURIComponent(ta)}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(r => r.json()).then(d => {
            if (d.status === 'ok') {
                alert(
                    `✅ Sinkronisasi Selesai!\n\n` +
                    `📚 MK: ${d.mk}\n` +
                    `👤 Mahasiswa ter-sync: ${d.synced_mahasiswa ?? d.mahasiswa_count}\n` +
                    `📊 CPMK records: ${d.cpmk_records}\n` +
                    `🎯 CPL records: ${d.cpl_records}\n\n` +
                    `Sumber: nilai_sub_cpmk`
                );
                location.reload();
            } else {
                alert('❌ Error: ' + (d.message ?? 'Terjadi kesalahan'));
            }
            btn.disabled = false;
            btn.innerHTML = '🔄 Sinkronkan Nilai OBE';
        }).catch(e => {
            alert('❌ Network error: ' + e);
            btn.disabled = false;
            btn.innerHTML = '🔄 Sinkronkan Nilai OBE';
        });
    }
</script>
@endsection