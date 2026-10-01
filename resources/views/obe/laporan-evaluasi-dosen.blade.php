<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan Evaluasi Dosen {{ $ta }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 10pt;
      color: #1f2937;
      background: #fff;
    }

    /* ── Page utilities ── */
    .page-break {
      page-break-before: always;
      padding-top: 20px;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    /* ── Cover ── */
    .cover {
      text-align: center;
      padding: 60px 40px;
      border: 3px double #1e40af;
      margin: 20px;
    }

    .cover-logo {
      width: 80px;
      height: 80px;
      background: #1e40af;
      border-radius: 50%;
      display: inline-block;
      line-height: 80px;
      font-size: 28px;
      color: #fff;
      margin-bottom: 20px;
    }

    .cover-title {
      font-size: 22pt;
      font-weight: bold;
      color: #1e40af;
      margin-bottom: 8px;
    }

    .cover-subtitle {
      font-size: 13pt;
      color: #374151;
      margin-bottom: 6px;
    }

    .cover-uni {
      font-size: 11pt;
      color: #6b7280;
    }

    .cover-ta {
      font-size: 14pt;
      font-weight: bold;
      color: #7c3aed;
      margin: 14px 0;
    }

    .cover-divider {
      border: none;
      border-top: 2px solid #1e40af;
      margin: 16px auto;
      width: 60%;
    }

    .cover-meta {
      font-size: 9pt;
      color: #6b7280;
      margin-top: 20px;
    }

    /* ── Headings ── */
    .section-header {
      background: #1e40af;
      color: #fff;
      font-size: 12pt;
      font-weight: bold;
      padding: 8px 16px;
      border-radius: 4px;
      margin: 0 0 12px;
    }

    .sub-header {
      background: #eff6ff;
      color: #1e40af;
      font-size: 10pt;
      font-weight: bold;
      padding: 6px 12px;
      border-left: 4px solid #1e40af;
      margin: 12px 0 8px;
    }

    .sub-header-purple {
      background: #f5f3ff;
      color: #5b21b6;
      font-size: 10pt;
      font-weight: bold;
      padding: 6px 12px;
      border-left: 4px solid #7c3aed;
      margin: 12px 0 8px;
    }

    /* ── Tables ── */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
      font-size: 9pt;
    }

    th {
      background: #1e40af;
      color: #fff;
      padding: 6px 8px;
      text-align: left;
    }

    th.c {
      text-align: center;
    }

    td {
      padding: 5px 8px;
      border: 1px solid #e5e7eb;
      vertical-align: top;
    }

    tr:nth-child(even) td {
      background: #f8fafc;
    }

    /* ── Score bar (dompdf CSS-only chart) ── */
    .bar-wrap {
      width: 100%;
      background: #e5e7eb;
      border-radius: 3px;
      height: 14px;
    }

    .bar-fill {
      height: 14px;
      border-radius: 3px;
    }

    .bar-blue {
      background: #1e40af;
    }

    .bar-green {
      background: #059669;
    }

    .bar-yellow {
      background: #d97706;
    }

    .bar-pink {
      background: #9d174d;
    }

    .bar-purple {
      background: #7c3aed;
    }

    /* ── Kategori badges ── */
    .badge {
      display: inline-block;
      padding: 1px 8px;
      border-radius: 99px;
      font-size: 8pt;
      font-weight: bold;
    }

    .badge-sk {
      background: #dcfce7;
      color: #166534;
    }

    .badge-k {
      background: #dbeafe;
      color: #1e40af;
    }

    .badge-c {
      background: #fef3c7;
      color: #92400e;
    }

    .badge-ku {
      background: #fee2e2;
      color: #991b1b;
    }

    /* ── Info box ── */
    .info-box {
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 10px 14px;
      margin: 8px 0;
    }

    .static-text {
      font-size: 9.5pt;
      color: #374151;
      line-height: 1.65;
      margin: 8px 0;
    }

    /* ── Two-column ── */
    .two-col {
      width: 100%;
      border-collapse: collapse;
    }

    .two-col td {
      border: none;
      width: 50%;
      vertical-align: top;
      padding: 0 8px 0 0;
    }

    /* ── Footer ── */
    .footer {
      font-size: 7.5pt;
      color: #9ca3af;
      text-align: right;
      margin-top: 12px;
      border-top: 1px solid #e5e7eb;
      padding-top: 5px;
    }

    /* ── Dosen card ── */
    .dosen-header {
      background: #f5f3ff;
      border: 1.5px solid #c4b5fd;
      border-radius: 6px;
      padding: 10px 14px;
      margin: 14px 0 8px;
    }

    .dosen-header h3 {
      font-size: 11pt;
      font-weight: bold;
      color: #5b21b6;
      margin: 0 0 2px;
    }

    .dosen-header p {
      font-size: 8pt;
      color: #7c3aed;
      margin: 0;
    }

    /* ── Bullet list ── */
    .feedback-item {
      border-left: 3px solid #c4b5fd;
      padding: 5px 10px;
      margin: 4px 0;
      background: #faf5ff;
      font-size: 8.5pt;
    }

    .overall-card {
      border: 1.5px solid #1e40af;
      border-radius: 6px;
      padding: 14px;
      text-align: center;
    }

    .overall-val {
      font-size: 24pt;
      font-weight: bold;
      color: #1e40af;
    }
  </style>
