<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Early Warning OBE</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; margin: 0; padding: 0; background: #f4f6f9; }
        .wrapper { max-width: 680px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #c0392b, #e74c3c); padding: 28px 32px; color: #fff; }
        .header h1 { margin: 0 0 4px; font-size: 20px; }
        .header p  { margin: 0; font-size: 13px; opacity: .85; }
        .body   { padding: 28px 32px; }
        .greeting { font-size: 15px; margin-bottom: 16px; }
        .summary-box { background: #fff5f5; border-left: 4px solid #e74c3c; padding: 14px 18px; border-radius: 4px; margin-bottom: 20px; }
        .summary-box p { margin: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
        th { background: #c0392b; color: #fff; padding: 9px 12px; text-align: left; }
        td { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
        tr:nth-child(even) td { background: #fafafa; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .badge-kritis   { background: #fde8e8; color: #c0392b; }
        .badge-intervensi { background: #fef3cd; color: #856404; }
        .footer { background: #f8f8f8; border-top: 1px solid #eee; padding: 18px 32px; font-size: 12px; color: #888; }
        .footer a { color: #c0392b; text-decoration: none; }
        .cta { display: inline-block; margin-top: 20px; padding: 11px 22px; background: #c0392b; color: #fff; border-radius: 5px; text-decoration: none; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>⚠️ Early Warning — Ketercapaian CPL</h1>
        <p>Tahun Akademik: {{ $ta }} &nbsp;|&nbsp; Sistem OBE</p>
    </div>
    <div class="body">
        <p class="greeting">Yth. Bapak/Ibu <strong>{{ $dosenNama }}</strong>,</p>

        <div class="summary-box">
            <p>Terdapat <strong>{{ count($warningList) }} catatan mahasiswa PA</strong> Anda yang
               <strong>belum mencapai CPL</strong> pada tahun akademik {{ $ta }}.
               Mohon segera ditindaklanjuti dengan bimbingan atau program remedial.</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>CPL</th>
                    <th>Nilai</th>
                    <th>Target</th>
                    <th>Gap</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($warningList as $w)
                <tr>
                    <td>{{ $w['nim'] }}</td>
                    <td>{{ $w['nama'] }}</td>
                    <td><strong>{{ $w['cpl_kode'] }}</strong></td>
                    <td>{{ $w['nilai_cpl'] }}</td>
                    <td>{{ $w['threshold'] }}</td>
                    <td style="color:#c0392b;font-weight:bold;">{{ $w['gap'] }}</td>
                    <td>
                        <span class="badge {{ $w['status'] === 'Kritis' ? 'badge-kritis' : 'badge-intervensi' }}">
                            {{ $w['status'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ url('/early-warning') }}" class="cta">Lihat Detail Early Warning →</a>

        <p style="margin-top: 24px; font-size: 13px; color: #666;">
            Email ini dikirim otomatis oleh Sistem OBE. Jika ada pertanyaan, hubungi Kaprodi.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Sistem OBE — Universitas Sari Mulia &nbsp;|&nbsp;
        <a href="{{ url('/') }}">Buka Sistem</a>
    </div>
</div>
</body>
</html>
