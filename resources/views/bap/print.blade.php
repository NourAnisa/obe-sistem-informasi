<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAP — {{ $mk->kode }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000;
            background: #fff;
        }

        .bap-doc {
            width: 100%;
            max-width: 270mm;
            margin: 0 auto;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
        }

        .header-cell {
            font-weight: bold;
            text-align: center;
            font-size: 11pt;
        }

        .green {
            background-color: #A8D08D;
            font-weight: bold;
            text-align: center;
        }

        .uts-row {
            background-color: #fff2cc;
        }

        .uas-row {
            background-color: #fce4d6;
        }

        .no-border {
            border: none;
        }

        .sign-box {
            border-top: 1px solid #000;
            min-height: 40px;
        }

        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: #1e40af;
            color: #fff;
            padding: 8px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: system-ui, sans-serif;
            font-size: 13px;
        }

        @media screen {
            body {
                background: #e5e7eb;
                padding-top: 50px;
            }

            .bap-doc {
                background: #fff;
                box-shadow: 0 4px 24px rgba(0, 0, 0, .18);
                padding: 12mm 15mm;
                margin: 20px auto;
            }
        }
    </style>
</head>

<body>

    <div class="no-print no-print-bar">
        <span>🖨️ BAP — {{ $mk->kode }} · {{ $mk->nama }}</span>
        <div style="display:flex;gap:8px">
            <a href="{{ route('bap.show', $mk->kode) }}" style="background:rgba(255,255,255,.2);color:#fff;padding:5px 14px;border-radius:6px;text-decoration:none;font-size:12px">✏️ Edit</a>
            <a href="{{ route('bap.word', $mk->kode) }}" style="background:#10b981;color:#fff;padding:5px 14px;border-radius:6px;text-decoration:none;font-size:12px">📝 Word</a>
            <button onclick="window.print()" style="background:#f59e0b;color:#1f2937;padding:5px 14px;border-radius:6px;border:none;cursor:pointer;font-size:12px;font-weight:700">🖨️ Cetak</button>
        </div>
    </div>

    <div class="bap-doc">

        {{-- ══ KOP DOKUMEN ══ --}}
        <table style="margin-bottom:4px">
            <tr>
                <td rowspan="2" style="width:80px;text-align:center;border:2px solid #000;padding:4px">
                    @php
                    $logoPath = base_path('Bahan/logo_unism.png');
                    $logoSrc = file_exists($logoPath)
                    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                    : asset('images/logo_unism.png');
                    @endphp
                    <img src="{{ $logoSrc }}" style="width:60px;height:auto">
                </td>
                <td style="text-align:center;border:2px solid #000;padding:4px;font-weight:bold;font-size:11pt">
                    UNIVERSITAS SARI MULIA<br>
                    <span style="font-weight:normal;font-size:10pt">FAKULTAS SAINS DAN TEKNOLOGI</span><br>
                    <span style="font-weight:normal;font-size:10pt">PROGRAM STUDI SISTEM INFORMASI</span>
                </td>
                <td style="text-align:center;border:2px solid #000;padding:6px;background-color:#A8D08D;font-weight:bold;font-size:12pt;vertical-align:middle;min-width:150px">
                    BERITA ACARA<br>PERKULIAHAN
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border:2px solid #000;padding:4px">
                    <table style="width:100%;border:none">
                        <tr>
                            <td style="border:none;width:50%"><strong>Nama MK:</strong> {{ $mk->nama }}</td>
                            <td style="border:none"><strong>Semester Aktif:</strong> {{ $bap->semester_aktif }}</td>
                        </tr>
                        <tr>
                            <td style="border:none"><strong>Kode MK:</strong> {{ $mk->kode }}</td>
                            <td style="border:none"><strong>Kelas:</strong> {{ $bap->kelas }}</td>
                        </tr>
                        <tr>
                            <td style="border:none"><strong>SKS:</strong> {{ $mk->sks }} SKS</td>
                            <td style="border:none"><strong>Ruangan:</strong> {{ $bap->ruangan ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td style="border:none"><strong>Dosen Pengampu:</strong> {{ $mk->pjmk ?: '—' }}</td>
                            <td style="border:none"><strong>Jml Mhs Terdaftar:</strong> {{ $bap->jumlah_mahasiswa ?: '—' }} mahasiswa</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- ══ TABEL BAP ══ --}}
        <table>
            <thead>
                <tr class="green">
                    <th rowspan="2" style="width:40px;text-align:center">No.<br>Prtm</th>
                    <th rowspan="2" style="width:85px;text-align:center">Tanggal</th>
                    <th rowspan="2" style="text-align:center">Pokok Bahasan / Materi Perkuliahan</th>
                    <th rowspan="2" style="width:160px;text-align:center">Metode Pembelajaran</th>
                    <th rowspan="2" style="width:45px;text-align:center">Jml Mhs<br>Hadir</th>
                    <th colspan="3" style="text-align:center">Jml Mhs Tdk Hadir</th>
                    <th rowspan="2" style="width:75px;text-align:center">Paraf dan<br>Nama Dosen</th>
                    <th rowspan="2" style="width:75px;text-align:center">Tanda Tangan<br>PJMK<br>(mahasiswa)</th>
                    <th rowspan="2" style="width:90px;text-align:center">Keterangan</th>
                </tr>
                <tr class="green">
                    <th style="width:30px;text-align:center">I</th>
                    <th style="width:30px;text-align:center">S</th>
                    <th style="width:30px;text-align:center">TK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bap->bapPertemuans->sortBy('minggu') as $pt)
                @php
                $isUts = (int)$pt->minggu === 8;
                $isUas = (int)$pt->minggu === 16;
                @endphp
                <tr @if($isUts) class="uts-row" @elseif($isUas) class="uas-row" @endif>
                    <td style="text-align:center;font-weight:bold">{{ $pt->minggu }}</td>
                    <td style="text-align:center;font-size:9pt">
                        {{ $pt->tanggal ? $pt->tanggal->translatedFormat('d M Y') : '…………' }}
                    </td>
                    <td style="font-size:9.5pt">
                        @if($isUts)
                        <strong>UJIAN TENGAH SEMESTER (UTS)</strong><br>
                        <em style="font-size:9pt">{{ $pt->materi ?: 'Materi Pertemuan 1–7' }}</em>
                        @elseif($isUas)
                        <strong>EVALUASI AKHIR SEMESTER (UAS)</strong><br>
                        <em style="font-size:9pt">{{ $pt->materi ?: 'Materi Pertemuan 9–15' }}</em>
                        @else
                        {{ $pt->materi ?: '—' }}
                        @endif
                    </td>
                    <td style="font-size:9pt">{{ $pt->metode_pembelajaran }}</td>
                    <td style="text-align:center">{{ $pt->jumlah_hadir ?: '…' }}</td>
                    <td style="text-align:center">{{ $pt->jumlah_ijin ?: '' }}</td>
                    <td style="text-align:center">{{ $pt->jumlah_sakit ?: '' }}</td>
                    <td style="text-align:center">{{ $pt->jumlah_tk ?: '' }}</td>
                    <td style="text-align:center">&nbsp;</td>
                    <td style="text-align:center">&nbsp;</td>
                    <td style="font-size:9pt">{{ $pt->keterangan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ══ TANDA TANGAN ══ --}}
        <table style="margin-top:12px;border:none">
            <tr style="border:none">
                <td style="border:none;width:50%;text-align:center;vertical-align:top;padding:0 20px">
                    <p style="margin:0 0 4px">Mengetahui,</p>
                    <p style="margin:0 0 4px;font-weight:bold">Ketua Program Studi Sistem Informasi</p>
                    <p style="margin:60px 0 0">&nbsp;</p>
                    <p style="margin:0"><strong>{{ $kaprodi?->name ?: 'Nor Anisa, S.Kom., M.Kom.' }}</strong></p>
                    <p style="margin:0;font-size:9pt">NIK. {{ $kaprodi?->nik ?: '………………………' }}</p>
                </td>
                <td style="border:none;width:50%;text-align:center;vertical-align:top;padding:0 20px">
                    <p style="margin:0 0 4px">Banjarmasin, ………………………</p>
                    <p style="margin:0 0 4px;font-weight:bold">Dosen Pengampu,</p>
                    <p style="margin:60px 0 0">&nbsp;</p>
                    <p style="margin:0"><strong>{{ $mk->pjmk ?: '………………………………' }}</strong></p>
                    <p style="margin:0;font-size:9pt">NIK. ………………………</p>
                </td>
            </tr>
        </table>

        @if($bap->catatan)
        <p style="margin-top:8px;font-size:9pt"><strong>Catatan:</strong> {{ $bap->catatan }}</p>
        @endif

    </div>
</body>

</html>