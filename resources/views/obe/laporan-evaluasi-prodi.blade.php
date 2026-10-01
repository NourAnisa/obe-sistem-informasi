<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan Evaluasi Program Studi Sistem Informasi {{ $ta }}</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      font-size: 9pt;
      color: #1a1a1a;
      background: #fff;
    }

    @page {
      size: A4 landscape;
      margin: 1.5cm 1.8cm 1.5cm 1.8cm;
    }

    /* ── Cover ── */
    .cover {
      text-align: center;
      padding-top: 60px;
    }

    .cover .logo-placeholder {
      width: 80px;
      height: 80px;
      border: 2px solid #374151;
      border-radius: 50%;
      margin: 0 auto 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 8pt;
      color: #6b7280;
      padding: 10px;
    }

    .cover h1 {
      font-size: 16pt;
      font-weight: bold;
      color: #1e3a5f;
      margin-bottom: 6px;
    }

    .cover h2 {
      font-size: 12pt;
      color: #374151;
      margin-bottom: 4px;
    }

    .cover h3 {
      font-size: 10pt;
      color: #4b5563;
      margin-bottom: 20px;
    }

    .cover .badge {
      display: inline-block;
      background: #1e3a5f;
      color: #fff;
      padding: 6px 22px;
      border-radius: 20px;
      font-size: 10pt;
      margin-bottom: 30px;
    }

    .cover .meta {
      font-size: 9pt;
      color: #6b7280;
      margin-top: 40px;
    }

    .cover .meta strong {
      color: #374151;
    }

    /* ── Section/page breaks ── */
    .page-break {
      page-break-before: always;
    }

    /* ── Headers ── */
    .section-header {
      background: #1e3a5f;
      color: #fff;
      padding: 8px 14px;
      font-size: 11pt;
      font-weight: bold;
      margin-bottom: 14px;
      border-radius: 3px;
    }

    .sub-header {
      background: #dbeafe;
      color: #1e3a5f;
      padding: 5px 12px;
      font-size: 9.5pt;
      font-weight: bold;
      margin-top: 14px;
      margin-bottom: 8px;
      border-left: 4px solid #1e3a5f;
    }

    h4.section-title {
      font-size: 10pt;
      color: #1e3a5f;
      margin: 10px 0 6px;
      font-weight: bold;
    }

    /* ── Tables ── */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 8.5pt;
      margin-bottom: 12px;
    }

    th {
      background: #1e3a5f;
      color: #fff;
      padding: 6px 8px;
      text-align: left;
      font-weight: bold;
    }

    td {
      padding: 5px 8px;
      border: 1px solid #d1d5db;
      vertical-align: top;
    }

    tr:nth-child(even) td {
      background: #f8fafc;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
    }

    /* ── Status badges ── */
    .badge-green {
      background: #d1fae5;
      color: #065f46;
      padding: 2px 7px;
      border-radius: 10px;
      font-size: 7.5pt;
      font-weight: bold;
    }

    .badge-yellow {
      background: #fef3c7;
      color: #92400e;
      padding: 2px 7px;
      border-radius: 10px;
      font-size: 7.5pt;
      font-weight: bold;
    }

    .badge-red {
      background: #fee2e2;
      color: #991b1b;
      padding: 2px 7px;
      border-radius: 10px;
      font-size: 7.5pt;
      font-weight: bold;
    }

    .badge-blue {
      background: #dbeafe;
      color: #1e40af;
      padding: 2px 7px;
      border-radius: 10px;
      font-size: 7.5pt;
      font-weight: bold;
    }

    /* ── Summary cards ── */
    .cards {
      display: table;
      width: 100%;
      margin-bottom: 14px;
      table-layout: fixed;
    }

    .card {
      display: table-cell;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 10px 12px;
      text-align: center;
    }

    .card-value {
      font-size: 18pt;
      font-weight: bold;
    }

    .card-label {
      font-size: 7.5pt;
      color: #6b7280;
      margin-top: 2px;
    }

    .card-green {
      border-color: #6ee7b7;
    }

    .card-yellow {
      border-color: #fcd34d;
    }

    .card-red {
      border-color: #fca5a5;
    }

    .card-blue {
      border-color: #93c5fd;
    }

    /* ── CSS Bar Chart ── */
    .chart-wrap {
      margin-bottom: 12px;
    }

    .chart-row {
      display: table;
      width: 100%;
      margin-bottom: 4px;
      table-layout: fixed;
    }

    .chart-label {
      display: table-cell;
      width: 70px;
      font-size: 7.5pt;
      color: #374151;
      vertical-align: middle;
      text-align: right;
      padding-right: 6px;
    }

    .chart-bars {
      display: table-cell;
      vertical-align: middle;
    }

    .bar-bg {
      background: #e5e7eb;
      border-radius: 2px;
      height: 12px;
      width: 100%;
      position: relative;
    }

    .bar-maks {
      position: absolute;
      top: 0;
      left: 0;
      height: 12px;
      background: #bfdbfe;
      border-radius: 2px;
    }

    .bar-skor {
      position: absolute;
      top: 0;
      left: 0;
      height: 12px;
      background: #1e3a5f;
      border-radius: 2px;
    }

    .chart-val {
      display: table-cell;
      width: 50px;
      font-size: 7.5pt;
      color: #374151;
      vertical-align: middle;
      padding-left: 6px;
    }

    /* ── Traffic light ── */
    .tl-table {
      width: 100%;
      border-collapse: collapse;
    }

    .tl-table th {
      background: #1e3a5f;
      color: #fff;
      padding: 5px 6px;
      font-size: 7.5pt;
      text-align: center;
    }

    .tl-table td {
      padding: 5px 6px;
      border: 1px solid #d1d5db;
      text-align: center;
      font-size: 8pt;
      font-weight: bold;
    }

    .tl-green {
      background: #d1fae5;
      color: #065f46;
    }

    .tl-yellow {
      background: #fef3c7;
      color: #92400e;
    }

    .tl-red {
      background: #fee2e2;
      color: #991b1b;
    }

    /* ── Rekomendasi ── */
    .rekom-box {
      border: 1px solid #d1d5db;
      border-radius: 5px;
      padding: 10px 14px;
      margin-bottom: 8px;
    }

    .rekom-box.green {
      border-left: 5px solid #10b981;
      background: #f0fdf4;
    }

    .rekom-box.yellow {
      border-left: 5px solid #f59e0b;
      background: #fffbeb;
    }

    .rekom-box.red {
      border-left: 5px solid #ef4444;
      background: #fef2f2;
    }

    .rekom-title {
      font-size: 9pt;
      font-weight: bold;
      margin-bottom: 4px;
    }

    .rekom-text {
      font-size: 8.5pt;
      color: #374151;
    }

    /* ── Footer ── */
    .footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 7pt;
      color: #9ca3af;
      border-top: 1px solid #e5e7eb;
      padding: 4px 0;
    }

    /* ── Tim Penyusun ── */
    .tim-row {
      display: table;
      width: 100%;
      margin-bottom: 8px;
    }

    .tim-cell {
      display: table-cell;
      width: 30%;
      padding: 10px;
      border: 1px solid #e5e7eb;
      border-radius: 4px;
      vertical-align: top;
      margin-right: 10px;
    }

    .tim-role {
      font-size: 7.5pt;
      color: #6b7280;
      margin-bottom: 2px;
    }

    .tim-name {
      font-size: 9pt;
      font-weight: bold;
    }

    /* ── Static text ── */
    .static-text {
      font-size: 8.5pt;
      color: #374151;
      line-height: 1.6;
      margin-bottom: 8px;
    }

    .indent {
      margin-left: 16px;
    }

    p {
      margin-bottom: 6px;
    }
  </style>
