@php
$_logoPath = base_path('Bahan/logo_unism.png');
$_logoSrc = file_exists($_logoPath)
? 'data:image/png;base64,' . base64_encode(file_get_contents($_logoPath))
: '';

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
$cpRowspan = 3 + count($cpls) + count($cpmks) + count($subCpmks);
$dosenList = $detail?->dosen_pengampu ?? [];
if (empty($dosenList)) $dosenList = [$h['pjmk']];
$useManual = $pertemuan->count() > 0;

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
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>RPS {{ $h['kode_mk'] }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 18mm 15mm 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 9.5pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 0;
        }

        table td,
        table th {
            border: 0.5pt solid #222;
            padding: 3px 5px;
            vertical-align: top;
        }

        .cell-label {
            font-weight: bold;
            vertical-align: middle;
            background-color: #f5f5f5;
            white-space: nowrap;
            width: 105pt;
        }

        /* Green = #A8D08D */
        .row-green td {
            background-color: #A8D08D;
            font-weight: bold;
            text-align: center;
        }

        .row-uts td,
        .row-uas td {
            background-color: #A8D08D;
            font-weight: bold;
            text-align: center;
        }

        table th {
            background-color: #A8D08D;
            font-weight: bold;
        }

        .gray-head {
            background-color: #d9d9d9 !important;
            font-weight: bold;
        }

        /* Blueprint inner */
        .bp th,
        .bp td {
            border: 0.5pt solid #555;
            padding: 2px 4px;
            font-size: 8pt;
        }

        .bp th {
            background-color: #d9d9d9;
            text-align: center;
        }

        /* RKBM col widths */
        .c-mg {
            width: 4%;
            text-align: center;
        }

        .c-cpmk {
            width: 13%;
        }

        .c-ind {
            width: 13%;
        }

        .c-tek {
            width: 11%;
        }

        .c-sin {
            width: 15%;
        }

        .c-asin {
            width: 12%;
        }

        .c-mat {
            width: 23%;
        }

        .c-bot {
            width: 9%;
            text-align: center;
        }

        /* EN italic row */
        .en-row {
            background-color: #f0f9ff;
            font-style: italic;
            color: #1a5276;
            font-size: 8pt;
        }

        .page-break {
            page-break-before: always;
        }

        p {
            margin: 2px 0;
        }
    </style>
</head>

