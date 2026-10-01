<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
    h1 { font-size: 16px; text-align: center; margin: 0 0 4px; }
    .sub { text-align: center; font-size: 11px; color: #555; margin-bottom: 20px; }
    .dosen-block { margin-bottom: 20px; }
    .dosen-name { font-size: 13px; font-weight: bold; background: #dbeafe; padding: 6px 10px; border-radius: 4px; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th { background: #f1f5f9; padding: 5px 8px; border: 1px solid #d1d5db; text-align: left; font-size: 10px; }
    td { padding: 5px 8px; border: 1px solid #e5e7eb; font-size: 10px; vertical-align: top; }
    .badge-p { background: #dbeafe; color: #1e3a8a; padding: 1px 6px; border-radius: 8px; font-size: 9px; font-weight: bold; }
    .badge-a { background: #dcfce7; color: #14532d; padding: 1px 6px; border-radius: 8px; font-size: 9px; font-weight: bold; }
    .footer { margin-top: 24px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
</style>
</head>
<body>
<h1>LAPORAN PUBLIKASI DOSEN</h1>
<p class="sub">Program Studi Sistem Informasi — Universitas Sari Mulia (UNISM) Banjarmasin<br>
Dicetak: {{ now()->format('d F Y H:i') }}
@if(!empty($filters['tahun'])) &nbsp;|&nbsp; Tahun: {{ $filters['tahun'] }} @endif
@if(!empty($filters['tipe'])) &nbsp;|&nbsp; Tipe: {{ ucfirst($filters['tipe']) }} @endif
</p>

@forelse($grouped as $dosenName => $pubs)
<div class="dosen-block">
    <div class="dosen-name">👤 {{ $dosenName }} ({{ $pubs->count() }} publikasi)</div>
    <table>
        <thead>
        <tr>
            <th style="width:30px">No</th>
            <th style="width:40px">Tahun</th>
            <th>Judul</th>
            <th style="width:70px">Tipe</th>
            <th style="width:60px">Jenis</th>
            <th>Sumber</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pubs->sortByDesc('tahun') as $i => $pub)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $pub->tahun ?? '-' }}</td>
            <td>{{ $pub->judul }}</td>
            <td>
                <span class="{{ $pub->tipe==='penelitian' ? 'badge-p' : 'badge-a' }}">{{ ucfirst($pub->tipe) }}</span>
            </td>
            <td>{{ $pub->jenis ? ucfirst($pub->jenis) : '-' }}</td>
            <td>{{ $pub->sumber ?? '-' }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@empty
<p style="text-align:center;color:#9ca3af">Tidak ada data publikasi.</p>
@endforelse

<div class="footer">
    Program Studi Sistem Informasi — UNISM Banjarmasin — TA 2025/2026
</div>
</body>
</html>