</head>

<body>

  {{-- ══════════════════════════════════════════════════ --}}
  {{-- COVER --}}
  {{-- ══════════════════════════════════════════════════ --}}
  <div style="min-height:520px; display:table; width:100%;">
    <div style="display:table-cell; vertical-align:middle;">
      <div class="cover">
        <div class="cover-logo">👨‍🏫</div>
        <div class="cover-title">LAPORAN EVALUASI DOSEN</div>
        <div class="cover-subtitle">Berdasarkan Penilaian Mahasiswa</div>
        <hr class="cover-divider">
        <div class="cover-ta">Tahun Akademik {{ $ta }}</div>
        <div class="cover-uni">Program Studi Sistem Informasi (S1)</div>
        <div class="cover-uni">Fakultas Sains dan Teknologi</div>
        <div class="cover-uni" style="font-weight:bold; color:#1e40af;">Universitas Sari Mulia (UNISM) Banjarmasin</div>
        <div class="cover-meta" style="margin-top:24px;">
          Digenerate pada: {{ $generatedAt }}<br>
          Diketahui oleh: {{ $kaprodi->name ?? 'Ketua Program Studi' }}<br>
          Ketua Program Studi Sistem Informasi
        </div>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════ --}}
  {{-- BAB I — PENDAHULUAN --}}
  {{-- ══════════════════════════════════════════════════ --}}
  <div class="page-break">
    <div class="section-header">BAB I — PENDAHULUAN</div>

    <div class="sub-header">A. Latar Belakang</div>
    <p class="static-text">
      Evaluasi kinerja dosen merupakan bagian integral dari sistem penjaminan mutu internal Program Studi
      Sistem Informasi Universitas Sari Mulia (UNISM) Banjarmasin. Sesuai dengan amanat Permendikbudristek
      No. 53 Tahun 2023 dan standar IAPS 4.0 LAM-INFOKOM, setiap program studi diwajibkan melaksanakan
      evaluasi pembelajaran secara komprehensif, termasuk penilaian terhadap kompetensi dosen oleh mahasiswa.
    </p>
    <p class="static-text">
      Instrumen penilaian yang digunakan mengukur empat dimensi kompetensi dosen yang telah ditetapkan oleh
      pemerintah, yaitu: (1) Kompetensi Pedagogik — kemampuan mengelola pembelajaran, (2) Kompetensi
      Profesional — penguasaan materi bidang ilmu, (3) Kompetensi Kepribadian — integritas dan
      profesionalisme personal, serta (4) Kompetensi Sosial — kemampuan interaksi dengan mahasiswa
      dan lingkungan akademik.
    </p>

    <div class="sub-header">B. Tujuan</div>
    <p class="static-text">Laporan ini bertujuan untuk:</p>
    <div class="info-box">
      <table style="border:none; font-size:9pt;">
        <tr>
          <td style="border:none; width:3%; padding:2px 6px;">1.</td>
          <td style="border:none; padding:2px 6px;">Memberikan gambaran umum tingkat kompetensi dosen Program Studi Sistem Informasi berdasarkan persepsi mahasiswa pada TA {{ $ta }}.</td>
        </tr>
        <tr>
          <td style="border:none; padding:2px 6px;">2.</td>
          <td style="border:none; padding:2px 6px;">Mengidentifikasi kekuatan dan area pengembangan masing-masing dosen berdasarkan empat kompetensi yang diukur.</td>
        </tr>
        <tr>
          <td style="border:none; padding:2px 6px;">3.</td>
          <td style="border:none; padding:2px 6px;">Menyediakan data dasar bagi pimpinan untuk pengambilan keputusan terkait pengembangan SDM dosen.</td>
        </tr>
        <tr>
          <td style="border:none; padding:2px 6px;">4.</td>
          <td style="border:none; padding:2px 6px;">Mendokumentasikan masukan mahasiswa (kritik dan saran) sebagai bahan evaluasi kurikulum dan metode pembelajaran.</td>
        </tr>
      </table>
    </div>

    <div class="sub-header">C. Skala Penilaian dan Kategori</div>
    <table>
      <tr>
        <th style="width:15%">Skala Likert</th>
        <th style="width:20%">Deskripsi</th>
        <th style="width:20%">Konversi (%)</th>
        <th style="width:20%">Kategori</th>
        <th>Keterangan</th>
      </tr>
      <tr>
        <td class="text-center">3</td>
        <td>Baik</td>
        <td class="text-center">84–100%</td>
        <td><span class="badge badge-sk">Sangat Kompeten</span></td>
        <td>Dosen telah memenuhi standar kompetensi dengan sangat baik</td>
      </tr>
      <tr>
        <td class="text-center">2–3</td>
        <td>Cukup–Baik</td>
        <td class="text-center">66–83%</td>
        <td><span class="badge badge-k">Kompeten</span></td>
        <td>Dosen memenuhi standar kompetensi minimum</td>
      </tr>
      <tr>
        <td class="text-center">2</td>
        <td>Cukup</td>
        <td class="text-center">48–65%</td>
        <td><span class="badge badge-c">Cukup</span></td>
        <td>Perlu pengembangan di beberapa aspek</td>
      </tr>
      <tr>
        <td class="text-center">1</td>
        <td>Kurang</td>
        <td class="text-center">&lt;48%</td>
        <td><span class="badge badge-ku">Kurang</span></td>
        <td>Diperlukan intervensi dan pembinaan intensif</td>
      </tr>
    </table>
    <p class="static-text" style="font-size:8.5pt; color:#6b7280; font-style:italic;">
      *) Konversi: Pct = ((Rata-rata Likert - 1) / 2) × 100
    </p>

    <div class="footer">Laporan Evaluasi Dosen | TA {{ $ta }} | {{ $generatedAt }}</div>
  </div>

  {{-- ══════════════════════════════════════════════════ --}}
  {{-- BAB II — REKAP NILAI & GRAFIK --}}
  {{-- ══════════════════════════════════════════════════ --}}
  <div class="page-break">
    <div class="section-header">BAB II — REKAP NILAI DOSEN PER MATA KULIAH</div>

    @if($rekap->isEmpty())
    <div class="info-box" style="text-align:center; color:#6b7280; padding:24px;">
      <p style="font-size:11pt;">Belum ada data evaluasi dosen untuk TA {{ $ta }}.</p>
      <p style="font-size:9pt; margin-top:8px;">Data akan tersedia setelah mahasiswa mengisi form Penilaian Dosen (/bap-penilaian).</p>
    </div>
    @else

    {{-- Overall summary cards (table-based for dompdf) --}}
    @php
    $kat = $overallKat['kategori'];
    $badgeCls = match($kat) {
    'Sangat Kompeten' => 'badge-sk',
    'Kompeten' => 'badge-k',
    'Cukup' => 'badge-c',
    default => 'badge-ku',
    };
    @endphp
    <div class="info-box" style="margin-bottom:14px;">
      <table style="border:none; width:100%;">
        <tr>
          <td style="border:none; text-align:center; padding:8px;">
            <div style="font-size:8pt; color:#6b7280; font-weight:bold; margin-bottom:4px;">Avg Pedagogik</div>
            <div style="font-size:18pt; font-weight:bold; color:#1e40af;">{{ $overallAvg['pedagogik'] }}</div>
            <div style="font-size:7.5pt; color:#6b7280;">{{ round(($overallAvg['pedagogik']-1)/2*100,1) }}%</div>
          </td>
          <td style="border:none; text-align:center; padding:8px;">
            <div style="font-size:8pt; color:#6b7280; font-weight:bold; margin-bottom:4px;">Avg Profesional</div>
            <div style="font-size:18pt; font-weight:bold; color:#059669;">{{ $overallAvg['profesional'] }}</div>
            <div style="font-size:7.5pt; color:#6b7280;">{{ round(($overallAvg['profesional']-1)/2*100,1) }}%</div>
          </td>
          <td style="border:none; text-align:center; padding:8px;">
            <div style="font-size:8pt; color:#6b7280; font-weight:bold; margin-bottom:4px;">Avg Kepribadian</div>
            <div style="font-size:18pt; font-weight:bold; color:#d97706;">{{ $overallAvg['kepribadian'] }}</div>
            <div style="font-size:7.5pt; color:#6b7280;">{{ round(($overallAvg['kepribadian']-1)/2*100,1) }}%</div>
          </td>
          <td style="border:none; text-align:center; padding:8px;">
            <div style="font-size:8pt; color:#6b7280; font-weight:bold; margin-bottom:4px;">Avg Sosial</div>
            <div style="font-size:18pt; font-weight:bold; color:#9d174d;">{{ $overallAvg['sosial'] }}</div>
            <div style="font-size:7.5pt; color:#6b7280;">{{ round(($overallAvg['sosial']-1)/2*100,1) }}%</div>
          </td>
          <td style="border:none; text-align:center; padding:8px; background:#f5f3ff; border-radius:6px;">
            <div style="font-size:8pt; color:#5b21b6; font-weight:bold; margin-bottom:4px;">Rata-rata Umum</div>
            <div style="font-size:22pt; font-weight:bold; color:#5b21b6;">{{ $overallAvg['total'] }}</div>
            <div><span class="badge {{ $badgeCls }}">{{ $overallKat['kategori'] }}</span></div>
          </td>
        </tr>
      </table>
    </div>

    {{-- Bar chart (CSS-only, dompdf-safe) --}}
    <div class="sub-header">Grafik Rata-rata Kompetensi (Keseluruhan)</div>
    @php
    $chartItems = [
    ['label'=>'Pedagogik', 'val'=>$overallAvg['pedagogik'], 'cls'=>'bar-blue'],
    ['label'=>'Profesional', 'val'=>$overallAvg['profesional'], 'cls'=>'bar-green'],
    ['label'=>'Kepribadian', 'val'=>$overallAvg['kepribadian'], 'cls'=>'bar-yellow'],
    ['label'=>'Sosial', 'val'=>$overallAvg['sosial'], 'cls'=>'bar-pink'],
    ['label'=>'Total', 'val'=>$overallAvg['total'], 'cls'=>'bar-purple'],
    ];
    @endphp
    <div style="padding:8px 0;">
      @foreach($chartItems as $ci)
      @php $pct = round(($ci['val']-1)/2*100, 1); $w = max(2, $pct); @endphp
      <table style="border:none; margin-bottom:6px; width:100%;">
        <tr>
          <td style="border:none; width:15%; font-size:8.5pt; font-weight:bold; color:#374151; padding:0 8px 0 0;">{{ $ci['label'] }}</td>
          <td style="border:none; width:65%; padding:0;">
            <div class="bar-wrap">
              <div class="bar-fill {{ $ci['cls'] }}" style="width:{{ $w }}%;"></div>
            </div>
          </td>
          <td style="border:none; width:10%; text-align:right; font-size:8.5pt; font-weight:bold; color:#374151; padding:0 0 0 8px;">{{ $ci['val'] }}</td>
          <td style="border:none; width:10%; text-align:right; font-size:8pt; color:#6b7280; padding:0 0 0 4px;">{{ $pct }}%</td>
        </tr>
      </table>
      @endforeach
    </div>

    {{-- Detail table per dosen per MK --}}
    <div class="sub-header">Tabel Rekap Per Dosen Per Mata Kuliah</div>
    <table>
      <tr>
        <th class="c" style="width:4%">No</th>
        <th style="width:22%">Dosen</th>
        <th style="width:18%">Mata Kuliah</th>
        <th class="c" style="width:6%">SKS</th>
        <th class="c" style="width:7%">Resp.</th>
        <th class="c" style="width:8%">Pedagogik</th>
        <th class="c" style="width:9%">Profesional</th>
        <th class="c" style="width:9%">Kepribadian</th>
        <th class="c" style="width:7%">Sosial</th>
        <th class="c" style="width:7%">Avg</th>
        <th class="c" style="width:13%">Kategori</th>
      </tr>
      @foreach($rekap as $i => $r)
      @php
      $kat = $r->detail_total['kategori'];
      $bc = match($kat) {'Sangat Kompeten'=>'badge-sk','Kompeten'=>'badge-k','Cukup'=>'badge-c',default=>'badge-ku'};
      @endphp
      <tr>
        <td class="text-center">{{ $i+1 }}</td>
        <td><strong>{{ $r->dosen_nama }}</strong>@if($r->dosen_nik)<br><span style="font-size:7.5pt;color:#6b7280">NIK: {{ $r->dosen_nik }}</span>@endif</td>
        <td>{{ $r->mk_kode }}<br><span style="font-size:7.5pt;color:#6b7280">{{ $r->mk_nama }}</span></td>
        <td class="text-center">{{ $r->sks }}</td>
        <td class="text-center">{{ $r->total_responden }}</td>
        <td class="text-center" style="color:#1e40af;font-weight:bold;">{{ $r->avg_pedagogik }}</td>
        <td class="text-center" style="color:#059669;font-weight:bold;">{{ $r->avg_profesional }}</td>
        <td class="text-center" style="color:#d97706;font-weight:bold;">{{ $r->avg_kepribadian }}</td>
        <td class="text-center" style="color:#9d174d;font-weight:bold;">{{ $r->avg_sosial }}</td>
        <td class="text-center" style="color:#5b21b6;font-weight:bold;">{{ $r->avg_total }}</td>
        <td class="text-center"><span class="badge {{ $bc }}">{{ $kat }}</span></td>
      </tr>
      @endforeach
    </table>

    @endif
    <div class="footer">Laporan Evaluasi Dosen | TA {{ $ta }} | {{ $generatedAt }}</div>
  </div>

  {{-- ══════════════════════════════════════════════════ --}}
  {{-- BAB III — KRITIK DAN SARAN --}}
  {{-- ══════════════════════════════════════════════════ --}}
  <div class="page-break">
    <div class="section-header">BAB III — KRITIK DAN SARAN MAHASISWA</div>
    <p class="static-text">
      Berikut ini adalah rekap kritik dan saran yang disampaikan mahasiswa pada pertemuan ke-16 (pertemuan terakhir)
      untuk setiap mata kuliah. Masukan ini merupakan bagian penting dalam proses evaluasi pembelajaran dan
      pengembangan kompetensi dosen secara berkelanjutan.
    </p>

    @if($feedbackByDosen->isEmpty())
    <div class="info-box" style="text-align:center; color:#6b7280; padding:24px;">
      Belum ada kritik/saran yang tercatat untuk TA {{ $ta }}.
      Data akan tersedia setelah pertemuan ke-16 dengan token evaluasi.
    </div>
    @else
    @foreach($feedbackByDosen as $dosenId => $mkGroups)
    @php $firstRow = $mkGroups->first()->first(); @endphp
    <div class="dosen-header">
      <h3>👨‍🏫 {{ $firstRow->dosen_nama }}</h3>
    </div>
    @foreach($mkGroups as $mkKode => $items)
    <div class="sub-header-purple">📚 {{ $mkKode }} — {{ $items->first()->mk_nama }}</div>
    <table>
      <tr>
        <th class="c" style="width:5%">No</th>
        <th style="width:47.5%">Kritik</th>
        <th style="width:47.5%">Saran</th>
      </tr>
      @foreach($items as $idx => $fb)
      <tr>
        <td class="text-center">{{ $idx+1 }}</td>
        <td>{{ $fb->kritik ?: '—' }}</td>
        <td>{{ $fb->saran ?: '—' }}</td>
      </tr>
      @endforeach
    </table>
    @endforeach
    @endforeach
    @endif

    <div class="footer">Laporan Evaluasi Dosen | TA {{ $ta }} | {{ $generatedAt }}</div>
  </div>

  {{-- ══════════════════════════════════════════════════ --}}
  {{-- BAB IV — DETAIL PER DOSEN --}}
  {{-- ══════════════════════════════════════════════════ --}}
  @if($rekapByDosen->isNotEmpty())
  @foreach($rekapByDosen as $dosenId => $dosenRows)
  @php
  $dosen = $dosenRows->first();
  $avgPed = round($dosenRows->avg('avg_pedagogik'), 2);
  $avgPro = round($dosenRows->avg('avg_profesional'), 2);
  $avgKep = round($dosenRows->avg('avg_kepribadian'), 2);
  $avgSos = round($dosenRows->avg('avg_sosial'), 2);
  $avgTot = round(($avgPed+$avgPro+$avgKep+$avgSos)/4, 2);
  $pctTot = round(($avgTot-1)/2*100, 1);
  $katTot = match(true) { $pctTot>=84=>'Sangat Kompeten', $pctTot>=66=>'Kompeten', $pctTot>=48=>'Cukup', default=>'Kurang' };
  $bcTot = match($katTot) {'Sangat Kompeten'=>'badge-sk','Kompeten'=>'badge-k','Cukup'=>'badge-c',default=>'badge-ku'};
  $totalResp = $dosenRows->sum('total_responden');
  @endphp
  <div class="page-break">
    <div class="section-header">BAB IV — DETAIL DOSEN: {{ strtoupper($dosen->dosen_nama) }}</div>

    {{-- Dosen identity --}}
    <div class="info-box" style="margin-bottom:12px;">
      <table style="border:none;">
        <tr>
          <td style="border:none; width:15%; font-size:8.5pt; color:#6b7280;">Nama Dosen</td>
          <td style="border:none; width:35%; font-weight:bold;">{{ $dosen->dosen_nama }}</td>
          <td style="border:none; width:15%; font-size:8.5pt; color:#6b7280;">NIK</td>
          <td style="border:none; width:35%;">{{ $dosen->dosen_nik ?: '—' }}</td>
        </tr>
        <tr>
          <td style="border:none; font-size:8.5pt; color:#6b7280;">Total Responden</td>
          <td style="border:none; font-weight:bold;">{{ $totalResp }} mahasiswa</td>
          <td style="border:none; font-size:8.5pt; color:#6b7280;">Kategori Umum</td>
          <td style="border:none;"><span class="badge {{ $bcTot }}">{{ $katTot }}</span></td>
        </tr>
      </table>
    </div>

    {{-- Overall bar chart for this dosen --}}
    <div class="sub-header">A. Profil Kompetensi Keseluruhan</div>
    @php
    $dosenBars = [
    ['label'=>'Pedagogik', 'val'=>$avgPed, 'cls'=>'bar-blue'],
    ['label'=>'Profesional', 'val'=>$avgPro, 'cls'=>'bar-green'],
    ['label'=>'Kepribadian', 'val'=>$avgKep, 'cls'=>'bar-yellow'],
    ['label'=>'Sosial', 'val'=>$avgSos, 'cls'=>'bar-pink'],
    ];
    @endphp
    <div style="padding:6px 0 10px;">
      @foreach($dosenBars as $db)
      @php $dbPct = round(($db['val']-1)/2*100, 1); $dbW = max(2, $dbPct); @endphp
      @php $dbKat = match(true) { $dbPct>=84=>'Sangat Kompeten', $dbPct>=66=>'Kompeten', $dbPct>=48=>'Cukup', default=>'Kurang' }; @endphp
      @php $dbBc = match($dbKat) {'Sangat Kompeten'=>'badge-sk','Kompeten'=>'badge-k','Cukup'=>'badge-c',default=>'badge-ku'}; @endphp
      <table style="border:none; margin-bottom:5px; width:100%;">
        <tr>
          <td style="border:none; width:14%; font-size:8.5pt; font-weight:bold; padding:0 8px 0 0;">{{ $db['label'] }}</td>
          <td style="border:none; width:50%; padding:0;">
            <div class="bar-wrap">
              <div class="bar-fill {{ $db['cls'] }}" style="width:{{ $dbW }}%;"></div>
            </div>
          </td>
          <td style="border:none; width:8%; text-align:right; font-size:8.5pt; font-weight:bold; padding:0 6px;">{{ $db['val'] }}</td>
          <td style="border:none; width:9%; font-size:7.5pt; color:#6b7280; padding:0 6px;">{{ $dbPct }}%</td>
          <td style="border:none; width:19%;"><span class="badge {{ $dbBc }}">{{ $dbKat }}</span></td>
        </tr>
      </table>
      @endforeach
    </div>

    {{-- Per MK detail table --}}
    <div class="sub-header">B. Detail Per Mata Kuliah</div>
    <table>
      <tr>
        <th style="width:20%">Mata Kuliah</th>
        <th class="c" style="width:7%">SKS</th>
        <th class="c" style="width:7%">Resp.</th>
        <th class="c" style="width:12%">Pedagogik</th>
        <th class="c" style="width:12%">Profesional</th>
        <th class="c" style="width:12%">Kepribadian</th>
        <th class="c" style="width:10%">Sosial</th>
        <th class="c" style="width:10%">Avg</th>
        <th class="c" style="width:10%">Kategori</th>
      </tr>
      @foreach($dosenRows as $r)
      @php
      $rKat = $r->detail_total['kategori'];
      $rBc = match($rKat) {'Sangat Kompeten'=>'badge-sk','Kompeten'=>'badge-k','Cukup'=>'badge-c',default=>'badge-ku'};
      @endphp
      <tr>
        <td><strong>{{ $r->mk_kode }}</strong><br><span style="font-size:7.5pt;color:#6b7280">{{ $r->mk_nama }}</span></td>
        <td class="text-center">{{ $r->sks }}</td>
        <td class="text-center">{{ $r->total_responden }}</td>
        <td class="text-center" style="color:#1e40af;font-weight:bold;">
          {{ $r->avg_pedagogik }}<br>
          <span style="font-size:7pt;color:#6b7280;">{{ $r->detail_pedagogik['pct'] }}% · <span style="color:#1e40af;">{{ $r->detail_pedagogik['kategori'] }}</span></span>
        </td>
        <td class="text-center" style="color:#059669;font-weight:bold;">
          {{ $r->avg_profesional }}<br>
          <span style="font-size:7pt;color:#6b7280;">{{ $r->detail_profesional['pct'] }}%</span>
        </td>
        <td class="text-center" style="color:#d97706;font-weight:bold;">
          {{ $r->avg_kepribadian }}<br>
          <span style="font-size:7pt;color:#6b7280;">{{ $r->detail_kepribadian['pct'] }}%</span>
        </td>
        <td class="text-center" style="color:#9d174d;font-weight:bold;">
          {{ $r->avg_sosial }}<br>
          <span style="font-size:7pt;color:#6b7280;">{{ $r->detail_sosial['pct'] }}%</span>
        </td>
        <td class="text-center" style="color:#5b21b6;font-weight:bold;">{{ $r->avg_total }}</td>
        <td class="text-center"><span class="badge {{ $rBc }}">{{ $rKat }}</span></td>
      </tr>
      @endforeach
    </table>

    {{-- Kritik & Saran for this dosen --}}
    @if(isset($feedbackByDosen[$dosenId]) && $feedbackByDosen[$dosenId]->isNotEmpty())
    <div class="sub-header-purple" style="margin-top:14px;">C. Kritik dan Saran Mahasiswa</div>
    @foreach($feedbackByDosen[$dosenId] as $mkKode => $feedbacks)
    <p style="font-size:8.5pt; font-weight:bold; color:#374151; margin:6px 0 4px;">📚 {{ $mkKode }}</p>
    @foreach($feedbacks as $fb)
    @if(!empty($fb->kritik))
    <div class="feedback-item"><strong>Kritik:</strong> {{ $fb->kritik }}</div>
    @endif
    @if(!empty($fb->saran))
    <div class="feedback-item" style="border-left-color:#6ee7b7; background:#f0fdf4;"><strong>Saran:</strong> {{ $fb->saran }}</div>
    @endif
    @endforeach
    @endforeach
    @endif

    <div class="footer">Laporan Evaluasi Dosen — {{ $dosen->dosen_nama }} | TA {{ $ta }} | {{ $generatedAt }}</div>
  </div>
  @endforeach
  @endif

  {{-- ══════════════════════════════════════════════════ --}}
  {{-- PENUTUP --}}
  {{-- ══════════════════════════════════════════════════ --}}
  <div class="page-break">
    <div class="section-header">PENUTUP</div>
    <p class="static-text">
      Laporan evaluasi dosen Program Studi Sistem Informasi Tahun Akademik {{ $ta }} ini disusun
      berdasarkan data penilaian mahasiswa yang terkumpul melalui Sistem Informasi OBE UNISM Banjarmasin.
      @if($rekap->isNotEmpty())
      Hasil evaluasi menunjukkan bahwa dari {{ $rekapByDosen->count() }} dosen yang dievaluasi,
      rata-rata capaian kompetensi keseluruhan adalah <strong>{{ $overallAvg['total'] }}</strong>
      ({{ round(($overallAvg['total']-1)/2*100,1) }}%) dengan kategori
      <strong>{{ $overallKat['kategori'] }}</strong>.
      @endif
    </p>
    <p class="static-text" style="margin-top:8px;">
      Hasil evaluasi ini diharapkan dapat menjadi bahan refleksi bagi seluruh dosen dalam meningkatkan
      kualitas pembelajaran dan memperkuat kompetensi di bidang masing-masing. Program Studi berkomitmen
      untuk menindaklanjuti temuan evaluasi ini melalui program pengembangan SDM dosen secara berkala.
    </p>

    <div style="margin-top:40px;">
      <table style="border:none; width:60%; margin:0 auto;">
        <tr>
          <td style="border:none; text-align:center; padding:10px;">
            <p style="font-size:9pt; color:#374151;">Banjarmasin, {{ now()->format('d F Y') }}</p>
            <p style="font-size:9pt; color:#374151; margin-top:8px;">Ketua Program Studi Sistem Informasi</p>
            <div style="margin:40px 0 8px; border-bottom:1px solid #374151; width:160px; margin-left:auto; margin-right:auto;"></div>
            <p style="font-size:9pt; font-weight:bold; color:#1e40af;">{{ $kaprodi->name ?? 'Nor Anisa, S.Kom., M.Kom.' }}</p>
            <p style="font-size:8pt; color:#6b7280;">NIK: {{ $kaprodi->nik ?? '...' }}</p>
          </td>
        </tr>
      </table>
    </div>

    <div class="footer">Laporan Evaluasi Dosen | TA {{ $ta }} | {{ $generatedAt }}</div>
  </div>

</body>

</html>