</head>

<body>

  <!-- ══════════════════════════════════════════ -->
  <!-- COVER PAGE -->
  <!-- ══════════════════════════════════════════ -->
  <div class="cover">
    <div class="logo-placeholder">LOGO<br>UNISM</div>
    <h1>LAPORAN EVALUASI PROGRAM STUDI</h1>
    <h2>Program Studi Sistem Informasi (S1)</h2>
    <h3>Fakultas Sains dan Teknologi — Universitas Sari Mulia Banjarmasin</h3>
    <div class="badge">Tahun Akademik {{ $ta }}</div>

    <table style="width:50%; margin: 30px auto 0; border:1px solid #d1d5db; font-size:9pt;">
      <tr>
        <td style="padding:6px 10px; background:#f9fafb;"><strong>Program Studi</strong></td>
        <td style="padding:6px 10px;">Sistem Informasi (S1)</td>
      </tr>
      <tr>
        <td style="padding:6px 10px; background:#f9fafb;"><strong>Ketua Program Studi</strong></td>
        <td style="padding:6px 10px;">{{ $kaprodi->name ?? config('obe.kaprodi') }}</td>
      </tr>
      <tr>
        <td style="padding:6px 10px; background:#f9fafb;"><strong>Tahun Akademik</strong></td>
        <td style="padding:6px 10px;">{{ $ta }}</td>
      </tr>
      <tr>
        <td style="padding:6px 10px; background:#f9fafb;"><strong>Tanggal Laporan</strong></td>
        <td style="padding:6px 10px;">{{ $generatedAt }}</td>
      </tr>
    </table>

    <div class="meta" style="margin-top:50px;">
      <strong>Sistem Informasi OBE</strong> — Universitas Sari Mulia Banjarmasin<br>
      Dokumen ini dibuat secara otomatis oleh sistem
    </div>
  </div>

  <div class="footer">Laporan Evaluasi Prodi Sistem Informasi — TA {{ $ta }} | Dicetak: {{ $generatedAt }}</div>

  <!-- ══════════════════════════════════════════ -->
  <!-- TIM PENYUSUN -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">Tim Penyusun Laporan</div>

    <table>
      <tr>
        <th style="width:5%">No</th>
        <th style="width:30%">Nama</th>
        <th style="width:20%">NIK / NIP</th>
        <th style="width:20%">Jabatan</th>
        <th style="width:25%">Peran dalam Penyusunan</th>
      </tr>
      <tr>
        <td class="text-center">1</td>
        <td>{{ $kaprodi->name ?? config('obe.kaprodi') }}</td>
        <td>{{ $kaprodi->nik ?? '—' }}</td>
        <td>Ketua Program Studi</td>
        <td>Penanggung jawab dan validator laporan</td>
      </tr>
      @foreach($timDosen as $i => $d)
      <tr>
        <td class="text-center">{{ $i + 2 }}</td>
        <td>{{ $d->name }}</td>
        <td>{{ $d->nik ?? '—' }}</td>
        <td>Dosen Pengampu</td>
        <td>Penyusun data pembelajaran dan capaian</td>
      </tr>
      @endforeach
    </table>

    <div class="sub-header">Persetujuan Laporan</div>
    <table style="width:60%; margin:0 auto;">
      <tr>
        <td style="text-align:center; padding:10px 20px;">
          <p>Banjarmasin, {{ now()->format('d F Y') }}</p>
          <p style="margin-top:50px;">{{ $kaprodi->name ?? config('obe.kaprodi') }}</p>
          <p style="font-size:7.5pt; color:#6b7280;">Ketua Program Studi Sistem Informasi</p>
        </td>
      </tr>
    </table>
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- BAB I — PENDAHULUAN -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">BAB I — PENDAHULUAN</div>

    <h4 class="section-title">A. Latar Belakang</h4>
    <p class="static-text">
      Evaluasi program studi merupakan bagian integral dari siklus penjaminan mutu pendidikan tinggi.
      Program Studi Sistem Informasi Universitas Sari Mulia Banjarmasin berkomitmen untuk melaksanakan
      proses evaluasi secara periodik sebagai wujud akuntabilitas akademik dan upaya peningkatan
      berkelanjutan (Continuous Quality Improvement / CQI).
    </p>
    <p class="static-text">
      Laporan ini disusun berdasarkan pendekatan <em>Outcome-Based Education</em> (OBE) sesuai
      Permendikbudristek No. 53 Tahun 2023 dan standar IAPS 4.0 LAM-INFOKOM. Evaluasi mencakup
      pencapaian Capaian Pembelajaran Lulusan (CPL), capaian per Mata Kuliah (CPMK), keterlaksanaan
      proses pembelajaran, serta analisis kebutuhan tindak lanjut.
    </p>

    <h4 class="section-title">B. Tujuan Evaluasi</h4>
    <p class="static-text indent">1. Mengidentifikasi tingkat ketercapaian Capaian Pembelajaran Lulusan (CPL) pada TA {{ $ta }}.</p>
    <p class="static-text indent">2. Mengevaluasi efektivitas proses pembelajaran ditinjau dari pelaksanaan RPS dan BAP.</p>
    <p class="static-text indent">3. Menganalisis distribusi beban mengajar dosen dan kesesuaiannya dengan kompetensi.</p>
    <p class="static-text indent">4. Memberikan rekomendasi perbaikan berdasarkan hasil analisis data.</p>

    <h4 class="section-title">C. Ruang Lingkup</h4>
    <p class="static-text">
      Laporan evaluasi ini mencakup seluruh mata kuliah yang diselenggarakan pada Tahun Akademik {{ $ta }},
      meliputi {{ $summary['total_mk'] }} mata kuliah dengan {{ $summary['total_dosen'] }} dosen pengampu
      yang tercatat dalam sistem. Evaluasi dilakukan terhadap {{ $summary['total_cpl'] }} Capaian
      Pembelajaran Lulusan (CPL) yang ditetapkan dalam kurikulum Program Studi Sistem Informasi.
    </p>

    <h4 class="section-title">D. Dasar Hukum</h4>
    <p class="static-text indent">1. Undang-Undang No. 12 Tahun 2012 tentang Pendidikan Tinggi</p>
    <p class="static-text indent">2. Permendikbudristek No. 53 Tahun 2023 tentang Penjaminan Mutu Pendidikan Tinggi</p>
    <p class="static-text indent">3. Standar IAPS 4.0 LAM-INFOKOM untuk Program Studi Informatika dan Sistem Informasi</p>
    <p class="static-text indent">4. Pedoman OBE Universitas Sari Mulia Banjarmasin</p>
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- BAB II — DESKRIPSI PROGRAM STUDI -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">BAB II — DESKRIPSI PROGRAM STUDI</div>

    <h4 class="section-title">A. Capaian Pembelajaran Lulusan (CPL)</h4>
    <p class="static-text">Berikut adalah daftar CPL yang ditetapkan dalam kurikulum Program Studi Sistem Informasi:</p>

    <table>
      <tr>
        <th style="width:8%">Kode CPL</th>
        <th style="width:10%">Kategori</th>
        <th style="width:57%">Deskripsi</th>
        <th style="width:10%">Skor Maks</th>
      </tr>
      @forelse($cplList as $cpl)
      <tr>
        <td class="text-center"><strong>{{ $cpl->kode }}</strong></td>
        <td class="text-center">
          @if(isset($cpl->kategori))
          <span class="badge-blue">{{ $cpl->kategori }}</span>
          @endif
        </td>
        <td>{{ $cpl->deskripsi }}</td>
        <td class="text-center">{{ $cpl->total_skor_maks ?? 100 }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="4" class="text-center" style="color:#9ca3af;">Belum ada data CPL</td>
      </tr>
      @endforelse
    </table>

    <div class="sub-header">B. Profil Lulusan</div>
    @if($profilList->count())
    <table>
      <tr>
        <th style="width:10%">Kode</th>
        <th style="width:90%">Deskripsi Profil Lulusan</th>
      </tr>
      @foreach($profilList as $p)
      <tr>
        <td class="text-center"><strong>{{ $p->kode }}</strong></td>
        <td>{{ $p->deskripsi }}</td>
      </tr>
      @endforeach
    </table>
    @else
    <p class="static-text" style="color:#6b7280; font-style:italic;">Data profil lulusan belum diinputkan dalam sistem.</p>
    @endif
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- BAB III-A — EVALUASI PROSES PEMBELAJARAN (RPS & DISTRIBUSI) -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">BAB III — EVALUASI PROSES PEMBELAJARAN</div>

    <div class="sub-header">A. Daftar Mata Kuliah TA {{ $ta }}</div>
    <table>
      <tr>
        <th style="width:5%">No</th>
        <th style="width:10%">Kode</th>
        <th style="width:35%">Nama Mata Kuliah</th>
        <th style="width:8%">SKS</th>
        <th style="width:10%">Semester</th>
        <th style="width:12%">Kategori</th>
        <th style="width:20%">Kode CPMK</th>
      </tr>
      @foreach($mataKuliahs as $i => $mk)
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td><strong>{{ $mk->kode }}</strong></td>
        <td>{{ $mk->nama }}</td>
        <td class="text-center">{{ $mk->sks }}</td>
        <td class="text-center">{{ $mk->semester }}</td>
        <td class="text-center">
          @if(isset($mk->kategori))<span class="badge-blue">{{ $mk->kategori }}</span>@endif
        </td>
        <td style="font-size:7.5pt;">{{ $mk->kode_cpmk ?? '—' }}</td>
      </tr>
      @endforeach
    </table>

    <div class="sub-header">B. Distribusi Dosen Mata Kuliah TA {{ $ta }}</div>
    @if($distribusiDosen->count())
    <table>
      <tr>
        <th style="width:5%">No</th>
        <th style="width:22%">Dosen</th>
        <th style="width:10%">MK</th>
        <th style="width:25%">Nama Mata Kuliah</th>
        <th style="width:7%">SKS</th>
        <th style="width:7%">Kelas</th>
        <th style="width:8%">Semester</th>
        <th style="width:8%">Peran</th>
        <th style="width:8%">Status</th>
      </tr>
      @foreach($distribusiDosen as $i => $d)
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td>{{ $d->nama_dosen }}</td>
        <td><strong>{{ $d->mk_kode }}</strong></td>
        <td>{{ $d->mk_nama }}</td>
        <td class="text-center">{{ $d->jumlah_sks ?? $d->sks ?? '—' }}</td>
        <td class="text-center">{{ $d->kelas ?? '—' }}</td>
        <td class="text-center">{{ $d->semester }}</td>
        <td class="text-center">
          <span class="{{ $d->peran === 'pengampu' ? 'badge-blue' : 'badge-green' }}">{{ ucfirst(str_replace('_',' ',$d->peran)) }}</span>
        </td>
        <td class="text-center">
          <span class="{{ ($d->status ?? 'aktif') === 'aktif' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($d->status ?? 'aktif') }}</span>
        </td>
      </tr>
      @endforeach
    </table>
    @else
    <p class="static-text" style="color:#6b7280; font-style:italic;">Belum ada data distribusi dosen untuk TA {{ $ta }}.</p>
    @endif
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- BAB III-B — PELAKSANAAN PEMBELAJARAN (BAP) -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">BAB III — PELAKSANAAN PEMBELAJARAN (Kehadiran & BAP)</div>

    <div class="sub-header">A. Rekapitulasi Kehadiran per Mata Kuliah</div>
    @if($bapData->count())
    <table>
      <tr>
        <th style="width:5%">No</th>
        <th style="width:10%">MK</th>
        <th style="width:30%">Nama Mata Kuliah</th>
        <th style="width:8%">Kelas</th>
        <th style="width:10%">Jml Mhs</th>
        <th style="width:10%">Pertemuan</th>
        <th style="width:12%">Rata Hadir</th>
        <th style="width:10%">% Hadir</th>
        <th style="width:5%">TK</th>
      </tr>
      @foreach($bapData as $i => $b)
      @php $pct = (float)($b->pct_hadir ?? 0); @endphp
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td><strong>{{ $b->mk_kode }}</strong></td>
        <td>{{ $b->mk_nama }}</td>
        <td class="text-center">{{ $b->kelas ?? '—' }}</td>
        <td class="text-center">{{ $b->jumlah_mahasiswa ?? '—' }}</td>
        <td class="text-center">{{ $b->total_pertemuan }}</td>
        <td class="text-center">{{ $b->rata_hadir }}</td>
        <td class="text-center">
          <span class="{{ $pct >= 75 ? 'badge-green' : ($pct >= 60 ? 'badge-yellow' : 'badge-red') }}">
            {{ $pct }}%
          </span>
        </td>
        <td class="text-center">{{ $b->total_tk ?? 0 }}</td>
      </tr>
      @endforeach
    </table>
    @else
    <p class="static-text" style="color:#6b7280; font-style:italic;">Belum ada data BAP/kehadiran untuk TA {{ $ta }}.</p>
    @endif

    <div class="sub-header">B. Keterangan</div>
    <p class="static-text indent">• % Hadir dihitung dari rata-rata jumlah mahasiswa hadir dibagi jumlah mahasiswa terdaftar × 100%.</p>
    <p class="static-text indent">• TK = Tanpa Keterangan (absensi tidak sah).</p>
    <p class="static-text indent">• Kehadiran ≥ 75% dianggap memenuhi standar minimum pembelajaran.</p>
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- BAB IV — ANALISIS OUTCOME -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">BAB IV — ANALISIS OUTCOME (Capaian CPL & CPMK)</div>

    <!-- Summary cards -->
    <div class="cards" style="margin-bottom:14px;">
      <div style="display:table; width:100%; table-layout:fixed; border-spacing:6px;">
        <div style="display:table-cell; border:1px solid #bfdbfe; border-radius:6px; padding:10px; text-align:center; background:#eff6ff;">
          <div style="font-size:20pt; font-weight:bold; color:#1e40af;">{{ $summary['total_cpl'] }}</div>
          <div style="font-size:7.5pt; color:#6b7280;">Total CPL</div>
        </div>
        <div style="display:table-cell; border:1px solid #6ee7b7; border-radius:6px; padding:10px; text-align:center; background:#f0fdf4; padding-left:12px;">
          <div style="font-size:20pt; font-weight:bold; color:#065f46;">{{ $summary['cpl_tercapai'] }}</div>
          <div style="font-size:7.5pt; color:#6b7280;">CPL Tercapai</div>
        </div>
        <div style="display:table-cell; border:1px solid #fcd34d; border-radius:6px; padding:10px; text-align:center; background:#fffbeb; padding-left:12px;">
          <div style="font-size:20pt; font-weight:bold; color:#92400e;">{{ $summary['cpl_monitoring'] }}</div>
          <div style="font-size:7.5pt; color:#6b7280;">Perlu Monitoring</div>
        </div>
        <div style="display:table-cell; border:1px solid #fca5a5; border-radius:6px; padding:10px; text-align:center; background:#fef2f2; padding-left:12px;">
          <div style="font-size:20pt; font-weight:bold; color:#991b1b;">{{ $summary['cpl_belum'] }}</div>
          <div style="font-size:7.5pt; color:#6b7280;">Belum Tercapai</div>
        </div>
        <div style="display:table-cell; border:1px solid #d1d5db; border-radius:6px; padding:10px; text-align:center; background:#f9fafb; padding-left:12px;">
          <div style="font-size:20pt; font-weight:bold; color:#374151;">{{ $summary['rata_capaian'] }}</div>
          <div style="font-size:7.5pt; color:#6b7280;">Rata-rata Nilai CPL</div>
        </div>
      </div>
    </div>

    <!-- CPL Bar Chart (CSS only) -->
    <div class="sub-header">A. Grafik Capaian CPL</div>
    @php $maxSkor = $cplStats->max('total_skor_maks') ?: 100; @endphp
    <div class="chart-wrap">
      @forelse($cplStats as $cpl)
      @php
      $maks = (float)($cpl->total_skor_maks ?: 100);
      $nilai = (float)($cpl->rata_nilai ?: 0);
      $barMax = min(100, round(($maks / $maxSkor) * 100));
      $barNilai = min(100, round(($nilai / $maxSkor) * 100));
      @endphp
      <div class="chart-row">
        <div class="chart-label">{{ $cpl->kode }}</div>
        <div class="chart-bars">
          <div class="bar-bg">
            <div class="bar-maks" style="width:{{ $barMax }}%;"></div>
            <div class="bar-skor" style="width:{{ $barNilai }}%;"></div>
          </div>
        </div>
        <div class="chart-val">{{ $nilai }} / {{ (int)$maks }}</div>
      </div>
      @empty
      <p style="font-size:8pt; color:#9ca3af;">Belum ada data capaian CPL.</p>
      @endforelse
    </div>
    <p style="font-size:7.5pt; color:#6b7280; margin-bottom:12px;">
      &#9632; <span style="color:#bfdbfe;">&#9632;</span> Skor Maksimal &nbsp;
      &#9632; <span style="color:#1e3a5f;">&#9632;</span> Rata-rata Capaian
    </p>

    <!-- CPL Table -->
    <div class="sub-header">B. Tabel Evaluasi CPL</div>
    <table>
      <tr>
        <th style="width:8%">CPL</th>
        <th style="width:35%">Deskripsi</th>
        <th style="width:8%">Skor Maks</th>
        <th style="width:10%">Total Mhs</th>
        <th style="width:10%">Jml Tercapai</th>
        <th style="width:10%">Rata Nilai</th>
        <th style="width:10%">% Tercapai</th>
        <th style="width:9%">Status</th>
      </tr>
      @forelse($cplStats as $cpl)
      @php
      $statusClass = match($cpl->status) {
      'Tercapai' => 'badge-green',
      'Perlu Monitoring' => 'badge-yellow',
      default => 'badge-red',
      };
      @endphp
      <tr>
        <td class="text-center"><strong>{{ $cpl->kode }}</strong></td>
        <td>{{ Str::limit($cpl->deskripsi, 80) }}</td>
        <td class="text-center">{{ (int)($cpl->total_skor_maks ?: 100) }}</td>
        <td class="text-center">{{ $cpl->total_mahasiswa }}</td>
        <td class="text-center">{{ $cpl->jumlah_tercapai }}</td>
        <td class="text-center">{{ $cpl->rata_nilai }}</td>
        <td class="text-center">{{ $cpl->pct_tercapai }}%</td>
        <td class="text-center"><span class="{{ $statusClass }}">{{ $cpl->status }}</span></td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center" style="color:#9ca3af;">Belum ada data capaian CPL.</td>
      </tr>
      @endforelse
    </table>
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- BAB IV-B — CPMK Achievement -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">BAB IV — ANALISIS OUTCOME (Lanjutan: Capaian CPMK per Mata Kuliah)</div>

    <div class="sub-header">C. Tabel Capaian CPMK per Mata Kuliah</div>
    @if($cpmkStats->count())
    <table>
      <tr>
        <th style="width:7%">CPL</th>
        <th style="width:7%">CPMK</th>
        <th style="width:7%">Kode MK</th>
        <th style="width:25%">Nama Mata Kuliah</th>
        <th style="width:6%">Total</th>
        <th style="width:6%">Tercapai</th>
        <th style="width:9%">Rata Nilai</th>
        <th style="width:9%">% Lulus</th>
        <th style="width:8%">Status</th>
        <th style="width:9%">Rubrik</th>
      </tr>
      @foreach($cpmkStats as $c)
      @php
      $pct = (float)($c->pct_lulus ?? 0);
      $stCls = $pct >= 80 ? 'badge-green' : ($pct >= 60 ? 'badge-yellow' : 'badge-red');
      $stTxt = $pct >= 80 ? 'Tercapai' : ($pct >= 60 ? 'Monitoring' : 'Belum');
      $rnilai = (float)($c->rata_nilai ?? 0);
      $rubrikCls = $rnilai >= 80 ? 'badge-green' : ($rnilai >= 60 ? 'badge-yellow' : 'badge-red');
      $rubrikTxt = $rnilai >= 80 ? 'Proficient' : ($rnilai >= 60 ? 'Developing' : 'Novice');
      @endphp
      <tr>
        <td class="text-center">{{ $c->cpl_kode }}</td>
        <td class="text-center">{{ $c->cpmk_kode }}</td>
        <td class="text-center">{{ $c->mk_kode }}</td>
        <td>{{ $c->mk_nama }}</td>
        <td class="text-center">{{ $c->total }}</td>
        <td class="text-center">{{ $c->tercapai }}</td>
        <td class="text-center">{{ $c->rata_nilai }}</td>
        <td class="text-center">{{ $pct }}%</td>
        <td class="text-center"><span class="{{ $stCls }}">{{ $stTxt }}</span></td>
        <td class="text-center"><span class="{{ $rubrikCls }}">{{ $rubrikTxt }}</span></td>
      </tr>
      @endforeach
    </table>
    @else
    <p class="static-text" style="color:#6b7280; font-style:italic;">Belum ada data capaian CPMK untuk TA {{ $ta }}.</p>
    @endif
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- BAB V — TEMUAN & RTL -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">BAB V — TEMUAN DAN RENCANA TINDAK LANJUT (RTL)</div>

    <div class="sub-header">A. Early Warning — Mahasiswa Belum Mencapai CPL</div>
    @if($earlyWarning->count())
    <p class="static-text">
      Terdapat <strong>{{ $summary['total_mhs_warning'] }} mahasiswa</strong> yang belum memenuhi threshold
      capaian CPL pada TA {{ $ta }}. Berikut adalah daftar 50 mahasiswa dengan nilai terendah:
    </p>
    <table>
      <tr>
        <th style="width:5%">No</th>
        <th style="width:13%">NIM</th>
        <th style="width:25%">Nama</th>
        <th style="width:8%">Angkatan</th>
        <th style="width:8%">CPL</th>
        <th style="width:10%">Nilai CPL</th>
        <th style="width:10%">Threshold</th>
        <th style="width:8%">Gap</th>
        <th style="width:10%">Status</th>
      </tr>
      @foreach($earlyWarning as $i => $w)
      @php $isKritis = $w->status === 'Kritis'; @endphp
      <tr>
        <td class="text-center">{{ $i + 1 }}</td>
        <td><strong>{{ $w->nim }}</strong></td>
        <td>{{ $w->nama }}</td>
        <td class="text-center">{{ $w->angkatan }}</td>
        <td class="text-center"><strong>{{ $w->cpl_kode }}</strong></td>
        <td class="text-center">{{ $w->nilai_cpl }}</td>
        <td class="text-center">{{ $w->threshold }}</td>
        <td class="text-center" style="color:#991b1b; font-weight:bold;">{{ $w->gap }}</td>
        <td class="text-center">
          <span class="{{ $isKritis ? 'badge-red' : 'badge-yellow' }}">{{ $w->status }}</span>
        </td>
      </tr>
      @endforeach
    </table>
    @else
    <p class="static-text" style="color:#10b981; font-weight:bold;">
      ✅ Tidak ada mahasiswa dengan status early warning pada TA {{ $ta }}. Semua CPL telah tercapai.
    </p>
    @endif

    <!-- Masukan Mahasiswa (kritik & saran from meeting 16) -->
    <div class="sub-header" style="margin-top:16px;">B. Masukan Mahasiswa</div>
    @if(isset($masukanMahasiswa) && $masukanMahasiswa->isNotEmpty())
    @foreach($masukanMahasiswa as $mkKode => $feedbacks)
    <div style="margin-bottom:12px; border:1px solid #e5e7eb; border-radius:6px; padding:10px;">
      <strong style="font-size:11px;color:#1e40af;">📚 {{ $mkKode }} — {{ $feedbacks->first()->mk_nama }}</strong>
      <table style="margin-top:6px; font-size:10px;">
        <tr>
          <th style="width:5%">No</th>
          <th style="width:47.5%">Kritik</th>
          <th style="width:47.5%">Saran</th>
        </tr>
        @foreach($feedbacks as $i => $f)
        <tr>
          <td class="text-center">{{ $i + 1 }}</td>
          <td>{{ $f->kritik ?: '—' }}</td>
          <td>{{ $f->saran ?: '—' }}</td>
        </tr>
        @endforeach
      </table>
    </div>
    @endforeach
    @else
    <p class="static-text" style="color:#6b7280; font-style:italic;">
      Belum ada masukan mahasiswa untuk TA {{ $ta }}. Data akan tersedia setelah mahasiswa mengisi evaluasi pertemuan ke-16.
    </p>
    @endif

    <!-- Rekomendasi Otomatis -->
    <div class="sub-header" style="margin-top:16px;">C. Rekomendasi Otomatis</div>

    @forelse($cplStats as $cpl)
    @php
    $pct = (float)($cpl->pct_tercapai ?? 0);
    $boxClass = $pct >= 80 ? 'green' : ($pct >= 60 ? 'yellow' : 'red');
    $icon = $pct >= 80 ? '✅' : ($pct >= 60 ? '⚠️' : '❌');
    $rekom = match(true) {
    $pct >= 80 => "Capaian {$cpl->kode} sudah memenuhi target ({$cpl->pct_tercapai}%). Pertahankan kualitas pembelajaran dan terus kembangkan inovasi metode pengajaran.",
    $pct >= 60 => "Capaian {$cpl->kode} mendekati target ({$cpl->pct_tercapai}%). Lakukan monitoring berkala, perkuat strategi pembelajaran pada materi yang masih lemah, dan berikan tutorial tambahan.",
    default => "Capaian {$cpl->kode} belum memenuhi target ({$cpl->pct_tercapai}%). Diperlukan evaluasi kurikulum segera, review metode pembelajaran, serta intervensi langsung kepada mahasiswa yang belum mencapai threshold.",
    };
    @endphp
    <div class="rekom-box {{ $boxClass }}">
      <div class="rekom-title">{{ $icon }} {{ $cpl->kode }} — {{ $cpl->status }} ({{ $cpl->pct_tercapai }}%)</div>
      <div class="rekom-text">{{ $rekom }}</div>
    </div>
    @empty
    <p class="static-text" style="color:#6b7280; font-style:italic;">Belum ada data CPL untuk diberikan rekomendasi.</p>
    @endforelse

    <!-- Ringkasan RTL -->
    <div class="sub-header" style="margin-top:16px;">D. Ringkasan Rencana Tindak Lanjut</div>
    <table>
      <tr>
        <th style="width:5%">No</th>
        <th style="width:20%">Fokus Area</th>
        <th style="width:35%">Tindakan</th>
        <th style="width:20%">Penanggung Jawab</th>
        <th style="width:20%">Target Waktu</th>
      </tr>
      @if($summary['cpl_belum'] > 0)
      <tr>
        <td class="text-center">1</td>
        <td>CPL Belum Tercapai ({{ $summary['cpl_belum'] }} CPL)</td>
        <td>Evaluasi metode pembelajaran, intensifikasi tutorial, review bobot penilaian</td>
        <td>Kaprodi + Dosen Pengampu</td>
        <td>Semester berikutnya</td>
      </tr>
      @endif
      @if($summary['cpl_monitoring'] > 0)
      <tr>
        <td class="text-center">{{ $summary['cpl_belum'] > 0 ? 2 : 1 }}</td>
        <td>CPL Perlu Monitoring ({{ $summary['cpl_monitoring'] }} CPL)</td>
        <td>Monitoring berkala bulanan, perkuat strategi pembelajaran aktif</td>
        <td>Dosen Pengampu</td>
        <td>Pertengahan semester</td>
      </tr>
      @endif
      @if($summary['total_mhs_warning'] > 0)
      <tr>
        <td class="text-center">{{ $summary['cpl_belum'] + $summary['cpl_monitoring'] + 1 }}</td>
        <td>Early Warning ({{ $summary['total_mhs_warning'] }} Mahasiswa)</td>
        <td>Program remedial, bimbingan PA intensif, konseling akademik</td>
        <td>Dosen PA + Akademik</td>
        <td>Segera / 1 bulan</td>
      </tr>
      @endif
      <tr>
        <td class="text-center">{{ $summary['cpl_belum'] + $summary['cpl_monitoring'] + ($summary['total_mhs_warning'] > 0 ? 2 : 1) }}</td>
        <td>Peningkatan Umum</td>
        <td>Workshop peningkatan kompetensi dosen, pembaruan materi RPS sesuai tren industri</td>
        <td>Kaprodi</td>
        <td>Setiap semester</td>
      </tr>
    </table>
  </div>

  <!-- ══════════════════════════════════════════ -->
  <!-- PENUTUP -->
  <!-- ══════════════════════════════════════════ -->
  <div class="page-break">
    <div class="section-header">PENUTUP</div>
    <p class="static-text">
      Laporan evaluasi Program Studi Sistem Informasi Tahun Akademik {{ $ta }} ini disusun berdasarkan
      data yang tercatat dalam Sistem Informasi OBE UNISM Banjarmasin. Hasil evaluasi menunjukkan
      bahwa dari {{ $summary['total_cpl'] }} CPL yang ditetapkan, sebanyak
      <strong>{{ $summary['cpl_tercapai'] }} CPL</strong> telah tercapai,
      <strong>{{ $summary['cpl_monitoring'] }} CPL</strong> perlu monitoring, dan
      <strong>{{ $summary['cpl_belum'] }} CPL</strong> belum tercapai dengan rata-rata capaian keseluruhan
      sebesar <strong>{{ $summary['rata_capaian'] }}</strong>.
    </p>
    <p class="static-text">
      Rekomendasi dan rencana tindak lanjut yang telah disusun diharapkan dapat menjadi acuan dalam
      perbaikan proses pembelajaran pada semester/tahun akademik berikutnya. Program Studi Sistem
      Informasi berkomitmen untuk terus meningkatkan kualitas pendidikan demi menghasilkan lulusan
      yang kompeten dan relevan dengan kebutuhan industri.
    </p>

    <table style="width:60%; margin:30px auto 0;">
      <tr>
        <td style="text-align:center; padding:10px 20px;">
          <p>Banjarmasin, {{ now()->format('d F Y') }}</p>
          <p>Ketua Program Studi Sistem Informasi</p>
          <p style="margin-top:55px; font-weight:bold;">{{ $kaprodi->name ?? config('obe.kaprodi') }}</p>
          <p style="font-size:7.5pt; color:#6b7280; border-top:1px solid #d1d5db; margin-top:4px; padding-top:4px;">Universitas Sari Mulia Banjarmasin</p>
        </td>
      </tr>
    </table>

    <p style="text-align:center; margin-top:30px; font-size:7.5pt; color:#9ca3af;">
      Dicetak otomatis oleh Sistem Informasi OBE UNISM — {{ $generatedAt }}
    </p>
  </div>

</body>

</html>