<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" lang="id">

<head>
    <meta charset="UTF-8">
    <title>BAP — {{ $mk->kode }}</title>
    <!--[if gte mso 9]>
<xml><w:WordDocument><w:View>Print</w:View><w:Zoom>100</w:Zoom><w:DoNotOptimizeForBrowser/></w:WordDocument></xml>
<![endif]-->
    <style>
        @page {
            size: 297mm 210mm;
            mso-page-orientation: landscape;
            margin: 15mm 18mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
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

        .green {
            background-color: #A8D08D;
            font-weight: bold;
            text-align: center;
            mso-pattern: auto none;
        }

        .uts-row td {
            background-color: #fff2cc;
            mso-pattern: auto none;
        }

        .uas-row td {
            background-color: #fce4d6;
            mso-pattern: auto none;
        }
    </style>
</head>

<body>

    {{-- KOP --}}
    <table style="margin-bottom:4px">
        <tr>
            <td rowspan="2" style="width:80px;text-align:center;padding:4px">
                @php
                $logoPath = base_path('Bahan/logo_unism.png');
                $logoSrc = file_exists($logoPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                : '';
                @endphp
                @if($logoSrc)<img src="{{ $logoSrc }}" style="width:60px;height:auto">@endif
            </td>
            <td style="text-align:center;padding:4px;font-weight:bold;font-size:11pt">
                UNIVERSITAS SARI MULIA<br>
                <span style="font-weight:normal;font-size:10pt">FAKULTAS SAINS DAN TEKNOLOGI</span><br>
                <span style="font-weight:normal;font-size:10pt">PROGRAM STUDI SISTEM INFORMASI</span>
            </td>
            <td class="green" style="font-size:12pt;min-width:150px;vertical-align:middle">
                BERITA ACARA<br>PERKULIAHAN
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding:4px">
                <table style="border:none">
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
                        <td style="border:none"><strong>Jml Mahasiswa Terdaftar:</strong> {{ $bap->jumlah_mahasiswa ?: '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TABEL PERTEMUAN --}}
    <table>
        <thead>
            <tr>
                <th class="green" rowspan="2" style="width:40px">No.<br>Prtm</th>
                <th class="green" rowspan="2" style="width:85px">Tanggal</th>
                <th class="green" rowspan="2">Pokok Bahasan / Materi Perkuliahan</th>
                <th class="green" rowspan="2" style="width:160px">Metode Pembelajaran</th>
                <th class="green" rowspan="2" style="width:45px">Jml Mhs<br>Hadir</th>
                <th class="green" colspan="3" style="text-align:center">Jml Mhs Tdk Hadir</th>
                <th class="green" rowspan="2" style="width:75px">Paraf dan<br>Nama Dosen</th>
                <th class="green" rowspan="2" style="width:75px">Tanda Tangan<br>PJMK<br>(mahasiswa)</th>
                <th class="green" rowspan="2" style="width:90px">Keterangan</th>
            </tr>
            <tr>
                <th class="green" style="width:30px">I</th>
                <th class="green" style="width:30px">S</th>
                <th class="green" style="width:30px">TK</th>
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
                <td style="text-align:center;font-size:9pt">{{ $pt->tanggal ? $pt->tanggal->translatedFormat('d M Y') : '' }}</td>
                <td style="font-size:9.5pt">
                    @if($isUts)<strong>UJIAN TENGAH SEMESTER (UTS)</strong><br>@elseif($isUas)<strong>EVALUASI AKHIR SEMESTER (UAS)</strong><br>@endif
                    {{ $pt->materi }}
                </td>
                <td style="font-size:9pt">{{ $pt->metode_pembelajaran }}</td>
                <td style="text-align:center">{{ $pt->jumlah_hadir ?: '' }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td style="font-size:9pt">{{ $pt->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table style="margin-top:12px;border:none">
        <tr style="border:none">
            <td style="border:none;width:50%;text-align:center;vertical-align:top;padding:0 20px">
                <p>Mengetahui,<br><strong>Ketua Program Studi Sistem Informasi</strong></p>
                <p style="margin-top:50px">&nbsp;</p>
                <p><strong>{{ $kaprodi?->name ?: 'Nor Anisa, S.Kom., M.Kom.' }}</strong><br>NIK. {{ $kaprodi?->nik ?: '………………………' }}</p>
            </td>
            <td style="border:none;width:50%;text-align:center;vertical-align:top;padding:0 20px">
                <p>Banjarmasin, ………………………<br><strong>Dosen Pengampu,</strong></p>
                <p style="margin-top:50px">&nbsp;</p>
                <p><strong>{{ $mk->pjmk ?: '………………………………' }}</strong><br>NIK. ………………………</p>
            </td>
        </tr>
    </table>

</body>

</html>