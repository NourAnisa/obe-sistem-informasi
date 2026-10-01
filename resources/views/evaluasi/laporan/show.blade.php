@extends('layouts.dashboard')
@section('title', 'Detail Laporan Evaluasi')
@section('breadcrumb', 'Detail Laporan Evaluasi')

@section('content')
@php $mk = $laporan->mataKuliah; @endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" id="print-area">

    {{-- Action bar (hidden on print) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('laporan-evaluasi.index') }}"
                class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">←</a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-display">Laporan Evaluasi</h1>
                <p class="text-sm text-gray-500">{{ $mk->nama ?? '-' }} — {{ $laporan->semester }} {{ $laporan->tahun_akademik }}</p>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button onclick="window.print()"
                class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-xl transition">
                🖨 Cetak
            </button>
            <a href="{{ route('laporan-evaluasi.edit', $laporan->id) }}"
                class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition">
                ✏️ Edit
            </a>
            <a href="{{ route('laporan-evaluasi.export-word', $laporan->id) }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                📄 Export Word
            </a>
        </div>
    </div>

    {{-- ─── DOCUMENT ─── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 print:shadow-none print:border-none p-8 space-y-8">

        {{-- Title --}}
        <div class="text-center border-b border-gray-200 pb-6">
            <h2 class="text-xl font-bold text-brand-800 uppercase tracking-wide">
                Laporan Evaluasi Ketercapaian Pembelajaran
            </h2>
            <p class="text-gray-600 mt-1 text-sm">
                {{ $mk->nama ?? '-' }} ({{ $mk->kode ?? '-' }}) &bull;
                Semester {{ $laporan->semester }} {{ $laporan->tahun_akademik }}
            </p>
            <div class="mt-2">
                @if($laporan->status === 'final')
                <span class="badge bg-green-100 text-green-800">✓ Final</span>
                @else
                <span class="badge bg-yellow-100 text-yellow-800">Draft</span>
                @endif
            </div>
        </div>

        {{-- ─ SECTION 1: Identitas ─ --}}
        <section>
            <h3 class="text-base font-bold text-brand-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs flex items-center justify-center flex-shrink-0">1</span>
                Identitas Mata Kuliah
            </h3>
            <table class="w-full text-sm border-collapse">
                @foreach([
                ['Kode Mata Kuliah', $mk->kode ?? '-'],
                ['Nama Mata Kuliah', $mk->nama ?? '-'],
                ['SKS', ($mk->sks ?? '-') . ' SKS'],
                ['Semester Pelaksanaan', $laporan->semester . ' ' . $laporan->tahun_akademik],
                ['Kelas', $laporan->kelas ?? '-'],
                ['Jumlah Mahasiswa', $laporan->jumlah_mahasiswa . ' mahasiswa'],
                ['Dosen PJMK', $laporan->dosen_pjmk ?? '-'],
                ['Status Laporan', strtoupper($laporan->status)],
                ] as [$label, $val])
                <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-600 bg-gray-50 w-52">{{ $label }}</td>
                    <td class="py-2 px-3 text-gray-800">{{ $val }}</td>
                </tr>
                @endforeach
                @if($laporan->catatan_umum)
                <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-600 bg-gray-50 align-top">Catatan Umum</td>
                    <td class="py-2 px-3 text-gray-800 whitespace-pre-line">{{ $laporan->catatan_umum }}</td>
                </tr>
                @endif
            </table>
        </section>

        {{-- ─ SECTION 2: Komponen Nilai ─ --}}
        <section>
            <h3 class="text-base font-bold text-brand-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs flex items-center justify-center flex-shrink-0">2</span>
                Rekapitulasi Komponen Nilai
            </h3>
            @if($laporan->komponenNilai->isEmpty())
            <p class="text-sm text-gray-400 italic">Belum ada data komponen nilai.</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-brand-600 text-white">
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Komponen</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Bobot (%)</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Rata-rata</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Nilai Min</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Nilai Maks</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Std. Deviasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan->komponenNilai as $k)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium capitalize">{{ $k->komponen }}</td>
                            <td class="px-3 py-2 text-center">{{ $k->bobot_persen }}%</td>
                            <td class="px-3 py-2 text-center">{{ $k->rata_rata }}</td>
                            <td class="px-3 py-2 text-center">{{ $k->nilai_min }}</td>
                            <td class="px-3 py-2 text-center">{{ $k->nilai_max }}</td>
                            <td class="px-3 py-2 text-center">{{ $k->std_deviasi }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

        {{-- ─ SECTION 3: CPMK ─ --}}
        <section>
            <h3 class="text-base font-bold text-brand-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs flex items-center justify-center flex-shrink-0">3</span>
                Rekapitulasi Ketercapaian CPMK
            </h3>
            @if($laporan->cpmks->isEmpty())
            <p class="text-sm text-gray-400 italic">Belum ada data CPMK.</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-brand-600 text-white">
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Kode CPMK</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Deskripsi</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Rata-rata</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">% Lulus</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Target (%)</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Tercapai</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan->cpmks as $c)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-3 py-2 font-semibold text-brand-700">{{ $c->kode_cpmk }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $c->deskripsi_cpmk ?? '-' }}</td>
                            <td class="px-3 py-2 text-center">{{ $c->rata_rata_nilai }}</td>
                            <td class="px-3 py-2 text-center">{{ $c->persen_lulus }}%</td>
                            <td class="px-3 py-2 text-center">{{ $c->target_capaian }}%</td>
                            <td class="px-3 py-2 text-center">
                                @if($c->tercapai)
                                <span class="badge bg-green-100 text-green-800">✓ Ya</span>
                                @else
                                <span class="badge bg-red-100 text-red-800">✗ Tidak</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-600 text-xs">{{ $c->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

        {{-- ─ SECTION 4: CPL ─ --}}
        <section>
            <h3 class="text-base font-bold text-brand-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs flex items-center justify-center flex-shrink-0">4</span>
                Ketercapaian CPL
            </h3>
            @if($laporan->cpls->isEmpty())
            <p class="text-sm text-gray-400 italic">Belum ada data CPL.</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-brand-600 text-white">
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Kode CPL</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Nilai CPL</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Target (%)</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Gap</th>
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700">Tercapai</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan->cpls as $c)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-3 py-2 font-semibold text-brand-700">{{ $c->kode_cpl }}</td>
                            <td class="px-3 py-2 text-center">{{ $c->nilai_cpl }}</td>
                            <td class="px-3 py-2 text-center">{{ $c->target_cpl }}%</td>
                            <td class="px-3 py-2 text-center font-mono {{ $c->gap >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $c->gap >= 0 ? '+' : '' }}{{ $c->gap }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($c->tercapai)
                                <span class="badge bg-green-100 text-green-800">✓ Ya</span>
                                @else
                                <span class="badge bg-red-100 text-red-800">✗ Tidak</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-600 text-xs">{{ $c->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

        {{-- ─ SECTION 5: Distribusi Nilai ─ --}}
        <section>
            <h3 class="text-base font-bold text-brand-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs flex items-center justify-center flex-shrink-0">5</span>
                Distribusi Nilai & Kelulusan
            </h3>
            @if(!$laporan->distribusiNilai)
            <p class="text-sm text-gray-400 italic">Belum ada data distribusi nilai.</p>
            @else
            @php $d = $laporan->distribusiNilai; $total = $d->jml_a + $d->jml_b + $d->jml_c + $d->jml_d + $d->jml_e; @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-4">
                @foreach([
                ['A (≥80)', $d->jml_a, 'bg-green-50 text-green-800 border-green-200'],
                ['B (70-79)', $d->jml_b, 'bg-blue-50 text-blue-800 border-blue-200'],
                ['C (60-69)', $d->jml_c, 'bg-yellow-50 text-yellow-800 border-yellow-200'],
                ['D (50-59)', $d->jml_d, 'bg-orange-50 text-orange-800 border-orange-200'],
                ['E (<50)', $d->jml_e, 'bg-red-50 text-red-800 border-red-200'],
                    ] as [$label, $count, $cls])
                    <div class="rounded-xl border p-3 text-center {{ $cls }}">
                        <div class="text-2xl font-bold">{{ $count }}</div>
                        <div class="text-xs font-medium mt-0.5">Nilai {{ $label }}</div>
                    </div>
                    @endforeach
            </div>
            <table class="w-full text-sm border-collapse">
                <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-600 bg-gray-50 w-52">Jumlah Lulus</td>
                    <td class="py-2 px-3 text-gray-800 font-semibold text-green-700">{{ $d->jumlah_lulus }} mahasiswa</td>
                </tr>
                <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-600 bg-gray-50">Jumlah Tidak Lulus</td>
                    <td class="py-2 px-3 text-gray-800 font-semibold text-red-700">{{ $d->jumlah_tidak_lulus }} mahasiswa</td>
                </tr>
                <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-600 bg-gray-50">Persentase Lulus</td>
                    <td class="py-2 px-3 text-gray-800 font-semibold">{{ $d->persen_lulus }}%</td>
                </tr>
                <tr class="border-b border-gray-100">
                    <td class="py-2 px-3 font-medium text-gray-600 bg-gray-50">Rata-rata Nilai Final</td>
                    <td class="py-2 px-3 text-gray-800 font-semibold">{{ $d->rata_rata_final }}</td>
                </tr>
            </table>
            @endif
        </section>

        {{-- ─ SECTION 6: Hambatan ─ --}}
        <section>
            <h3 class="text-base font-bold text-brand-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs flex items-center justify-center flex-shrink-0">6</span>
                Hambatan & Permasalahan
            </h3>
            @if($laporan->hambatans->isEmpty())
            <p class="text-sm text-gray-400 italic">Tidak ada hambatan yang dicatat.</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-brand-600 text-white">
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700 w-10">No</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Jenis Hambatan</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Deskripsi</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Solusi / Usulan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan->hambatans as $h)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-3 py-2 text-center text-gray-500">{{ $h->no_urut }}</td>
                            <td class="px-3 py-2 capitalize font-medium text-gray-700">{{ $h->jenis_hambatan }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $h->deskripsi }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $h->solusi_usulan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

        {{-- ─ SECTION 7: Tindak Lanjut ─ --}}
        <section>
            <h3 class="text-base font-bold text-brand-700 mb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-brand-600 text-white text-xs flex items-center justify-center flex-shrink-0">7</span>
                Rekomendasi & Tindak Lanjut
            </h3>
            @if($laporan->tindakLanjuts->isEmpty())
            <p class="text-sm text-gray-400 italic">Belum ada tindak lanjut yang direncanakan.</p>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-brand-600 text-white">
                            <th class="px-3 py-2 text-center font-semibold border border-brand-700 w-10">No</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Aspek</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Permasalahan</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Rekomendasi</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Penanggung Jawab</th>
                            <th class="px-3 py-2 text-left font-semibold border border-brand-700">Target Semester</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan->tindakLanjuts as $t)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-3 py-2 text-center text-gray-500">{{ $t->no_urut }}</td>
                            <td class="px-3 py-2 capitalize font-medium text-gray-700">{{ $t->aspek }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $t->permasalahan }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $t->rekomendasi }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $t->penanggung_jawab ?? '-' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $t->target_semester ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

        {{-- Footer / Signature area --}}
        <div class="border-t border-gray-200 pt-6 mt-6">
            <div class="grid grid-cols-2 gap-8 text-sm text-center">
                <div>
                    <p class="text-gray-500 mb-12">Mengetahui,</p>
                    <p class="font-semibold text-gray-800 border-t border-gray-400 pt-1 mx-8">Ketua Program Studi</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-12">Dosen PJMK,</p>
                    <p class="font-semibold text-gray-800 border-t border-gray-400 pt-1 mx-8">{{ $laporan->dosen_pjmk ?? '...........................' }}</p>
                </div>
            </div>
        </div>

    </div>{{-- /document --}}
</div>

@push('styles')
<style>
    @media print {
        .print\:hidden {
            display: none !important;
        }

        aside,
        header {
            display: none !important;
        }

        body {
            background: white !important;
        }

        #print-area {
            max-width: 100%;
            padding: 0;
        }

        .rounded-2xl {
            border-radius: 0 !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }
    }
</style>
@endpush
@endsection