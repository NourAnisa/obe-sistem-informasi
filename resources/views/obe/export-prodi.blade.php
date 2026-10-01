<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Evaluasi Program Studi — {{ $ta }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.5;
        }

        /* ── Header ── */
        .header {
            border-bottom: 3px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header-logo {
            width: 60px;
            height: 60px;
            border: 2px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            color: #9ca3af;
            text-align: center;
        }

        .header-title {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }

        .header-title .univ {
            font-size: 13px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
        }

        .header-title .prodi {
            font-size: 11px;
            color: #374151;
        }

        .header-title .doc-title {
            font-size: 10px;
            color: #6b7280;
            margin-top: 2px;
        }

        .header-meta {
            text-align: right;
            font-size: 9px;
            color: #6b7280;
        }

        /* ── Section titles ── */
        .section-title {
            background: #1e40af;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 10px;
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .section-sub {
            background: #eff6ff;
            color: #1e40af;
            padding: 4px 10px;
            font-weight: bold;
            font-size: 9px;
            margin-top: 10px;
            margin-bottom: 6px;
            border-left: 3px solid #1e40af;
        }

        /* ── Summary cards ── */
        .cards {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .card {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }

        .card .num {
            font-size: 22px;
            font-weight: bold;
        }

        .card .lbl {
            font-size: 8px;
            color: #6b7280;
            margin-top: 2px;
        }

        .card.blue .num {
            color: #1e40af;
        }

        .card.green .num {
            color: #15803d;
        }

        .card.red .num {
            color: #dc2626;
        }

        .card.purple .num {
            color: #7c3aed;
        }

        /* ── Tables ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 12px;
        }

        th {
            background: #eff6ff;
            color: #1e40af;
            padding: 5px 7px;
            text-align: left;
            border: 1px solid #bfdbfe;
            font-size: 8.5px;
        }

        td {
            padding: 4px 7px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }

        tr:nth-child(even) td {
            background: #f9fafb;
        }

        /* ── Status badges ── */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-green {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-yellow {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-red {
            background: #fee2e2;
            color: #dc2626;
        }

        /* ── Progress bar ── */
        .bar-wrap {
            background: #e5e7eb;
            border-radius: 4px;
            height: 8px;
            width: 100%;
        }

        .bar-fill {
            height: 8px;
            border-radius: 4px;
        }

        .bar-green {
            background: #22c55e;
        }

        .bar-yellow {
            background: #facc15;
        }

        .bar-red {
            background: #ef4444;
        }

        /* ── Traffic light grid ── */
        .tl-table th,
        .tl-table td {
            text-align: center;
            font-size: 8px;
            padding: 3px 5px;
        }

        .tl-green {
            background: #bbf7d0;
            color: #14532d;
            font-weight: bold;
        }

        .tl-yellow {
            background: #fef08a;
            color: #713f12;
            font-weight: bold;
        }

        .tl-red {
            background: #fecaca;
            color: #7f1d1d;
            font-weight: bold;
        }

        .tl-empty {
            background: #f3f4f6;
            color: #9ca3af;
        }

        /* ── CPL chart bars (CSS-based, dompdf has no canvas) ── */
        .chart-row {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
            gap: 6px;
        }

        .chart-label {
            width: 55px;
            font-size: 8.5px;
            font-weight: bold;
            text-align: right;
            color: #374151;
        }

        .chart-bars {
            flex: 1;
        }

        .bar-row {
            display: flex;
            align-items: center;
            margin-bottom: 2px;
            gap: 4px;
        }

        .bar-legend {
            width: 60px;
            font-size: 7.5px;
            color: #6b7280;
        }

        .bar-outer {
            flex: 1;
            background: #f3f4f6;
            height: 10px;
            border-radius: 3px;
            position: relative;
        }

        .bar-inner {
            height: 10px;
            border-radius: 3px;
        }

        .bar-pct {
            font-size: 7.5px;
            color: #374151;
            margin-left: 4px;
        }

        /* ── Rekomendasi ── */
        .rec-item {
            border-left: 3px solid;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-size: 9px;
        }

        .rec-red {
            border-color: #ef4444;
            background: #fff7f7;
        }

        .rec-yellow {
            border-color: #facc15;
            background: #fffdf0;
        }

        .rec-green {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        .rec-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #9ca3af;
        }

        /* ── Page break ── */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    {{-- ══════════════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════════════ --}}
    <div class="header">
        <div class="header-top">
            <div class="header-logo">LOGO<br>UNISM</div>
            <div class="header-title">
                <div class="univ">Universitas Sari Mulia (UNISM) Banjarmasin</div>
                <div class="prodi">Fakultas Sains dan Teknologi — Program Studi Sistem Informasi (S1)</div>
                <div class="doc-title">LAPORAN EVALUASI CAPAIAN PEMBELAJARAN LULUSAN (CPL)</div>
            </div>
            <div class="header-meta">
                Tahun Akademik<br><strong>{{ $ta }}</strong><br><br>
                Tanggal Cetak<br>{{ $generatedAt }}
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
     BAGIAN 1 — SUMMARY CARDS
══════════════════════════════════════════════════════ --}}
    <div class="section-title">1. RINGKASAN CAPAIAN CPL</div>

    <div class="cards">
        <div class="card blue">
            <div class="num">{{ $totalCpl }}</div>
            <div class="lbl">Total CPL</div>
        </div>
        <div class="card green">
            <div class="num">{{ $jumlahTercapai }}</div>
            <div class="lbl">CPL Tercapai</div>
        </div>
        <div class="card red">
            <div class="num">{{ $jumlahBelum }}</div>
            <div class="lbl">CPL Belum Tercapai</div>
        </div>
        <div class="card purple">
            <div class="num">{{ $rataCapaian }}</div>
            <div class="lbl">Rata-rata Capaian</div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
     BAGIAN 2 — TABEL EVALUASI CPL
══════════════════════════════════════════════════════ --}}
    <div class="section-title">2. TABEL EVALUASI CPL PER INDIKATOR</div>

    <table>
        <thead>
            <tr>
                <th style="width:8%">CPL</th>
                <th style="width:30%">Deskripsi</th>
                <th style="width:7%">Kategori</th>
                <th style="width:8%" class="text-right">Mhs</th>
                <th style="width:8%" class="text-right">Rata Nilai</th>
                <th style="width:18%">Progress</th>
                <th style="width:8%" class="text-right">% Tercapai</th>
                <th style="width:13%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cplStats as $row)
            @php
            $pct = (float)$row->pct_tercapai;
            $barColor = $pct >= 80 ? 'bar-green' : ($pct >= 60 ? 'bar-yellow' : 'bar-red');
            $badgeCls = $pct >= 80 ? 'badge-green' : ($pct >= 60 ? 'badge-yellow' : 'badge-red');
            @endphp
            <tr>
                <td><strong>{{ $row->kode }}</strong></td>
                <td style="font-size:8px">{{ $row->deskripsi }}</td>
                <td>{{ $row->kategori ?? '-' }}</td>
                <td style="text-align:center">{{ $row->total_mahasiswa }}</td>
                <td style="text-align:center">{{ $row->rata_nilai }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:4px">
                        <div class="bar-wrap" style="flex:1">
                            <div class="bar-fill {{ $barColor }}" style="width:{{ min(100, $pct) }}%"></div>
                        </div>
                    </div>
                </td>
                <td style="text-align:center">{{ $row->pct_tercapai }}%</td>
                <td><span class="badge {{ $badgeCls }}">{{ $row->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ══════════════════════════════════════════════════════
     BAGIAN 3 — TRAFFIC LIGHT GRID (CPL × Angkatan)
══════════════════════════════════════════════════════ --}}
    @if($cohortByAngkatan->isNotEmpty())
    <div class="section-title">3. TRAFFIC LIGHT GRID — CPL × ANGKATAN</div>

    <table class="tl-table">
        <thead>
            <tr>
                <th>Angkatan</th>
                @foreach($cplList as $c)
                <th>{{ $c->kode }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($cohortByAngkatan as $angk => $rows)
            <tr>
                <td style="font-weight:bold;text-align:center">{{ $angk }}</td>
                @foreach($cplList as $c)
                @php
                $cell = $rows->firstWhere('cpl_id', $c->id);
                if ($cell) {
                $cls = match($cell->status_target) {
                'tercapai' => 'tl-green',
                'perlu_monitoring' => 'tl-yellow',
                default => 'tl-red',
                };
                $label = round($cell->pct_lulus, 0).'%';
                } else {
                $cls = 'tl-empty';
                $label = '—';
                }
                @endphp
                <td class="{{ $cls }}">{{ $label }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-size:8px;color:#6b7280;margin-top:4px">
        <span style="background:#bbf7d0;padding:1px 6px;border-radius:3px;margin-right:6px">■ Tercapai (≥target)</span>
        <span style="background:#fef08a;padding:1px 6px;border-radius:3px;margin-right:6px">■ Perlu Monitoring (≥80% target)</span>
        <span style="background:#fecaca;padding:1px 6px;border-radius:3px">■ Belum Tercapai</span>
    </p>
    @endif

    {{-- ══════════════════════════════════════════════════════
     BAGIAN 4 — GRAFIK CPL (CSS bar chart — dompdf safe)
══════════════════════════════════════════════════════ --}}
    <div class="section-title" style="margin-top:18px">4. GRAFIK PERBANDINGAN CPL: SKOR MAKSIMAL vs SKOR SEMENTARA</div>

    @foreach($cplStats as $row)
    @php
    $maks = (float)$row->total_skor_maks;
    $skor = (float)$row->rata_nilai;
    $pMaks = $maks > 0 ? min(100, round($maks / max($maks, $skor) * 100)) : 0;
    $pSkor = $maks > 0 ? min(100, round($skor / $maks * 100)) : min(100, round($skor));
    $barC = $pSkor >= 80 ? '#22c55e' : ($pSkor >= 60 ? '#facc15' : '#ef4444');
    @endphp
    <div class="chart-row">
        <div class="chart-label">{{ $row->kode }}</div>
        <div class="chart-bars" style="flex:1">
            <div class="bar-row">
                <div class="bar-legend" style="color:#15803d;font-size:7.5px">Skor Maks</div>
                <div class="bar-outer">
                    <div class="bar-inner" style="width:{{ $pMaks }}%;background:#22c55e;opacity:0.7"></div>
                </div>
                <div class="bar-pct" style="color:#15803d">{{ $maks }}</div>
            </div>
            <div class="bar-row">
                <div class="bar-legend" style="color:#1d4ed8;font-size:7.5px">Skor Sementara</div>
                <div class="bar-outer">
                    <div class="bar-inner" style="width:{{ $pSkor }}%;background:{{ $barC }}"></div>
                </div>
                <div class="bar-pct" style="color:#1d4ed8">{{ $skor }}</div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- ══════════════════════════════════════════════════════
     BAGIAN 5 — DETAIL CPMK PER MK (halaman baru)
══════════════════════════════════════════════════════ --}}
    @if($cpmkStats->isNotEmpty())
    <div class="page-break"></div>
    <div class="header" style="padding-bottom:8px;margin-bottom:14px">
        <div style="text-align:center">
            <div style="font-size:12px;font-weight:bold;color:#1e40af">DETAIL CAPAIAN CPMK PER MATA KULIAH</div>
            <div style="font-size:9px;color:#6b7280">Program Studi Sistem Informasi — UNISM Banjarmasin — TA {{ $ta }}</div>
        </div>
    </div>

    <div class="section-title">5. CAPAIAN CPMK PER MATA KULIAH</div>

    <table>
        <thead>
            <tr>
                <th style="width:10%">MK</th>
                <th style="width:26%">Nama MK</th>
                <th style="width:7%">CPL</th>
                <th style="width:8%">CPMK</th>
                <th style="width:8%" class="text-right">Mhs</th>
                <th style="width:8%" class="text-right">Tercapai</th>
                <th style="width:8%" class="text-right">Rata Nilai</th>
                <th style="width:8%" class="text-right">% Lulus</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cpmkStats as $r)
            @php $pct = (float)$r->pct_lulus; @endphp
            <tr>
                <td>{{ $r->mk_kode }}</td>
                <td style="font-size:8px">{{ $r->mk_nama }}</td>
                <td style="text-align:center">{{ $r->cpl_kode }}</td>
                <td style="text-align:center">{{ $r->cpmk_kode }}</td>
                <td style="text-align:center">{{ $r->total }}</td>
                <td style="text-align:center">{{ $r->tercapai }}</td>
                <td style="text-align:center">{{ $r->rata_nilai }}</td>
                <td style="text-align:center">
                    <span class="badge {{ $pct >= 80 ? 'badge-green' : ($pct >= 60 ? 'badge-yellow' : 'badge-red') }}">
                        {{ $pct }}%
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ══════════════════════════════════════════════════════
     BAGIAN 6 — REKOMENDASI OTOMATIS
══════════════════════════════════════════════════════ --}}
    <div class="section-title" style="margin-top:18px">6. REKOMENDASI OTOMATIS</div>

    @php
    $rBelum = $cplStats->where('status', 'Belum Tercapai');
    $rMonitor = $cplStats->where('status', 'Perlu Monitoring');
    $rBaik = $cplStats->where('status', 'Tercapai');
    @endphp

    @if($rBelum->isNotEmpty())
    <div class="rec-item rec-red">
        <div class="rec-title">🚨 CPL Belum Tercapai — Memerlukan Intervensi Segera</div>
        @foreach($rBelum as $r)
        <div style="margin-bottom:3px">
            <strong>{{ $r->kode }}</strong> ({{ $r->rata_nilai }} / {{ $r->pct_tercapai }}% mahasiswa lulus) —
            {{ $r->rekomendasi }}
        </div>
        @endforeach
    </div>
    @endif

    @if($rMonitor->isNotEmpty())
    <div class="rec-item rec-yellow">
        <div class="rec-title">⚠️ CPL Perlu Monitoring Berkala</div>
        @foreach($rMonitor as $r)
        <div style="margin-bottom:3px">
            <strong>{{ $r->kode }}</strong> ({{ $r->rata_nilai }} / {{ $r->pct_tercapai }}% mahasiswa lulus) —
            {{ $r->rekomendasi }}
        </div>
        @endforeach
    </div>
    @endif

    @if($rBaik->isNotEmpty())
    <div class="rec-item rec-green">
        <div class="rec-title">✅ CPL Telah Tercapai — Pertahankan Kualitas</div>
        @foreach($rBaik as $r)
        <div style="margin-bottom:2px">
            <strong>{{ $r->kode }}</strong> ({{ $r->rata_nilai }} / {{ $r->pct_tercapai }}% mahasiswa lulus)
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════ --}}
    <div class="footer">
        <div>Dicetak oleh Sistem OBE — UNISM Banjarmasin</div>
        <div>Laporan ini bersifat rahasia dan hanya untuk keperluan akreditasi internal</div>
        <div>{{ $generatedAt }}</div>
    </div>

</body>

</html>