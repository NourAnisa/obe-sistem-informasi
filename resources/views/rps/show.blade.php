@extends('layouts.dashboard')
@section('title', 'RPS ' . $rps['header']['kode_mk'])
@section('breadcrumb', 'RPS — ' . $rps['header']['kode_mk'])

@push('styles')
<style>
    /* ──────────────────────────────────────────────
   RPS DOCUMENT STYLES — UNISM Template TA 2025/2026
   Landscape A4 (297×210mm), Times New Roman
────────────────────────────────────────────── */
    @media screen {
        body {
            background: #e5e7eb;
        }
    }

    .rps-wrapper {
        padding: 16px 8px;
    }

    .rps-doc {
        font-family: 'Times New Roman', Times, serif;
        font-size: 10pt;
        line-height: 1.4;
        color: #000;
        background: #fff;
        width: 297mm;
        max-width: 297mm;
        margin: 0 auto;
        padding: 16mm 18mm;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
    }

    .rps-doc table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
        color: #000;
    }

    .rps-doc table td,
    .rps-doc table th {
        border: 1px solid #000;
        padding: 3px 6px;
        vertical-align: top;
    }

    .cell-label {
        font-weight: bold;
        vertical-align: middle;
        white-space: nowrap;
        background: #fff;
    }

    .row-green td {
        background-color: #A8D08D;
        font-weight: bold;
        text-align: center;
    }

    .row-gray td {
        background-color: #d9d9d9;
        font-weight: bold;
    }

    .row-uts td,
    .row-uas td {
        background-color: #A8D08D;
        font-weight: bold;
        text-align: center;
    }

    .rps-doc table th {
        background-color: #A8D08D;
    }

    .blueprint-inner {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
        margin-top: 4px;
    }

    .blueprint-inner th,
    .blueprint-inner td {
        border: 1px solid #555;
        padding: 2px 4px;
        vertical-align: top;
    }

    .blueprint-inner th {
        background: #d9d9d9;
        text-align: center;
        font-weight: bold;
    }

    .section-page-title {
        font-family: 'Times New Roman', Times, serif;
        font-size: 11pt;
        font-weight: bold;
        text-align: center;
        margin: 6px 0 3px;
        color: #000;
    }

    /* ── Rencana Kegiatan column widths (from template) ── */
    .rkbm-col-mg {
        width: 4%;
        min-width: 28px;
        text-align: center;
    }

    .rkbm-col-cpmk {
        width: 13%;
    }

    .rkbm-col-ind {
        width: 13%;
    }

    .rkbm-col-tek {
        width: 11%;
    }

    .rkbm-col-sinkron {
        width: 15%;
    }

    .rkbm-col-asinkron {
        width: 12%;
    }

    .rkbm-col-materi {
        width: 23%;
    }

    .rkbm-col-bobot {
        width: 9%;
        min-width: 50px;
        text-align: center;
    }

    /* Action toolbar */
    .no-print {
        position: sticky;
        top: 0;
        z-index: 50;
        background: #1e3a8a;
        color: #fff;
        padding: 7px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        width: 297mm;
        max-width: 297mm;
        margin: 0 auto 10px;
        border-radius: 0 0 8px 8px;
        box-sizing: border-box;
    }

    .no-print a,
    .no-print button {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        font-family: system-ui, sans-serif;
    }

    .btn-edit {
        background: #f59e0b;
        color: #000;
    }

    .btn-print {
        background: #10b981;
        color: #fff;
    }

    .btn-pdf {
        background: #ef4444;
        color: #fff;
    }

    .btn-docx {
        background: #3b82f6;
        color: #fff;
    }

    /* ── Portrait section (Kontrak & Rencana Tugas) ── */
    .portrait-section {
        width: 210mm;
        max-width: 210mm;
        margin: 0 auto;
        box-sizing: border-box;
        font-size: 10pt;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 16mm 18mm;
        }

        @page portrait {
            size: A4 portrait;
            margin: 18mm 20mm;
        }

        /* Force background colors to print (fixes #A8D08D not showing) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        .no-print {
            display: none !important;
        }

        /* Hide all layout chrome: sidebar, topbar, breadcrumb nav */
        aside,
        header,
        nav,
        [x-data*="sidebarOpen"]>aside,
        body>aside {
            display: none !important;
        }

        body {
            background: white;
        }

        main {
            margin-left: 0 !important;
            padding-top: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        /* Override Alpine.js dynamic ml-64 class set by JS */
        main[class*="ml-"] {
            margin-left: 0 !important;
        }

        .p-6 {
            padding: 0 !important;
        }

        .rps-wrapper {
            padding: 0;
        }

        .rps-doc {
            box-shadow: none;
            padding: 0;
            width: 100%;
            max-width: 100%;
            margin: 0;
        }

        .portrait-section {
            page: portrait;
            width: 100%;
            max-width: 100%;
        }

        .page-break {
            page-break-before: always;
        }
    }