<body>

    {{-- ══ KOP ══ --}}
    <table style="border: 1.5pt solid #222;">
        <tr>
            <td style="width:80pt; text-align:center; vertical-align:middle; border:none; border-right:0.5pt solid #222;">
                @if($_logoSrc)
                <img src="{{ $_logoSrc }}" alt="Logo UNISM" style="height:65px; width:auto;">
                @else
                <span style="font-size:8pt; color:#999;">LOGO UNISM</span>
                @endif
            </td>
            <td style="text-align:center; vertical-align:middle; border:none;">
                <div style="font-size:13pt; font-weight:bold; letter-spacing:0.3pt;">UNIVERSITAS SARI MULIA</div>
                <div style="font-size:10.5pt; font-weight:bold;">FAKULTAS {{ strtoupper($h['fakultas']) }}</div>
                <div style="font-size:10.5pt; font-weight:bold;">PROGRAM STUDI {{ strtoupper($h['prodi']) }}</div>
                <div style="font-size:10pt;">TAHUN AKADEMIK {{ $h['tahun_akademik'] }}</div>
            </td>
            <td style="width:140pt; text-align:center; vertical-align:middle; background-color:#A8D08D; border:none; border-left:0.5pt solid #222;">
                <div style="font-size:10pt; font-weight:bold; line-height:1.6;">RENCANA<br>PEMBELAJARAN<br>SEMESTER<br>(RPS)</div>
            </td>
        </tr>
    </table>

    {{-- ══ IDENTITAS RPS ══ --}}
    <table>
        <tr class="row-green">
            <td colspan="5" style="text-align:center; font-size:11pt; background-color:#A8D08D;">RENCANA PEMBELAJARAN SEMESTER (RPS)
                <br><em style="font-size:9pt;">Semester Learning Plan</em>
            </td>
        </tr>
        <tr>
            <th class="gray-head" style="text-align:center; background-color:#d9d9d9;">Mata Kuliah (MK)<br><em style="font-size:8pt; font-weight:bold;">Course Name</em></th>
            <th class="gray-head" style="text-align:center; background-color:#d9d9d9;">Kode<br><em style="font-size:8pt; font-weight:bold;">Code</em></th>
            <th class="gray-head" style="text-align:center; background-color:#d9d9d9;">Bobot (SKS)<br><em style="font-size:8pt; font-weight:bold;">Credits</em></th>
            <th class="gray-head" style="text-align:center; background-color:#d9d9d9;">Semester<br><em style="font-size:8pt; font-weight:bold;">Semester</em></th>
            <th class="gray-head" style="text-align:center; background-color:#d9d9d9;">Tanggal Penyusunan<br><em style="font-size:8pt; font-weight:bold;">Preparation Date</em></th>
        </tr>
        <tr>
            <td style="font-weight:bold;">{{ $h['nama_mk'] }}<br>
                <em style="font-size:8.5pt; color:#1a5276;">{{ $h['nama_mk_en'] ?? $rps['mk']->nama_en ?? $h['nama_mk'] }}</em>
            </td>
            <td style="text-align:center;">{{ $h['kode_mk'] }}</td>
            <td style="text-align:center;">
                <strong>{{ $h['sks'] }} SKS</strong><br>
                <em style="font-size:8.5pt;">({{ $h['sks_teori'] ?? $h['sks'] }}T + {{ $h['sks_praktikum'] ?? 0 }}P)</em><br>
                <span style="font-size:8pt; color:#1a5276;"><em>{{ $h['sks'] }} Credits ({{ $h['sks_teori'] ?? $h['sks'] }}T + {{ $h['sks_praktikum'] ?? 0 }}P)</em></span>
            </td>
            <td style="text-align:center;">
                Semester {{ $h['semester'] }}<br>
                <em style="font-size:8.5pt;">({{ ($h['semester'] % 2 == 1) ? 'Ganjil' : 'Genap' }})</em><br>
                <span style="font-size:8pt; color:#1a5276;"><em>Semester {{ $h['semester'] }} ({{ ($h['semester'] % 2 == 1) ? 'Odd' : 'Even' }})</em></span>
            </td>
            <td>{{ $h['tanggal'] }}<br>
                <em style="font-size:8pt; color:#1a5276;">{{ $h['tanggal_en'] ?? '' }}</em>
            </td>
        </tr>
    </table>

    {{-- ══ OTORISASI ══ --}}
    <table>
        <tr>
            <td rowspan="3" class="cell-label" style="width:105pt; text-align:center; vertical-align:middle;">
                OTORISASI /<br>PENGESAHAN<br>
                <em style="font-weight:normal; font-size:8pt; color:#1a5276;">Authorization /<br>Approval</em>
            </td>
            <td style="font-weight:bold; text-align:center; background-color:#A8D08D;">Dosen Pengembang RPS<br>
                <em style="font-size:8pt;">Course Plan Developer</em>
            </td>
            <td style="font-weight:bold; text-align:center; background-color:#A8D08D;">Ketua Program Studi<br>
                <em style="font-size:8pt;">Head of Study Program</em>
            </td>
        </tr>
        <tr>
            <td style="height:70px;">&nbsp;</td>
            <td style="height:70px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="font-size:9pt;">({{ $dosenList[0] ?? '..................................' }})<br>NIK.</td>
            <td style="font-size:9pt;">({{ $h['kaprodi'] }})<br>NIK. {{ $h['nik_kaprodi'] ?? '' }}</td>
        </tr>
    </table>

    {{-- ══ CAPAIAN PEMBELAJARAN ══ --}}
    <table>
        <tr>
            <td rowspan="{{ $cpRowspan }}" class="cell-label" style="text-align:center; vertical-align:middle;">
                <strong>Capaian<br>Pembelajaran</strong><br>
                <em style="font-weight:normal; font-size:7.5pt; color:#1a5276;">Learning<br>Outcomes</em>
            </td>
            <td colspan="2" style="background-color:#d9d9d9; font-weight:bold;">
                Capaian Pembelajaran Lulusan (CPL) yang dibebankan pada MK<br>
                <em style="font-size:8pt;">Graduate Learning Outcomes (PLO) assigned to this Course</em>
            </td>
        </tr>
        @forelse($cpls as $cpl)
        <tr>
            <td style="font-weight:bold; white-space:nowrap; width:90pt;">{{ $cpl->kode }}</td>
            <td>{{ $cpl->deskripsi }}
                @if($cpl->deskripsi_en)
                <br><span style="color:#1a5276; font-style:italic; font-size:8.5pt;">{{ $cpl->deskripsi_en }}</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="2"><em style="color:#999;">Belum ada CPL</em></td>
        </tr>
        @endforelse
        <tr>
            <td colspan="2" style="background-color:#d9d9d9; font-weight:bold;">
                Capaian Pembelajaran Mata Kuliah (CPMK)<br>
                <em style="font-size:8pt;">Course Learning Outcomes (CLO)</em>
            </td>
        </tr>
        @forelse($cpmks as $cpmk)
        <tr>
            <td style="font-weight:bold; white-space:nowrap;">{{ $cpmk->kode }}</td>
            <td>{{ $cpmk->deskripsi }}
                @if($cpmk->deskripsi_en)
                <br><span style="color:#1a5276; font-style:italic; font-size:8.5pt;">{{ $cpmk->deskripsi_en }}</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="2"><em style="color:#999;">Belum ada CPMK</em></td>
        </tr>
        @endforelse
        <tr>
            <td colspan="2" style="background-color:#d9d9d9; font-weight:bold;">
                Kemampuan akhir tiap tahapan belajar <em>(Sub CPMK)</em><br>
                <em style="font-size:8pt;">Expected Learning Outcomes at Each Learning Stage (Sub CLO)</em>
            </td>
        </tr>
        @forelse($subCpmks as $sub)
        <tr>
            <td style="font-weight:bold; white-space:nowrap;">{{ $sub->kode }}</td>
            <td>{{ $sub->deskripsi }}
                @if($sub->deskripsi_en)
                <br><span style="color:#1a5276; font-style:italic; font-size:8.5pt;">{{ $sub->deskripsi_en }}</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="2"><em style="color:#999;">Belum ada Sub CPMK</em></td>
        </tr>
        @endforelse
    </table>

    {{-- ══ DESKRIPSI ══ --}}
    <table>
        <tr>
            <td class="cell-label"><strong>Deskripsi<br>Singkat MK</strong><br><em style="font-weight:normal; font-size:7.5pt; color:#1a5276;">Brief Course<br>Description</em></td>
            <td>{{ $h['deskripsi'] ?: ($rps['mk']->deskripsi ?: '–') }}
                @if($rps['mk']->deskripsi_en)
                <br><span style="color:#1a5276; font-style:italic; font-size:8.5pt;">{{ $rps['mk']->deskripsi_en }}</span>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ BAHAN KAJIAN ══ --}}
    <table>
        <tr>
            <td class="cell-label"><strong>Bahan Kajian</strong><br><em style="font-weight:normal; font-size:7.5pt; color:#1a5276;">Study Materials</em></td>
            <td>
                @forelse($bahanKajians as $i => $bk)
                {{ $i+1 }}. {{ $bk->nama }};<br>
                @empty
                <em style="color:#999;">Belum ada bahan kajian</em>
                @endforelse
            </td>
        </tr>
    </table>

    {{-- ══ PUSTAKA ══ --}}
    <table>
        <tr>
            <td class="cell-label"><strong>Pustaka</strong><br><em style="font-weight:normal; font-size:7.5pt; color:#1a5276;">References</em></td>
            <td>
                @if($referensiU->count())
                <strong>Utama / <em>Main</em>:</strong><br>
                @foreach($referensiU as $i => $ref){{ $i+1 }}. {{ $ref->citation ?? $ref->referensi ?? '' }}<br>@endforeach
                @endif
                @if($referensiP->count())
                <br><strong>Pendukung / <em>Supplementary</em>:</strong><br>
                @foreach($referensiP as $i => $ref){{ $i+1 }}. {{ $ref->citation ?? $ref->referensi ?? '' }}<br>@endforeach
                @endif
                @if($referensiU->isEmpty() && $referensiP->isEmpty())
                <em style="color:#999;">Belum ada referensi</em>
                @endif
            </td>
        </tr>
    </table>

    {{-- ══ BLUEPRINT ══ --}}
    <table>
        <tr>
            <td class="cell-label" style="vertical-align:top;"><strong>Kriteria<br>Penilaian &amp;<br>Blueprint<br>Asesmen</strong><br>
                <em style="font-weight:normal; font-size:7pt; color:#1a5276;">Assessment<br>Criteria &amp;<br>Blueprint</em>
            </td>
            <td>
                <table class="bp" style="width:100%; border-collapse:collapse;">
                    <tr>
                        <th style="width:110pt; background-color:#d9d9d9;">Basis Evaluasi<br><em style="font-size:7pt; font-weight:normal;">Evaluation Basis</em></th>
                        <th style="background-color:#d9d9d9;">Komponen Evaluasi<br><em style="font-size:7pt; font-weight:normal;">Evaluation Component</em></th>
                        <th style="width:80pt; background-color:#d9d9d9;">Sub CPMK/CPMK</th>
                        <th style="background-color:#d9d9d9;">Deskripsi % Indikator</th>
                        <th style="background-color:#d9d9d9;">Metode Penilaian<br><em style="font-size:7pt; font-weight:normal;">Assessment Method</em></th>
                        <th style="width:45pt; text-align:center; background-color:#d9d9d9;">Bobot %</th>
                    </tr>
                    @forelse($blueprint['rows'] as $row)
                    @php
                    $rowCpmkKodes = array_map('trim', explode(',', $row['cpmk']));
                    $rowSubCpmks = $subCpmks->filter(fn($s) => in_array($s->cpmk->kode ?? '', $rowCpmkKodes))->sortBy('kode');
                    @endphp
                    <tr>
                        @if($row['rowspan'] > 0)
                        <td rowspan="{{ $row['rowspan'] }}" style="vertical-align:middle; font-weight:bold;">
                            {{ $row['basis'] }}
                            @if(isset($basisEn[$row['basis']]))
                            <br><span style="font-weight:normal; font-style:italic; color:#1a5276; font-size:7.5pt;">{{ $basisEn[$row['basis']] }}</span>
                            @endif
                        </td>
                        @endif
                        <td>{{ $row['komponen'] }}
                            @if(isset($komponenEn[$row['komponen']]))
                            <br><span style="font-style:italic; color:#1a5276; font-size:7.5pt;">{{ $komponenEn[$row['komponen']] }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($rowSubCpmks->isNotEmpty())
                            @foreach($rowSubCpmks as $rs)
                            <div style="font-size:7.5pt; font-weight:bold;">{{ $rs->kode }}</div>
                            @endforeach
                            @else
                            {{ $row['cpmk'] }}
                            @endif
                        </td>
                        <td>{{ $row['deskripsi'] }}</td>
                        <td>{{ $row['metode'] }}
                            @if(isset($metodeEn[$row['metode']]))
                            <br><span style="font-style:italic; color:#1a5276; font-size:7.5pt;">{{ $metodeEn[$row['metode']] }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;">{{ $row['bobot'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; font-style:italic; color:#999;">Belum ada data blueprint</td>
                    </tr>
                    @endforelse
                    <tr style="background-color:#d9d9d9;">
                        <td colspan="5" style="text-align:right; font-weight:bold;">Total</td>
                        <td style="text-align:center; font-weight:bold;">{{ $blueprint['grandTotal'] ?: 100 }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ══ DOSEN PENGAMPU ══ --}}
    <table>
        <tr>
            <td class="cell-label"><strong>Dosen<br>Pengampu</strong><br><em style="font-weight:normal; font-size:7.5pt; color:#1a5276;">Lecturers</em></td>
            <td>
                @foreach($dosenList as $idx => $d)
                {{ $idx+1 }}. {{ $d }}<br>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ══ MK SYARAT ══ --}}
    <table>
        <tr>
            <td class="cell-label"><strong>Mata Kuliah<br>Syarat</strong><br><em style="font-weight:normal; font-size:7.5pt; color:#1a5276;">Prerequisite Course</em></td>
            <td>{{ $h['prasyarat'] ?? '–' }}</td>
        </tr>
    </table>

    {{-- ══ RKBM ══ --}}
    <br>
    <div class="page-break"></div>
    <p style="font-weight:bold; font-size:11pt; margin:6px 0 3px;"><strong>Rencana Kegiatan Belajar Mengajar</strong><br>
        <em style="font-size:9pt;">Teaching and Learning Activity Plan</em>
    </p>

    <table>
        <colgroup>
            <col class="c-mg">
            <col class="c-cpmk">
            <col class="c-ind">
            <col class="c-tek">
            <col class="c-sin">
            <col class="c-asin">
            <col class="c-mat">
            <col class="c-bot">
        </colgroup>
        <tr>
            <th rowspan="2" style="text-align:center; vertical-align:middle; background-color:#A8D08D;">Mg<br>Ke-<br><em style="font-size:7pt; font-weight:normal;">Week</em></th>
            <th rowspan="2" style="text-align:center; vertical-align:middle; background-color:#A8D08D;">Kemampuan Akhir Tiap Tahapan Belajar (CPMK)<br><em style="font-size:7pt; font-weight:normal;">Expected Learning Outcomes (CLO)</em></th>
            <th colspan="2" style="text-align:center; background-color:#A8D08D;">Penilaian<br><em style="font-size:7pt; font-weight:normal;">Assessment</em></th>
            <th colspan="2" style="text-align:center; background-color:#A8D08D;">Bentuk &amp; Metode Pembelajaran; Penugasan; [Estimasi Waktu]<br><em style="font-size:7pt; font-weight:normal;">Learning Form &amp; Method; Assignment; [Time Estimate]</em></th>
            <th rowspan="2" style="text-align:center; vertical-align:middle; background-color:#A8D08D;">Materi Pembelajaran [Pustaka]<br><em style="font-size:7pt; font-weight:normal;">Learning Materials [References]</em></th>
            <th rowspan="2" style="text-align:center; vertical-align:middle; background-color:#A8D08D;">Bobot (%)<br><em style="font-size:7pt; font-weight:normal;">Weight (%)</em></th>
        </tr>
        <tr>
            <th style="text-align:center; background-color:#A8D08D;">Indikator<br><em style="font-size:7pt; font-weight:normal;">Indicators</em></th>
            <th style="text-align:center; background-color:#A8D08D;">Teknik &amp; Kriteria<br><em style="font-size:7pt; font-weight:normal;">Technique &amp; Criteria</em></th>
            <th style="text-align:center; background-color:#A8D08D;">Tatap Muka / Sinkron<br><em style="font-size:7pt; font-weight:normal;">Face-to-Face / Synchronous</em></th>
            <th style="text-align:center; background-color:#A8D08D;">Pembelajaran Mandiri / Asinkron<br><em style="font-size:7pt; font-weight:normal;">Self-Directed / Asynchronous</em></th>
        </tr>
        <tr style="background-color:#d9d9d9; text-align:center; font-weight:bold;">
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
        <tr>
            <td style="text-align:center; font-weight:bold; background-color:#A8D08D;">8</td>
            <td colspan="7" style="font-weight:bold; text-align:center; background-color:#A8D08D;">UJIAN TENGAH SEMESTER (UTS)<br><em style="font-size:8pt; font-weight:normal;">Mid-Term Examination (MTE)</em></td>
        </tr>
        @elseif((int)$p->minggu === 16)
        <tr>
            <td style="text-align:center; font-weight:bold; background-color:#A8D08D;">16</td>
            <td colspan="7" style="font-weight:bold; text-align:center; background-color:#A8D08D;">EVALUASI AKHIR SEMESTER (EAS): Melakukan Validasi Penilaian Akhir dan Menentukan Kelulusan Mahasiswa<br><em style="font-size:8pt; font-weight:normal;">End-of-Semester Evaluation (ESE): Conducting Final Grade Validation and Determining Student Graduation</em></td>
        </tr>
        @else
        @php
        $selSubs = $p->sub_cpmk_ids && count($p->sub_cpmk_ids) > 0
        ? $subCpmks->whereIn('id', $p->sub_cpmk_ids)->sortBy('kode')->values()
        : collect();
        @endphp
        {{-- Row 1: ID content --}}
        <tr>
            <td rowspan="3" style="text-align:center; vertical-align:middle; font-weight:bold;">{{ $p->minggu }}</td>
            <td rowspan="2" style="vertical-align:top;">
                @if($selSubs->count())
                @foreach($selSubs as $ss)
                <div style="margin-bottom:2px;"><strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi }}</div>
                @endforeach
                @else
                {{ $p->cpmk_label ?: '–' }}
                @endif
            </td>
            <td rowspan="2" style="vertical-align:top;">{{ $p->indikator ?: '–' }}</td>
            <td rowspan="2" style="vertical-align:top;">
                @if($p->teknik_penilaian)<strong>Teknik Non tes:</strong><br>{{ $p->teknik_penilaian }}@else–@endif
                @if($p->kreteria)<br><strong>Kriteria:</strong><br><em>{{ $p->kreteria }}</em>@endif
            </td>
            <td style="vertical-align:top;">{{ $p->metode_sinkron ?: '–' }}</td>
            <td style="vertical-align:top;">{{ $p->metode_asinkron ?: '–' }}</td>
            <td rowspan="2" style="vertical-align:top;">{{ $p->materi ?: '–' }}</td>
            <td rowspan="3" style="text-align:center; vertical-align:middle;">{{ $p->bobot > 0 ? $p->bobot.'%' : '–' }}</td>
        </tr>
        {{-- Row 2: tugas ID --}}
        <tr>
            <td colspan="2" style="vertical-align:top;">@if($p->tugas)<strong>{{ $p->tugas }}</strong>@endif</td>
        </tr>
        {{-- Row 3: EN italic --}}
        <tr style="background-color:#f0f9ff;">
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">
                @foreach($selSubs as $ss)
                @if($ss->deskripsi_en)<div><strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi_en }}</div>@endif
                @endforeach
            </td>
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">{{ $p->indikator_en ?: '' }}</td>
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">
                @if($p->teknik_penilaian_en)<em>Non-test:</em><br>{{ $p->teknik_penilaian_en }}@endif
                @if($p->kreteria_en)<br><em>Criteria:</em><br>{{ $p->kreteria_en }}@endif
            </td>
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">{{ $p->metode_sinkron_en ?: '' }}</td>
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">{{ $p->metode_asinkron_en ?: '' }}</td>
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">{{ $p->materi_en ?: '' }}</td>
        </tr>
        @endif
        @endforeach
        @else
        @foreach($jadwal as $row)
        @if($row['type'] === 'uts')
        <tr>
            <td style="text-align:center; font-weight:bold; background-color:#A8D08D;">{{ $row['minggu'] }}</td>
            <td colspan="7" style="font-weight:bold; text-align:center; background-color:#A8D08D;">UJIAN TENGAH SEMESTER (UTS)<br><em style="font-size:8pt; font-weight:normal;">Mid-Term Examination (MTE)</em></td>
        </tr>
        @elseif($row['type'] === 'uas')
        <tr>
            <td style="text-align:center; font-weight:bold; background-color:#A8D08D;">{{ $row['minggu'] }}</td>
            <td colspan="7" style="font-weight:bold; text-align:center; background-color:#A8D08D;">EVALUASI AKHIR SEMESTER (EAS): Melakukan Validasi Penilaian Akhir dan Menentukan Kelulusan Mahasiswa<br><em style="font-size:8pt; font-weight:normal;">End-of-Semester Evaluation (ESE)</em></td>
        </tr>
        @else
        <tr>
            <td rowspan="2" style="text-align:center; vertical-align:middle; font-weight:bold;">{{ $row['minggu'] }}</td>
            <td rowspan="2" style="vertical-align:top;">
                @forelse(collect($row['subCpmks']) as $ss)
                <div><strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi }}</div>
                @empty –
                @endforelse
            </td>
            <td rowspan="2" style="vertical-align:top;">{{ $row['indikator'] }}</td>
            <td style="vertical-align:top;">@if($row['teknik'])<strong>Teknik Non tes:</strong><br>{{ $row['teknik'] }}@else–@endif</td>
            <td style="vertical-align:top;">{{ $row['metode'] }}</td>
            <td style="vertical-align:top;">–</td>
            <td rowspan="2" style="vertical-align:top;">{{ $row['materi'] }}</td>
            <td rowspan="2" style="text-align:center; vertical-align:middle;">{{ is_numeric($row['bobot']) ? $row['bobot'].'%' : $row['bobot'] }}</td>
        </tr>
        <tr style="background-color:#f0f9ff;">
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">
                @forelse(collect($row['subCpmks']) as $ss)
                @if($ss->deskripsi_en)<div><strong>{{ $ss->kode }}</strong>: {{ $ss->deskripsi_en }}</div>@endif
                @empty @endforelse
            </td>
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">–</td>
            <td colspan="2" style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">–</td>
            <td style="vertical-align:top; font-style:italic; color:#1a5276; font-size:8pt;">{{ $row['materi'] }}</td>
        </tr>
        @endif
        @endforeach
        @endif
    </table>

</body>

</html>