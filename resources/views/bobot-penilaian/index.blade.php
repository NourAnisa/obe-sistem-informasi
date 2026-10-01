@extends('layouts.dashboard')
@section('title','Bobot Penilaian')
@section('breadcrumb', 'Bobot Penilaian')
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Bobot Penilaian</h1>
        <p class="text-gray-500 mt-1">Pilih mata kuliah untuk mengatur bobot penilaian per SubCPMK</p>
    </div>

    {{-- Quick-navigate: select MK → go to SubCPMK bobot page --}}
    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Mata Kuliah untuk Atur Bobot SubCPMK</label>
        <div class="flex gap-3 items-center">
            <select id="mk-select" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-unism-primary">
                <option value="">-- Pilih Mata Kuliah --</option>
                @foreach($mataKuliahs->groupBy('semester') as $sem => $mks)
                <optgroup label="Semester {{ $sem }}">
                    @foreach($mks as $m)
                    <option value="{{ $m->id }}" data-kode="{{ $m->kode }}"
                        {{ (isset($selectedMk) && $selectedMk?->kode == $m->kode) ? 'selected' : '' }}>
                        [{{ $m->kode }}] {{ $m->nama }}
                    </option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            <button id="btn-atur" onclick="goToBobot()"
                class="px-6 py-2 bg-unism-primary text-white rounded-lg text-sm font-medium hover:bg-unism-secondary transition">
                ⚙ Atur Bobot SubCPMK
            </button>
            <button onclick="goToView()"
                class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition border border-gray-300">
                👁 Lihat Bobot CPMK
            </button>
        </div>
    </div>
    <script>
        function goToBobot() {
            const id = document.getElementById('mk-select').value;
            if (!id) {
                alert('Pilih mata kuliah terlebih dahulu');
                return;
            }
            window.location = '/bobot-penilaian/' + id;
        }

        function goToView() {
            const sel = document.getElementById('mk-select');
            const kode = sel.options[sel.selectedIndex]?.dataset?.kode;
            if (!kode) {
                alert('Pilih mata kuliah terlebih dahulu');
                return;
            }
            window.location = '{{ route("bobot-penilaian.index") }}?mk=' + kode;
        }
    </script>

    {{-- Legacy view: show CPMK bobot table when ?mk= param given --}}
    @php $showLegacy = request()->filled('mk') && isset($selectedMk); @endphp
    @if($showLegacy)
    {{-- keep existing form hidden but functional --}}
    <form method="GET" action="{{ route('bobot-penilaian.index') }}" class="hidden">
        <input name="mk" value="{{ request('mk') }}">
    </form>
    @endif

    @if($selectedMk)
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-1">{{ $selectedMk->nama }}</h2>
        <p class="text-sm text-gray-500 mb-4">{{ $selectedMk->kode }} · {{ $selectedMk->sks }} SKS · Semester {{ $selectedMk->semester }}</p>

        {{-- Export / Import buttons --}}
        <div class="flex gap-2 mb-4 flex-wrap">
            <a href="{{ route('obe.export-nilai-cpmk', ['mkId' => $selectedMk->id]) }}"
               class="inline-flex items-center gap-1 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
               ⬇ Export Excel
            </a>
            <form method="POST" action="{{ route('obe.import-nilai-cpmk') }}" enctype="multipart/form-data" class="inline-flex items-center gap-2">
                @csrf
                <label class="cursor-pointer inline-flex items-center gap-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    ⬆ Import Excel
                    <input type="file" name="file" accept=".xlsx,.xls" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            @if(session('success'))
            <span class="inline-flex items-center px-3 py-2 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">✅ {{ session('success') }}</span>
            @endif
            @if(session('warning'))
            <span class="inline-flex items-center px-3 py-2 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-sm">⚠️ {{ session('warning') }}</span>
            @endif
        </div>

        @if($bobots->isEmpty())
        <div class="text-center py-10 text-gray-400">
            <p class="text-5xl mb-3">📊</p>
            <p>Belum ada data bobot penilaian untuk mata kuliah ini.</p>
        </div>
        @else
        @php
        $grandTugas = $bobots->sum('bobot_tugas');
        $grandUts = $bobots->sum('bobot_uts');
        $grandUas = $bobots->sum('bobot_uas');
        $grandPart = $bobots->sum('bobot_partisipatif');
        $grandProy = $bobots->sum('bobot_proyek');
        $grandTotal = $grandTugas + $grandUts + $grandUas + $grandPart + $grandProy;
        $isValidTotal = $grandTotal === 100;
        @endphp
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full text-sm border-collapse">
                <thead class="bg-unism-primary text-white">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold border border-unism-secondary">CPL</th>
                        <th class="px-4 py-2 text-left font-semibold border border-unism-secondary">CPMK</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">MBKM</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">Quiz</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">Tugas</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">UTS</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">UAS</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">Aktifitas Partisipatif</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">Hasil Proyek</th>
                        <th class="px-4 py-2 text-center font-semibold border border-unism-secondary">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bobots as $bp)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-4 py-2 border border-gray-200 text-xs font-medium text-purple-700">
                            {{ $bp->cpmk?->cpl?->kode ?? '-' }}
                        </td>
                        <td class="px-4 py-2 border border-gray-200 font-medium text-unism-primary">{{ $bp->cpmk?->kode }}</td>
                        <td class="px-4 py-2 border border-gray-200 text-center text-gray-400">0</td>
                        <td class="px-4 py-2 border border-gray-200 text-center text-gray-400">0</td>
                        <td class="px-4 py-2 border border-gray-200 text-center">{{ $bp->bobot_tugas ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-200 text-center">{{ $bp->bobot_uts ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-200 text-center">{{ $bp->bobot_uas ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-200 text-center">{{ $bp->bobot_partisipatif ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-200 text-center">{{ $bp->bobot_proyek ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-200 text-center font-bold text-gray-700">{{ $bp->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="{{ $isValidTotal ? 'bg-green-50' : 'bg-red-50' }}">
                        <td class="px-4 py-2 border border-gray-300 font-bold text-gray-800" colspan="2">Total MK</td>
                        <td class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-400" colspan="2">-</td>
                        <td class="px-4 py-2 border border-gray-300 text-center font-bold">{{ $grandTugas ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300 text-center font-bold">{{ $grandUts ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300 text-center font-bold">{{ $grandUas ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300 text-center font-bold">{{ $grandPart ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300 text-center font-bold">{{ $grandProy ?: '-' }}</td>
                        <td class="px-4 py-2 border border-gray-300 text-center font-bold {{ $isValidTotal ? 'text-green-700' : 'text-red-700' }}">
                            {{ $grandTotal }}%
                            @if(!$isValidTotal)<span class="ml-1">⚠️</span>@endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        {{-- Progress bars --}}
        <h3 class="font-semibold text-gray-700 mb-3">Distribusi Bobot per Teknik Penilaian</h3>
        <div class="space-y-3">
            @php
            $teknikTotals = [
            'Tugas' => $grandTugas,
            'UTS' => $grandUts,
            'UAS' => $grandUas,
            'Aktifitas Partisipatif' => $grandPart,
            'Hasil Proyek' => $grandProy,
            ];
            @endphp
            @foreach($teknikTotals as $nama => $val)
            @if($val > 0)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-gray-700">{{ $nama }}</span>
                    <span class="text-unism-primary font-semibold">{{ $val }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-unism-primary h-2 rounded-full transition-all" style="width: {{ $grandTotal > 0 ? round($val/$grandTotal*100) : 0 }}%"></div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>
    @endif
</div>
@endsection