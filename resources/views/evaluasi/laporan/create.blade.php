@extends('layouts.dashboard')
@section('title', isset($laporan) ? 'Edit Laporan Evaluasi' : 'Buat Laporan Evaluasi')
@section('breadcrumb', isset($laporan) ? 'Edit Laporan Evaluasi' : 'Buat Laporan Evaluasi')

@section('content')
@php
$isEdit = isset($laporan);
$action = $isEdit ? route('laporan-evaluasi.update', $laporan->id) : route('laporan-evaluasi.store');
$method = $isEdit ? 'PUT' : 'POST';

// Pre-fill komponen default rows
$defaultKomponen = ['Tugas', 'UTS', 'UAS', 'Partisipatif', 'Proyek'];
$existingKomponen = $isEdit ? $laporan->komponenNilai->toArray() : [];
$existingCpmks = $isEdit ? $laporan->cpmks->toArray() : [];
$existingCpls = $isEdit ? $laporan->cpls->toArray() : [];
$existingDist = $isEdit ? ($laporan->distribusiNilai?->toArray() ?? []) : [];
$existingHambatans = $isEdit ? $laporan->hambatans->toArray() : [];
$existingTindaks = $isEdit ? $laporan->tindakLanjuts->toArray() : [];
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('laporan-evaluasi.index') }}"
            class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">←</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">
                {{ $isEdit ? 'Edit Laporan Evaluasi' : 'Buat Laporan Evaluasi Baru' }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Isi semua tab dan klik Simpan di tab terakhir</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        <strong>Terdapat kesalahan:</strong>
        <ul class="mt-1 list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $action }}"
        x-data="{
            tab: 1,
            bobotData: {{ json_encode($bobotPerMk ?? []) }},
            cpmkData: {{ json_encode($cpmkPerMk ?? []) }},
            cplData: {{ json_encode($cplPerMk ?? []) }},
            komponen: {{ json_encode(count($existingKomponen) ? $existingKomponen : array_map(fn($k) => ['komponen' => $k, 'bobot_persen' => 0, 'rata_rata' => 0, 'nilai_min' => 0, 'nilai_max' => 0, 'std_deviasi' => 0], $defaultKomponen)) }},
            cpmkRows: {{ json_encode(count($existingCpmks) ? $existingCpmks : [['kode_cpmk' => '', 'deskripsi_cpmk' => '', 'rata_rata_nilai' => 0, 'persen_lulus' => 0, 'target_capaian' => 70, 'keterangan' => '']]) }},
            cplRows: {{ json_encode(count($existingCpls) ? $existingCpls : [['kode_cpl' => '', 'nilai_cpl' => 0, 'target_cpl' => 70, 'keterangan' => '']]) }},
            hambatanRows: {{ json_encode(count($existingHambatans) ? $existingHambatans : [['jenis_hambatan' => 'materi', 'deskripsi' => '', 'solusi_usulan' => '']]) }},
            tindakRows: {{ json_encode(count($existingTindaks) ? $existingTindaks : [['aspek' => 'materi', 'permasalahan' => '', 'rekomendasi' => '', 'penanggung_jawab' => '', 'target_semester' => '']]) }},
            dist: {{ json_encode(count($existingDist) ? $existingDist : ['jumlah_lulus' => 0, 'jumlah_tidak_lulus' => 0, 'persen_lulus' => 0, 'rata_rata_final' => 0, 'jml_a' => 0, 'jml_b' => 0, 'jml_c' => 0, 'jml_d' => 0, 'jml_e' => 0]) }},
            loadMkData(mkId) {
                if (!mkId) return;
                const b = this.bobotData[mkId];
                if (b) {
                    this.komponen = [
                        {komponen:'Tugas',        bobot_persen: b.tugas,        rata_rata:0, nilai_min:0, nilai_max:0, std_deviasi:0},
                        {komponen:'UTS',          bobot_persen: b.uts,          rata_rata:0, nilai_min:0, nilai_max:0, std_deviasi:0},
                        {komponen:'UAS',          bobot_persen: b.uas,          rata_rata:0, nilai_min:0, nilai_max:0, std_deviasi:0},
                        {komponen:'Partisipatif', bobot_persen: b.partisipatif, rata_rata:0, nilai_min:0, nilai_max:0, std_deviasi:0},
                        {komponen:'Proyek',       bobot_persen: b.proyek,       rata_rata:0, nilai_min:0, nilai_max:0, std_deviasi:0},
                    ];
                }
                const c = this.cpmkData[mkId];
                if (c && c.length) { this.cpmkRows = c; }
                const p = this.cplData[mkId];
                if (p && p.length) { this.cplRows = p; }
            },
            get totalBobot() {
                return this.komponen.reduce((s, r) => s + Number(r.bobot_persen || 0), 0);
            }
          }">
        @csrf
        @if($method === 'PUT') @method('PUT') @endif

        {{-- Tab navigation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-1">
            <div class="flex overflow-x-auto border-b border-gray-100">
                @php
                $tabs = ['Identitas', 'Komponen Nilai', 'CPMK', 'CPL', 'Distribusi', 'Hambatan', 'Tindak Lanjut'];
                @endphp
                @foreach($tabs as $i => $label)
                <button type="button" @click="tab = {{ $i + 1 }}"
                    :class="tab === {{ $i + 1 }} ? 'border-b-2 border-brand-600 text-brand-700 font-semibold bg-brand-50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-4 py-3 text-sm whitespace-nowrap transition flex-shrink-0">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs mr-1"
                        :class="tab === {{ $i + 1 }} ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-600'">{{ $i + 1 }}</span>
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <div class="p-6">

                {{-- ═══ TAB 1: IDENTITAS ═══ --}}
                <div x-show="tab === 1" x-cloak>
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Identitas Mata Kuliah</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Kuliah <span class="text-red-500">*</span></label>
                            <select name="mata_kuliah_id" id="mkSelect" required
                                @change="loadMkData($event.target.value)"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                <option value="">-- Pilih Mata Kuliah --</option>
                                @foreach($mataKuliahs->groupBy('semester') as $sem => $mks)
                                <optgroup label="Semester {{ $sem }}">
                                    @foreach($mks as $mk)
                                    <option value="{{ $mk->id }}"
                                        {{ (isset($laporan) && $laporan->mata_kuliah_id == $mk->id) ? 'selected' : (old('mata_kuliah_id') == $mk->id ? 'selected' : '') }}>
                                        [{{ $mk->kode }}] {{ $mk->nama }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>

                            {{-- Kalkulasi Otomatis button --}}
                            <div class="mt-2 flex items-center gap-2"
                                x-data="{
                                    loading: false, msg: '', msgType: '',
                                    async kalkulasi() {
                                        const mkId = document.getElementById('mkSelect').value;
                                        const ta   = document.querySelector('[name=tahun_akademik]').value || '{{ config('obe.tahun_akademik') }}';
                                        if (!mkId) { this.msg='Pilih mata kuliah terlebih dahulu.'; this.msgType='warn'; return; }
                                        this.loading = true; this.msg = '';
                                        try {
                                            const r = await fetch(`/api/nilai-mahasiswa/${mkId}/kalkulasi?ta=${encodeURIComponent(ta)}`, {headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}});
                                            if (!r.ok) { const e=await r.json(); this.msg=e.error||'Gagal mengambil data.'; this.msgType='error'; return; }
                                            const d = await r.json();
                                            // Populate komponen tab
                                            $data.komponen = d.komponen;
                                            // Populate cpmk tab
                                            if (d.cpmk && d.cpmk.length) $data.cpmkRows = d.cpmk;
                                            // Populate cpl tab
                                            if (d.cpl && d.cpl.length) $data.cplRows = d.cpl;
                                            // Populate distribusi tab
                                            if (d.distribusi) $data.dist = d.distribusi;
                                            // Set jumlah mahasiswa & dosen pjmk
                                            if (d.jumlah_mahasiswa) document.querySelector('[name=jumlah_mahasiswa]').value = d.jumlah_mahasiswa;
                                            if (d.mk && d.mk.pjmk) document.querySelector('[name=dosen_pjmk]').value = d.mk.pjmk;
                                            this.msg = '✅ Data berhasil dikalkulasi dari ' + d.jumlah_mahasiswa + ' mahasiswa. Periksa setiap tab.';
                                            this.msgType = 'success';
                                        } catch(e) {
                                            this.msg = 'Error: ' + e.message; this.msgType='error';
                                        } finally { this.loading = false; }
                                    }
                                }">
                                <button type="button" @click="kalkulasi()"
                                    :disabled="loading"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 text-white text-sm font-semibold rounded-xl transition">
                                    <span x-show="!loading">⚡ Kalkulasi Otomatis dari Nilai</span>
                                    <span x-show="loading" class="flex items-center gap-1.5">
                                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        Menghitung...
                                    </span>
                                </button>
                                <a href="{{ route('nilai-mahasiswa.index') }}" target="_blank"
                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm rounded-xl transition">
                                    📝 Input Nilai
                                </a>
                                <div x-show="msg" x-text="msg"
                                    :class="msgType==='success'?'text-green-700 bg-green-50 border border-green-200':'msgType==='warn'?'text-amber-700 bg-amber-50 border border-amber-200':'text-red-700 bg-red-50 border border-red-200'"
                                    class="flex-1 text-xs px-3 py-1.5 rounded-lg"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
                            <select name="semester" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                <option value="">-- Pilih --</option>
                                <option value="Ganjil" {{ (isset($laporan) && $laporan->semester === 'Ganjil') ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ (isset($laporan) && $laporan->semester === 'Genap')  ? 'selected' : '' }}>Genap</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
                            <input type="text" name="tahun_akademik" placeholder="e.g. 2025/2026" required
                                value="{{ $laporan->tahun_akademik ?? old('tahun_akademik', $taAktif ?? '') }}"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                            <input type="text" name="kelas" placeholder="A / B / Reguler"
                                value="{{ $laporan->kelas ?? old('kelas') }}"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Mahasiswa</label>
                            <input type="number" name="jumlah_mahasiswa" min="0"
                                value="{{ $laporan->jumlah_mahasiswa ?? old('jumlah_mahasiswa', 0) }}"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosen PJMK</label>
                            <input type="text" name="dosen_pjmk" placeholder="Nama dosen penanggung jawab"
                                value="{{ $laporan->dosen_pjmk ?? old('dosen_pjmk') }}"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                <option value="draft" {{ (isset($laporan) && $laporan->status === 'draft') ? 'selected' : '' }}>Draft</option>
                                <option value="final" {{ (isset($laporan) && $laporan->status === 'final') ? 'selected' : '' }}>Final</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Umum</label>
                            <textarea name="catatan_umum" rows="3" placeholder="Catatan atau keterangan umum tentang pelaksanaan perkuliahan..."
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none">{{ $laporan->catatan_umum ?? old('catatan_umum') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ═══ TAB 2: KOMPONEN NILAI ═══ --}}
                <div x-show="tab === 2" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-700">Rekapitulasi Komponen Nilai</h2>
                        <p class="text-xs text-gray-500">
                            Bobot otomatis dari RPS ·
                            Total bobot: <span class="font-bold" :class="totalBobot===100?'text-green-600':'text-red-500'" x-text="totalBobot + '%'"></span>
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-100">
                            <thead class="bg-brand-50">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-semibold text-brand-800">Komponen</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Bobot (%)</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Rata-rata</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Nilai Min</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Nilai Maks</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Std. Deviasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="(row, idx) in komponen" :key="idx">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'komponen_nilai[' + idx + '][komponen]'" x-model="row.komponen"
                                                class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="1" min="0" max="100"
                                                :name="'komponen_nilai[' + idx + '][bobot_persen]'"
                                                x-model="row.bobot_persen"
                                                class="w-20 border border-brand-200 bg-brand-50 rounded-lg px-2 py-1.5 text-sm text-center font-semibold text-brand-700 focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <template x-for="field in ['rata_rata','nilai_min','nilai_max','std_deviasi']" :key="field">
                                            <td class="px-3 py-2">
                                                <input type="number" step="0.01" min="0" max="100"
                                                    :name="'komponen_nilai[' + idx + '][' + field + ']'"
                                                    x-model="row[field]"
                                                    class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                                <tr>
                                    <td class="px-3 py-2 font-semibold text-gray-700">Total</td>
                                    <td class="px-3 py-2 text-center font-bold"
                                        :class="totalBobot===100?'text-green-600':'text-red-500'"
                                        x-text="totalBobot + '%'"></td>
                                    <td class="px-3 py-2 text-center text-xs text-gray-400" colspan="4">
                                        Nilai Akhir = Σ (Bobot × Rata-rata) / 100
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">💡 Klik "⚡ Kalkulasi Otomatis" di Tab 1 untuk mengisi otomatis dari data nilai mahasiswa</p>
                </div>

                {{-- ═══ TAB 3: CPMK ═══ --}}
                <div x-show="tab === 3" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-700">Rekapitulasi Ketercapaian CPMK</h2>
                        <button type="button" @click="cpmkRows.push({kode_cpmk:'',deskripsi_cpmk:'',rata_rata_nilai:0,persen_lulus:0,target_capaian:70,keterangan:''})"
                            class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-sm font-medium rounded-xl transition">
                            ＋ Tambah CPMK
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-100">
                            <thead class="bg-brand-50">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-semibold text-brand-800">Kode CPMK</th>
                                    <th class="px-3 py-2.5 text-left font-semibold text-brand-800">Deskripsi</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Rata-rata</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">% Lulus</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Target (%)</th>
                                    <th class="px-3 py-2.5 text-left font-semibold text-brand-800">Keterangan</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="(row, idx) in cpmkRows" :key="idx">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'eval_cpmk[' + idx + '][kode_cpmk]'" x-model="row.kode_cpmk" placeholder="CPMK-01"
                                                class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'eval_cpmk[' + idx + '][deskripsi_cpmk]'" x-model="row.deskripsi_cpmk" placeholder="Deskripsi CPMK"
                                                class="w-full min-w-[160px] border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" :name="'eval_cpmk[' + idx + '][rata_rata_nilai]'" x-model="row.rata_rata_nilai"
                                                class="w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" max="100" :name="'eval_cpmk[' + idx + '][persen_lulus]'" x-model="row.persen_lulus"
                                                class="w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" max="100" :name="'eval_cpmk[' + idx + '][target_capaian]'" x-model="row.target_capaian"
                                                class="w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'eval_cpmk[' + idx + '][keterangan]'" x-model="row.keterangan" placeholder="Opsional"
                                                class="w-full min-w-[120px] border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="cpmkRows.splice(idx,1)" x-show="cpmkRows.length > 1"
                                                class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ═══ TAB 4: CPL ═══ --}}
                <div x-show="tab === 4" x-cloak>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-700">Ketercapaian CPL</h2>
                        <button type="button" @click="cplRows.push({kode_cpl:'',nilai_cpl:0,target_cpl:70,keterangan:''})"
                            class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-sm font-medium rounded-xl transition">
                            ＋ Tambah CPL
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm divide-y divide-gray-100">
                            <thead class="bg-brand-50">
                                <tr>
                                    <th class="px-3 py-2.5 text-left font-semibold text-brand-800">Kode CPL</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Nilai CPL</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Target (%)</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Gap (auto)</th>
                                    <th class="px-3 py-2.5 text-left font-semibold text-brand-800">Keterangan</th>
                                    <th class="px-3 py-2.5 text-center font-semibold text-brand-800">Hapus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="(row, idx) in cplRows" :key="idx">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'eval_cpl[' + idx + '][kode_cpl]'" x-model="row.kode_cpl" placeholder="CPL-01"
                                                class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" :name="'eval_cpl[' + idx + '][nilai_cpl]'" x-model="row.nilai_cpl"
                                                class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.01" min="0" max="100" :name="'eval_cpl[' + idx + '][target_cpl]'" x-model="row.target_cpl"
                                                class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="text-sm font-mono" :class="(parseFloat(row.nilai_cpl) - parseFloat(row.target_cpl)) >= 0 ? 'text-green-600' : 'text-red-600'"
                                                x-text="(parseFloat(row.nilai_cpl || 0) - parseFloat(row.target_cpl || 0)).toFixed(2)"></span>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" :name="'eval_cpl[' + idx + '][keterangan]'" x-model="row.keterangan" placeholder="Opsional"
                                                class="w-full min-w-[140px] border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-brand-500">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" @click="cplRows.splice(idx,1)" x-show="cplRows.length > 1"
                                                class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ═══ TAB 5: DISTRIBUSI ═══ --}}
                <div x-show="tab === 5" x-cloak>
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Distribusi Nilai & Kelulusan</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @php
                        $distFields = [
                        ['jumlah_lulus', 'Jumlah Lulus', 'number'],
                        ['jumlah_tidak_lulus', 'Jumlah Tidak Lulus', 'number'],
                        ['rata_rata_final', 'Rata-rata Final', 'number'],
                        ['jml_a', 'Nilai A (≥80)', 'number'],
                        ['jml_b', 'Nilai B (70-79)', 'number'],
                        ['jml_c', 'Nilai C (60-69)', 'number'],
                        ['jml_d', 'Nilai D (50-59)', 'number'],
                        ['jml_e', 'Nilai E (<50)', 'number' ],
                            ];
                            @endphp
                            @foreach($distFields as [$field, $label, $type])
                            <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                            <input type="{{ $type }}" name="distribusi[{{ $field }}]" step="0.01" min="0"
                                value="{{ $existingDist[$field] ?? 0 }}"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    @endforeach
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">% Lulus <span class="text-gray-400 text-xs">(dihitung otomatis)</span></label>
                        <div class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50 text-gray-500">
                            Dihitung saat simpan
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ TAB 6: HAMBATAN ═══ --}}
            <div x-show="tab === 6" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Hambatan & Permasalahan</h2>
                    <button type="button" @click="hambatanRows.push({jenis_hambatan:'materi',deskripsi:'',solusi_usulan:''})"
                        class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-sm font-medium rounded-xl transition">
                        ＋ Tambah Hambatan
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(row, idx) in hambatanRows" :key="idx">
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 relative">
                            <div class="absolute top-3 right-3">
                                <button type="button" @click="hambatanRows.splice(idx,1)" x-show="hambatanRows.length > 1"
                                    class="text-red-400 hover:text-red-600 text-xl leading-none">×</button>
                            </div>
                            <div class="text-xs font-semibold text-gray-400 mb-3">Hambatan #<span x-text="idx + 1"></span></div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Hambatan</label>
                                    <select :name="'hambatan[' + idx + '][jenis_hambatan]'" x-model="row.jenis_hambatan"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                                        <option value="materi">Materi</option>
                                        <option value="metode">Metode</option>
                                        <option value="sarana">Sarana/Prasarana</option>
                                        <option value="mahasiswa">Mahasiswa</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi Hambatan</label>
                                    <textarea :name="'hambatan[' + idx + '][deskripsi]'" x-model="row.deskripsi" rows="2"
                                        placeholder="Jelaskan hambatan yang ditemui..."
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500 resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Solusi / Usulan</label>
                                    <textarea :name="'hambatan[' + idx + '][solusi_usulan]'" x-model="row.solusi_usulan" rows="2"
                                        placeholder="Solusi atau usulan penanganan..."
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500 resize-none"></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ═══ TAB 7: TINDAK LANJUT ═══ --}}
            <div x-show="tab === 7" x-cloak>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Rekomendasi & Tindak Lanjut</h2>
                    <button type="button" @click="tindakRows.push({aspek:'materi',permasalahan:'',rekomendasi:'',penanggung_jawab:'',target_semester:''})"
                        class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-sm font-medium rounded-xl transition">
                        ＋ Tambah
                    </button>
                </div>
                <div class="space-y-3">
                    <template x-for="(row, idx) in tindakRows" :key="idx">
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 relative">
                            <div class="absolute top-3 right-3">
                                <button type="button" @click="tindakRows.splice(idx,1)" x-show="tindakRows.length > 1"
                                    class="text-red-400 hover:text-red-600 text-xl leading-none">×</button>
                            </div>
                            <div class="text-xs font-semibold text-gray-400 mb-3">Tindak Lanjut #<span x-text="idx + 1"></span></div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Aspek</label>
                                    <select :name="'tindak_lanjut[' + idx + '][aspek]'" x-model="row.aspek"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                                        <option value="materi">Materi</option>
                                        <option value="metode">Metode Pembelajaran</option>
                                        <option value="penilaian">Penilaian</option>
                                        <option value="fasilitas">Fasilitas</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Permasalahan</label>
                                    <textarea :name="'tindak_lanjut[' + idx + '][permasalahan]'" x-model="row.permasalahan" rows="2"
                                        placeholder="Uraikan permasalahan..."
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500 resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Rekomendasi</label>
                                    <textarea :name="'tindak_lanjut[' + idx + '][rekomendasi]'" x-model="row.rekomendasi" rows="2"
                                        placeholder="Rekomendasi tindakan..."
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500 resize-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Penanggung Jawab</label>
                                    <input type="text" :name="'tindak_lanjut[' + idx + '][penanggung_jawab]'" x-model="row.penanggung_jawab"
                                        placeholder="Nama / jabatan"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Target Semester</label>
                                    <input type="text" :name="'tindak_lanjut[' + idx + '][target_semester]'" x-model="row.target_semester"
                                        placeholder="Ganjil 2025/2026"
                                        class="w-full border border-gray-200 rounded-lg px-2 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Submit on last tab --}}
                <div class="mt-6 pt-5 border-t border-gray-100 flex justify-end gap-3">
                    <a href="{{ route('laporan-evaluasi.index') }}"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow transition">
                        {{ $isEdit ? '💾 Simpan Perubahan' : '✅ Simpan Laporan' }}
                    </button>
                </div>
            </div>

        </div>{{-- /p-6 --}}
</div>{{-- /tab panel --}}

{{-- Tab navigation buttons --}}
<div class="flex justify-between mt-4">
    <button type="button" @click="tab = Math.max(1, tab - 1)" x-show="tab > 1"
        class="px-5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-sm font-medium rounded-xl shadow-sm transition">
        ← Sebelumnya
    </button>
    <div x-show="tab === 1" class="flex-1"></div>
    <button type="button" @click="tab = Math.min(7, tab + 1)" x-show="tab < 7"
        class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow transition ml-auto">
        Selanjutnya →
    </button>
</div>

</form>
</div>
@endsection