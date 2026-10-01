<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reminder Evaluasi BAP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; margin: 0; background: #f4f6f9; }
        .wrapper { max-width: 640px; margin: 30px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.08); overflow: hidden; }
        .header { background: linear-gradient(135deg, #1a56db, #3b82f6); padding: 28px 32px; color: #fff; }
        .header h1 { margin: 0 0 4px; font-size: 19px; }
        .header p  { margin: 0; font-size: 13px; opacity: .85; }
        .body   { padding: 28px 32px; }
        .info-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 18px; border-radius: 4px; margin-bottom: 20px; }
        .cta-btn { display: inline-block; margin: 20px 0; padding: 13px 28px; background: #1a56db; color: #fff; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 15px; }
        .token-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 12px 18px; border-radius: 6px; font-family: monospace; font-size: 16px; letter-spacing: 2px; text-align: center; margin: 16px 0; color: #1a56db; font-weight: bold; }
        .footer { background: #f8f8f8; border-top: 1px solid #eee; padding: 16px 32px; font-size: 12px; color: #888; }
        .footer a { color: #1a56db; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>📋 Reminder: Evaluasi Perkuliahan</h1>
        <p>Tahun Akademik: {{ $ta }} — Minggu ke-{{ $minggu }}</p>
    </div>
    <div class="body">
        <p>Yth. <strong>{{ $mahasiswaNama }}</strong>,</p>

        <div class="info-box">
            <p style="margin:0;">Kamu belum mengisi <strong>evaluasi perkuliahan</strong> untuk mata kuliah:</p>
            <p style="margin: 8px 0 0; font-size: 15px; font-weight: bold;">{{ $mataKuliahNama }}</p>
        </div>

        <p>Evaluasi perkuliahan sangat penting untuk perbaikan proses pembelajaran. Mohon segera isi menggunakan tombol di bawah atau masukkan token secara manual di sistem.</p>

        <div class="token-box">{{ $token }}</div>

        <a href="{{ url('/bap/evaluasi?token=' . $token) }}" class="cta-btn">Isi Evaluasi Sekarang →</a>

        <p style="font-size: 12px; color: #888; margin-top: 16px;">
            Link berlaku selama 7 hari. Jika ada masalah, hubungi Dosen Pengampu atau Admin.
        </p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Sistem OBE — Universitas Sari Mulia &nbsp;|&nbsp;
        <a href="{{ url('/') }}">Buka Sistem</a>
    </div>
</div>
</body>
</html>