</style>
@endpush

@section('content')
<div class="rps-wrapper">

    {{-- Action Bar --}}
    <div class="no-print">
        <span style="font-size:13px;font-weight:700;font-family:system-ui,sans-serif;margin-right:4px">
            📄 RPS: {{ $rps['header']['kode_mk'] }}
        </span>
        <a href="{{ route('rps.edit', $mk->kode) }}" class="btn-edit">✏️ Edit RPS</a>
        <a href="{{ route('rps.pdf', $mk->kode) }}" class="btn-pdf" target="_blank">📥 PDF</a>
        <a href="{{ route('rps.docx', $mk->kode) }}" class="btn-docx" target="_blank">📝 Word</a>
        <button onclick="printRps()" class="btn-print">🖨️ Cetak RPS</button>
        <a href="{{ route('rps.kontrak', $mk->kode) }}"
            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-semibold font-sans"
            style="background:#7c3aed;color:#fff">📑 Kontrak</a>
        <a href="{{ route('rps.index') }}" style="background:#6b7280;color:#fff"
            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-semibold font-sans">
            ← Kembali
        </a>
        @if(session('success'))
        <span style="background:#d1fae5;color:#065f46;padding:4px 10px;border-radius:5px;font-size:11px;font-family:system-ui,sans-serif">
            ✅ {{ session('success') }}
        </span>
        @endif
    </div>

    <div class="rps-doc">

        @php
        $h = $rps['header'];
        $cpls = $rps['cpls'];
        $cpmks = $rps['cpmks'];
        $subCpmks = $rps['subCpmks'];
        $bahanKajians = $rps['bahanKajians'];
        $referensiU = $rps['referensiUtama'];
        $referensiP = $rps['referensiPendukung'];
        $blueprint = $rps['blueprint'];
        $detail = $rps['detail'] ?? null;
        $pertemuan = $rps['pertemuan'] ?? collect();
        $jadwal = $rps['jadwalMingguan'];
        $kontrak = $rps['kontrak'];
        $rencanaTugas = $rps['rencanaTugas'];

        // Rowspan: 1 CPL header + n CPL + 1 CPMK header + n CPMK + 1 SubCPMK header + n SubCPMK
        $cpRowspan = 3 + count($cpls) + count($cpmks) + count($subCpmks);

        // Dosen list
        $dosenList = $detail?->dosen_pengampu ?? [];
        if (empty($dosenList)) $dosenList = [$h['pjmk']];
        @endphp

        {{-- ══ KOP ══ --}}
        <table>
            <tr>
                <td width="80" style="text-align:center;vertical-align:middle;border:none">
                    <img src="{{ asset('images/logo_unism.png') }}" alt="Logo UNISM" style="height:70px;width:auto">
                </td>
                <td style="text-align:center;vertical-align:middle">
                    <div style="font-size:13pt;font-weight:bold">UNIVERSITAS SARI MULIA</div>
                    <div style="font-size:10pt;font-weight:bold">FAKULTAS {{ strtoupper($h['fakultas']) }}</div>
                    <div style="font-size:10pt;font-weight:bold">PROGRAM STUDI {{ strtoupper($h['prodi']) }}</div>
                    <div style="font-size:10pt">TAHUN AKADEMIK {{ $h['tahun_akademik'] }}</div>
                </td>
                <td width="130" style="text-align:center;vertical-align:middle">
                    <div style="font-size:10pt;font-weight:bold;line-height:1.4">RENCANA<br>PEMBELAJARAN<br>SEMESTER<br>(RPS)</div>
                </td>
            </tr>
        </table>

        {{-- ══ IDENTITAS RPS ══ --}}
        <table style="margin-top:-1px">
            <tr class="row-green">
                <td colspan="5" style="text-align:center;font-size:11pt">RENCANA PEMBELAJARAN SEMESTER (RPS)
                    <br><em style="font-size:9.5pt;font-weight:bold">Semester Learning Plan</em>
                </td>
            </tr>
            <tr>
                <th style="background:#d9d9d9;text-align:center">Mata Kuliah (MK)<br><em style="font-weight:bold;font-size:8.5pt">Course Name</em></th>
                <th style="background:#d9d9d9;text-align:center">Kode<br><em style="font-weight:bold;font-size:8.5pt">Code</em></th>
                <th style="background:#d9d9d9;text-align:center">Bobot (SKS)<br><em style="font-weight:bold;font-size:8.5pt">Credits</em></th>
                <th style="background:#d9d9d9;text-align:center">Semester<br><em style="font-weight:bold;font-size:8.5pt">Semester</em></th>
                <th style="background:#d9d9d9;text-align:center">Tanggal Penyusunan<br><em style="font-weight:bold;font-size:8.5pt">Preparation Date</em></th>
            </tr>
            <tr>
                <td style="font-weight:bold">{{ $h['nama_mk'] }}<br>
                    <em style="font-weight:bold;font-size:9pt;color:#1a5276">{{ $h['nama_mk_en'] ?? $rps['mk']->nama_en ?? $h['nama_mk'] }}</em>
                </td>
                <td style="text-align:center">{{ $h['kode_mk'] }}</td>
                <td style="text-align:center">
                    <strong>{{ $h['sks'] }} SKS</strong><br>
                    <em style="font-size:9pt">({{ $h['sks_teori'] ?? $h['sks'] }}T + {{ $h['sks_praktikum'] ?? 0 }}P)</em><br>
                    <em style="font-size:8.5pt;color:#1a5276"><strong>{{ $h['sks'] }} Credits</strong> ({{ $h['sks_teori'] ?? $h['sks'] }}T + {{ $h['sks_praktikum'] ?? 0 }}P)</em>
                </td>
                <td style="text-align:center">
                    Semester {{ $h['semester'] }}<br>
                    <em style="font-size:9pt">({{ ($h['semester'] % 2 == 1) ? 'Ganjil' : 'Genap' }})</em><br>
                    <em style="font-size:8.5pt;color:#1a5276">Semester {{ $h['semester'] }} ({{ ($h['semester'] % 2 == 1) ? 'Odd' : 'Even' }})</em>
                </td>
                <td>{{ $h['tanggal'] }}<br>
                    <em style="font-size:8.5pt;color:#1a5276">{{ $h['tanggal_en'] }}</em>
                </td>
            </tr>
        </table>

        {{-- ══ OTORISASI ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td rowspan="3" class="cell-label" width="130">OTORISASI /<br>PENGESAHAN<br>
                    <em style="font-weight:normal;font-size:8.5pt;color:#1a5276">Authorization /<br>Approval</em>
                </td>
                <td style="font-weight:bold;text-align:center;background:#A8D08D">Dosen Pengembang RPS<br>
                    <em style="font-size:8.5pt;font-weight:bold">Course Plan Developer</em>
                </td>
                <td style="font-weight:bold;text-align:center;background:#A8D08D">Ketua Program Studi<br>
                    <em style="font-size:8.5pt;font-weight:bold">Head of Study Program</em>
                </td>
            </tr>
            <tr>
                <td style="height:80px">&nbsp;</td>
                <td style="height:80px">&nbsp;</td>
            </tr>
            <tr>
                <td style="font-size:9.5pt">
                    ({{ $dosenList[0] ?? '..................................' }})<br>NIK.
                </td>
                <td style="font-size:9.5pt">
                    ({{ $h['kaprodi'] }})<br>NIK. {{ $h['nik_kaprodi'] ?? '' }}
                </td>
            </tr>
        </table>

        {{-- ══ CAPAIAN PEMBELAJARAN ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td rowspan="{{ $cpRowspan }}" class="cell-label" width="130" style="text-align:center;vertical-align:middle">
                    <strong>Capaian<br>Pembelajaran</strong><br>
                    <em style="font-weight:normal;font-size:8pt;color:#1a5276">Learning<br>Outcomes</em>
                </td>
                <td colspan="2" style="background:#d9d9d9;font-weight:bold">
                    Capaian Pembelajaran Lulusan (CPL) yang dibebankan pada MK<br>
                    <em style="font-size:8.5pt;font-weight:bold">Graduate Learning Outcomes (PLO) assigned to this Course</em>
                </td>
            </tr>
            @forelse($cpls as $cpl)
            <tr>
                <td width="90" style="font-weight:bold;white-space:nowrap">{{ $cpl->kode }}</td>
                <td>{{ $cpl->deskripsi }}
                    @if($cpl->deskripsi_en)
                    <br><span style="color:#1a5276;font-style:italic;font-size:9pt">{{ $cpl->deskripsi_en }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2"><em style="color:#999">Belum ada CPL</em></td>
            </tr>
            @endforelse
            <tr>
                <td colspan="2" style="background:#d9d9d9;font-weight:bold">
                    Capaian Pembelajaran Mata Kuliah (CPMK)<br>
                    <em style="font-size:8.5pt;font-weight:bold">Course Learning Outcomes (CLO)</em>
                </td>
            </tr>
            @forelse($cpmks as $cpmk)
            <tr>
                <td style="font-weight:bold;white-space:nowrap">{{ $cpmk->kode }}</td>
                <td>{{ $cpmk->deskripsi }}
                    @if($cpmk->deskripsi_en)
                    <br><span style="color:#1a5276;font-style:italic;font-size:9pt">{{ $cpmk->deskripsi_en }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2"><em style="color:#999">Belum ada CPMK</em></td>
            </tr>
            @endforelse
            <tr>
                <td colspan="2" style="background:#d9d9d9;font-weight:bold">
                    Kemampuan akhir tiap tahapan belajar <em>(Sub CPMK)</em><br>
                    <em style="font-size:8.5pt;font-weight:bold">Expected Learning Outcomes at Each Learning Stage <em>(Sub CLO)</em></em>
                </td>
            </tr>
            @forelse($subCpmks as $sub)
            <tr>
                <td style="font-weight:bold;white-space:nowrap">{{ $sub->kode }}</td>
                <td>{{ $sub->deskripsi }}
                    @if($sub->deskripsi_en)
                    <br><span style="color:#1a5276;font-style:italic;font-size:9pt">{{ $sub->deskripsi_en }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2"><em style="color:#999">Belum ada Sub CPMK</em></td>
            </tr>
            @endforelse
        </table>

        {{-- ══ DESKRIPSI ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td class="cell-label" width="130"><strong>Deskripsi<br>Singkat<br>Mata Kuliah</strong><br><em style="font-weight:normal;font-size:8pt;color:#1a5276">Brief Course<br>Description</em></td>
                <td>{{ $h['deskripsi'] ?: ($rps['mk']->deskripsi ?: '–') }}
                    @if($rps['mk']->deskripsi_en)
                    <br><span style="color:#1a5276;font-style:italic;font-size:9pt">{{ $rps['mk']->deskripsi_en }}</span>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ══ BAHAN KAJIAN ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td class="cell-label" width="130"><strong>Bahan Kajian</strong><br><em style="font-weight:normal;font-size:8pt;color:#1a5276">Study Materials</em></td>
                <td>
                    @forelse($bahanKajians as $i => $bk)
                    {{ $i+1 }}. {{ $bk->nama }};<br>
                    @empty
                    <em style="color:#999">Belum ada bahan kajian</em>
                    @endforelse
                </td>
            </tr>
        </table>

        {{-- ══ TAUTAN ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td class="cell-label" width="130"><strong>Tautan Kelas<br>Daring</strong><br><em style="font-weight:normal;font-size:8pt;color:#1a5276">Online Class<br>Link</em></td>
                <td>
                    @if($detail?->tautan_kelas_daring)
                    <a href="{{ $detail->tautan_kelas_daring }}" target="_blank" style="color:#1a5276">{{ $detail->tautan_kelas_daring }}</a>
                    @else
                    <em style="color:#999">–</em>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ══ PUSTAKA ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td class="cell-label" width="130"><strong>Pustaka</strong><br><em style="font-weight:normal;font-size:8pt;color:#1a5276">References</em></td>
                <td>
                    @if($referensiU->count())
                    <strong>Utama / <em>Main</em>:</strong><br>
                    @foreach($referensiU as $i => $ref){{ $i+1 }}. {!! $ref->citation !!}<br>@endforeach
                    @endif
                    @if($referensiP->count())
                    <br><strong>Pendukung / <em>Supplementary</em>:</strong><br>
                    @foreach($referensiP as $i => $ref){{ $i+1 }}. {!! $ref->citation !!}<br>@endforeach
                    @endif
                    @if($referensiU->isEmpty() && $referensiP->isEmpty())
                    <em style="color:#999">Belum ada referensi</em>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ══ BLUEPRINT ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td class="cell-label" width="130" style="vertical-align:top">
                    <strong>Kriteria<br>Penilaian &amp;<br>Blueprint<br>Asesmen<br>CPL–CPMK</strong><br>
                    <em style="font-weight:normal;font-size:7.5pt;color:#1a5276">Assessment<br>Criteria &amp;<br>Blueprint</em>
                </td>
                <td>
                    @if($detail?->catatan_blueprint)
                    <p style="color:blue;font-style:italic;font-size:9pt;margin:0 0 6px">{{ $detail->catatan_blueprint }}</p>
                    @endif
                    <table class="blueprint-inner">
                        <tr>
                            <th style="width:110px">Basis Evaluasi<br><em style="font-size:7.5pt;font-weight:normal">Evaluation Basis</em></th>
                            <th>Komponen Evaluasi<br><em style="font-size:7.5pt;font-weight:normal">Evaluation Component</em></th>
                            <th style="width:90px">Sub CPMK/CPMK</th>
                            <th>Deskripsi % Indikator<br><em style="font-size:7.5pt;font-weight:normal">% Indicator Description</em></th>
                            <th>Metode Penilaian<br><em style="font-size:7.5pt;font-weight:normal">Assessment Method</em></th>
                            <th style="width:55px;text-align:center">Bobot %<br><em style="font-size:7.5pt;font-weight:normal">Weight %</em></th>
                        </tr>
                        @php
                        $basisEn = [
                        'Aktivitas Partisipatif' => 'Participatory Activities',
                        'Hasil Proyek / Produk' => 'Project / Product',
                        'Tugas Terstruktur' => 'Structured Assignment',
                        'Ujian Tengah Semester' => 'Mid-Term Examination',
                        'Ujian Akhir Semester' => 'End-Term Examination',
                        ];
                        $komponenEn = [
                        'Partisipasi & Keaktifan Kelas' => 'Class Participation & Activeness',
                        'Proyek / Case Study' => 'Project / Case Study',
                        'Tugas Individu / Kelompok' => 'Individual / Group Assignment',
                        'Tes Tertulis UTS' => 'Written Mid-Term Test',
                        'Tes Tertulis UAS' => 'Written End-Term Test',
                        ];
                        $metodeEn = [
                        'Observasi, Penilaian Diri' => 'Observation, Self-Assessment',
                        'Penilaian Produk, Rubrik Holistik' => 'Product Assessment, Holistic Rubric',
                        'Tes Tertulis / Laporan' => 'Written Test / Report',
                        'Tes Esai / Pilihan Ganda' => 'Essay / Multiple Choice Test',
                        ];
                        @endphp
                        @forelse($blueprint['rows'] as $row)
                        <tr>
                            @if($row['rowspan'] > 0)
                            <td rowspan="{{ $row['rowspan'] }}" style="vertical-align:middle;font-weight:bold">
                                {{ $row['basis'] }}
                                @if(isset($basisEn[$row['basis']]))
                                <br><span style="font-weight:normal;font-style:italic;color:#1a5276;font-size:8pt">{{ $basisEn[$row['basis']] }}</span>
                                @endif
                            </td>
                            @endif
                            <td>
                                {{ $row['komponen'] }}
                                @if(isset($komponenEn[$row['komponen']]))
                                <br><span style="font-style:italic;color:#1a5276;font-size:8pt">{{ $komponenEn[$row['komponen']] }}</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                @php
                                // Get CPMK kodes from the row, then find their Sub CPMKs
                                $rowCpmkKodes = array_map('trim', explode(',', $row['cpmk']));
                                $rowSubCpmks = $subCpmks->filter(fn($s) => in_array($s->cpmk->kode ?? '', $rowCpmkKodes))->sortBy('kode');
                                @endphp
                                @if($rowSubCpmks->isNotEmpty())
                                @foreach($rowSubCpmks as $rs)
                                <div style="font-size:8pt;font-weight:bold">{{ $rs->kode }}</div>
                                @endforeach
                                @else
                                {{ $row['cpmk'] }}
                                @endif
                            </td>
                            <td>{{ $row['deskripsi'] }}</td>
                            <td>
                                {{ $row['metode'] }}
                                @if(isset($metodeEn[$row['metode']]))
                                <br><span style="font-style:italic;color:#1a5276;font-size:8pt">{{ $metodeEn[$row['metode']] }}</span>
                                @endif
                            </td>
                            <td style="text-align:center">{{ $row['bobot'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;font-style:italic;color:#999">Belum ada data blueprint</td>
                        </tr>
                        @endforelse
                        <tr style="background:#d9d9d9">
                            <td colspan="5" style="text-align:right;font-weight:bold">Total</td>
                            <td style="text-align:center;font-weight:bold">{{ $blueprint['grandTotal'] ?: 100 }}</td>
                        </tr>
                    </table>
                    @if(!empty($blueprint['rangkuman']))
                    <br><strong style="font-size:9.5pt">Rangkuman Bobot per Basis Evaluasi / <em>Assessment Weight Summary</em>:</strong>
                    <table class="blueprint-inner" style="margin-top:4px">
                        <tr>
                            <th>Kategori / <em>Category</em></th>
                            <th style="width:70px;text-align:center">Bobot (%) / <em>Weight</em></th>
                            <th>Keterangan / <em>Description</em></th>
                        </tr>
                        @foreach($blueprint['rangkuman'] as $r)
                        <tr>
                            <td>
                                {{ $r['kategori'] }}
                                @if(isset($basisEn[$r['kategori']]))
                                <br><span style="font-style:italic;color:#1a5276;font-size:8pt">{{ $basisEn[$r['kategori']] }}</span>
                                @endif
                            </td>
                            <td style="text-align:center">{{ $r['bobot'] }}%</td>
                            <td>{{ $r['keterangan'] }}</td>
                        </tr>
                        @endforeach
                        <tr style="background:#d9d9d9">
                            <td><strong>Total</strong></td>
                            <td style="text-align:center"><strong>{{ array_sum(array_column($blueprint['rangkuman'], 'bobot')) }}%</strong></td>
                            <td></td>
                        </tr>
                    </table>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ══ DOSEN PENGAMPU ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td class="cell-label" width="130"><strong>Dosen<br>Pengampu</strong><br><em style="font-weight:normal;font-size:8pt;color:#1a5276">Lecturers</em></td>
                <td>
                    @foreach(array_chunk($dosenList, 2) as $chunk)
                    <div style="display:flex;gap:30px">
                        @foreach($chunk as $idx => $d)
                        <span>{{ ($loop->parent->index * 2 + $idx + 1) }}. {{ $d }}</span>
                        @endforeach
                    </div>
                    @endforeach
                </td>
            </tr>
        </table>

        {{-- ══ MATA KULIAH SYARAT ══ --}}
        <table style="margin-top:-1px">
            <tr>
                <td class="cell-label" width="130"><strong>Mata Kuliah<br>Syarat</strong><br><em style="font-weight:normal;font-size:8pt;color:#1a5276">Prerequisite<br>Course</em></td>
                <td>{{ $h['prasyarat'] ?? '–' }}</td>
            </tr>
        </table>

        {{-- ══ RENCANA KEGIATAN BELAJAR MENGAJAR ══ --}}
        <br>
        <p style="font-weight:bold;font-size:11pt;margin:8px 0 4px"><strong>Rencana Kegiatan Belajar Mengajar</strong><br><em style="font-size:9.5pt;font-weight:bold">Teaching and Learning Activity Plan</em></p>

        @php $useManual = $pertemuan->count() > 0; @endphp

        @if(!$useManual)
        <div class="no-print" style="background:#fffbeb;border:1px solid #f59e0b;padding:6px 10px;margin-bottom:6px;font-size:9.5pt;font-family:system-ui,sans-serif;border-radius:4px">
            ⚠️ <em>Data jadwal digenerate otomatis. <a href="{{ route('rps.edit', $mk->kode) }}" style="color:#1d4ed8">Edit RPS</a> untuk mengisi detail pertemuan manual.</em>
        </div>
        @endif

        <table>
            <colgroup>
                <col class="rkbm-col-mg">
                <col class="rkbm-col-cpmk">
                <col class="rkbm-col-ind">
                <col class="rkbm-col-tek">
                <col class="rkbm-col-sinkron">
                <col class="rkbm-col-asinkron">
                <col class="rkbm-col-materi">
                <col class="rkbm-col-bobot">
            </colgroup>
            <tr>
                <th rowspan="2" class="rkbm-col-mg" style="text-align:center;vertical-align:middle">Mg<br>Ke-<br><em style="font-size:7.5pt;font-weight:normal">Week</em></th>
                <th rowspan="2" style="text-align:center;vertical-align:middle">Kemampuan Akhir Tiap Tahapan Belajar (CPMK)<br><em style="font-size:7.5pt;font-weight:normal">Expected Learning Outcomes at Each Stage (CLO)</em></th>
                <th colspan="2" style="text-align:center">Penilaian<br><em style="font-size:7.5pt;font-weight:normal">Assessment</em></th>
                <th colspan="2" style="text-align:center">Bentuk Pembelajaran; Metode Pembelajaran; Penugasan Mahasiswa; [Estimasi Waktu]<br><em style="font-size:7.5pt;font-weight:normal">Learning Form; Teaching Method; Student Assignment; [Time Estimate]</em></th>
                <th rowspan="2" style="text-align:center;vertical-align:middle">Materi Pembelajaran [Pustaka]<br><em style="font-size:7.5pt;font-weight:normal">Learning Materials [References]</em></th>
                <th rowspan="2" style="text-align:center;vertical-align:middle">Bobot Penilaian (%) &amp; Dosen<br><em style="font-size:7.5pt;font-weight:normal">Assessment Weight (%) &amp; Lecturer</em></th>
            </tr>
            <tr>
                <th style="text-align:center">Indikator<br><em style="font-size:7.5pt;font-weight:normal">Indicators</em></th>
                <th style="text-align:center">Teknik &amp; Kreteria<br><em style="font-size:7.5pt;font-weight:normal">Technique &amp; Criteria</em></th>
                <th style="text-align:center">Tatap Muka / Sinkron<br><em style="font-size:7.5pt;font-weight:normal">Face-to-Face / Synchronous</em></th>
                <th style="text-align:center">Pembelajaran Mandiri / Asinkron<br><em style="font-size:7.5pt;font-weight:normal">Self-Directed / Asynchronous</em></th>
            </tr>
            <tr style="background:#d9d9d9;text-align:center;font-weight:bold">
                <td>(1)</td>
                <td>(2)</td>
                <td>(3)</td>
                <td>(4)</td>
                <td>(5)</td>
                <td>(6)</td>
                <td>(7)</td>
                <td>(8)</td>
            </tr>
            @if($useManual)
            @foreach($pertemuan->sortBy(fn($p) => (int)$p->minggu) as $p)
            @if((int)$p->minggu === 8)
            <tr class="row-uts">
                <td style="text-align:center">8</td>
                <td colspan="7">UJIAN TENGAH SEMESTER (UTS)<br><em style="font-size:8.5pt;font-weight:normal">Mid-Term Examination (MTE)</em></td>
            </tr>
            @elseif((int)$p->minggu === 16)
            <tr class="row-uas">
                <td style="text-align:center">16</td>
                <td colspan="7">EVALUASI AKHIR SEMESTER (EAS) : Melakukan Validasi Penilaian Akhir dan Menentukan Kelulusan Mahasiswa<br><em style="font-size:8.5pt;font-weight:normal">End-of-Semester Evaluation (ESE) : Conducting Final Grade Validation and Determining Student Graduation</em></td>
            </tr>
            @else
            {{-- Row 1: Indonesian content --}}
            <tr>
                <td rowspan="4" style="text-align:center;vertical-align:middle">{{ $p->minggu }}</td>
                <td rowspan="2" style="vertical-align:top">
                    @php
                    $selSubs = $p->sub_cpmk_ids && count($p->sub_cpmk_ids) > 0
                    ? $subCpmks->whereIn('id', $p->sub_cpmk_ids)->sortBy('kode')->values()
                    : collect();
                    @endphp
                    @if($selSubs->count())
                    @foreach($selSubs as $ss)
                    <div style="margin-bottom:2px"><strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi }}</div>
                    @endforeach
                    @else
                    {{ $p->cpmk_label ?: '–' }}
                    @endif
                </td>
                <td rowspan="2" style="vertical-align:top">{{ $p->indikator ?: '–' }}</td>
                <td rowspan="2" style="vertical-align:top">
                    @if($p->teknik_penilaian)<strong>Teknik Non tes:</strong><br>{{ $p->teknik_penilaian }}@else–@endif
                    @if($p->kreteria)<br><br><strong>Kriteria:</strong><br><em>{{ $p->kreteria }}</em>@else<br><br><em style="color:#aaa">Kriteria: Pedoman Penskoran</em>@endif
                </td>
                <td style="vertical-align:top">{{ $p->metode_sinkron ?: '–' }}</td>
                <td style="vertical-align:top">{{ $p->metode_asinkron ?: '–' }}</td>
                <td rowspan="2" style="vertical-align:top">{{ $p->materi ?: '–' }}</td>
                <td rowspan="4" style="text-align:center;vertical-align:middle">
                    {{ $p->bobot > 0 ? $p->bobot.'%' : '–' }}
                    @if($p->dosen)<br><small style="font-size:8.5pt">{{ $p->dosen }}</small>@endif
                </td>
            </tr>
            {{-- Row 2: Tugas ID --}}
            <tr style="border-top:1px dashed #bbb">
                <td colspan="2" style="vertical-align:top">
                    @if($p->tugas)<strong>{{ $p->tugas }}</strong>@endif
                </td>
            </tr>
            {{-- Row 3: English italic (SubCPMK, Indikator, Teknik, Sinkron, Asinkron, Materi) --}}
            <tr style="border-top:1px dashed #aaa;background:#f0f9ff">
                <td rowspan="2" style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">
                    @if($selSubs->count())
                    @foreach($selSubs as $ss)
                    <div style="margin-bottom:2px">@if($ss->deskripsi_en)<strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi_en }}@endif</div>
                    @endforeach
                    @endif
                </td>
                <td rowspan="2" style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">{{ $p->indikator_en ?: '' }}</td>
                <td rowspan="2" style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">
                    @if($p->teknik_penilaian_en)<em>Non-test Techniques:</em><br>{{ $p->teknik_penilaian_en }}@endif
                    @if($p->kreteria_en)<br><br><em>Criteria:</em><br>{{ $p->kreteria_en }}@endif
                </td>
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">{{ $p->metode_sinkron_en ?: '' }}</td>
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">{{ $p->metode_asinkron_en ?: '' }}</td>
                <td rowspan="2" style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">{{ $p->materi_en ?: '' }}</td>
            </tr>
            {{-- Row 4: Tugas EN --}}
            <tr style="background:#f0f9ff">
                <td colspan="2" style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt;border-top:none">
                    @if($p->tugas_en){{ $p->tugas_en }}@endif
                </td>
            </tr>
            @endif
            @endforeach
            @else
            @foreach($jadwal as $row)
            @if($row['type'] === 'uts')
            <tr class="row-uts">
                <td style="text-align:center">{{ $row['minggu'] }}</td>
                <td colspan="7">UJIAN TENGAH SEMESTER (UTS)<br><em style="font-size:8.5pt;font-weight:normal">Mid-Term Examination (MTE)</em></td>
            </tr>
            @elseif($row['type'] === 'uas')
            <tr class="row-uas">
                <td style="text-align:center">{{ $row['minggu'] }}</td>
                <td colspan="7">EVALUASI AKHIR SEMESTER (EAS) : Melakukan Validasi Penilaian Akhir dan Menentukan Kelulusan Mahasiswa<br><em style="font-size:8.5pt;font-weight:normal">End-of-Semester Evaluation (ESE) : Conducting Final Grade Validation and Determining Student Graduation</em></td>
            </tr>
            @else
            {{-- Row 1: Indonesian --}}
            <tr>
                <td rowspan="3" style="text-align:center;vertical-align:middle">{{ $row['minggu'] }}</td>
                <td rowspan="2" style="vertical-align:top">
                    @forelse(collect($row['subCpmks']) as $ss)
                    <div style="margin-bottom:2px"><strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi }}</div>
                    @empty
                    –
                    @endforelse
                </td>
                <td rowspan="2" style="vertical-align:top">{{ $row['indikator'] }}</td>
                <td style="vertical-align:top">
                    @if($row['teknik'])<strong>Teknik Non tes:</strong><br>{{ $row['teknik'] }}@else–@endif
                </td>
                <td style="vertical-align:top">{{ $row['metode'] }}</td>
                <td style="vertical-align:top">–</td>
                <td rowspan="2" style="vertical-align:top">{{ $row['materi'] }}</td>
                <td rowspan="3" style="text-align:center;vertical-align:middle">{{ is_numeric($row['bobot']) ? $row['bobot'].'%' : $row['bobot'] }}</td>
            </tr>
            {{-- Row 2: Kriteria --}}
            <tr style="border-top:1px dashed #bbb">
                <td style="vertical-align:top"><em style="color:#aaa">Kriteria: Pedoman Penskoran</em></td>
                <td colspan="2" style="vertical-align:top"></td>
            </tr>
            {{-- Row 3: English italic --}}
            <tr style="border-top:1px dashed #aaa;background:#f0f9ff">
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">
                    @forelse(collect($row['subCpmks']) as $ss)
                    <div style="margin-bottom:2px">@if($ss->deskripsi_en)<strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi_en }}@endif</div>
                    @empty–@endforelse
                </td>
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">–</td>
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">–</td>
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">–</td>
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">–</td>
                <td style="vertical-align:top;font-style:italic;color:#1a5276;font-size:8.5pt">{{ $row['materi'] }}</td>
            </tr>
            @endif
            @endforeach
            @endif
        </table>

    </div>{{-- end rps-doc --}}
</div>{{-- end rps-wrapper --}}

@push('scripts')
<script>
    function printRps() {
        // Clear browser print header/footer by temporarily blanking the page title
        var origTitle = document.title;
        document.title = '';
        window.print();
        // Restore title after print dialog closes
        setTimeout(function() {
            document.title = origTitle;
        }, 1000);
    }
</script>
@endpush

@endsection