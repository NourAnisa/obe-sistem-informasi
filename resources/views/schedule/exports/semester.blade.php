<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal Semester {{ $semester }} — TA {{ $ta }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 10pt; color:#222; }
  .header { text-align:center; margin-bottom:12px; border-bottom:2px solid #333; padding-bottom:8px; }
  .header h2 { font-size:13pt; }
  .header p { font-size:9pt; color:#555; }
  table { width:100%; border-collapse:collapse; margin-bottom:14px; }
  th { background:#2c5282; color:#fff; padding:6px 8px; font-size:9pt; text-align:left; }
  td { padding:5px 8px; border:1px solid #ccc; font-size:9pt; vertical-align:top; }
  tr:nth-child(even) td { background:#f7f9fc; }
  .day-header { background:#ebf4ff; color:#2c5282; font-weight:bold; padding:6px 8px; font-size:10pt; }
  .locked { color:#c53030; font-size:8pt; }
  .footer { font-size:8pt; color:#777; text-align:right; margin-top:8px; }
</style>
</head>
<body>
<div class="header">
  <h2>Jadwal Perkuliahan Semester {{ $semester }}</h2>
  <p>Program Studi Sistem Informasi — Universitas Sari Mulia Banjarmasin</p>
  <p>Tahun Akademik {{ $ta }} &nbsp;|&nbsp; Total: {{ $total }} Mata Kuliah</p>
</div>

@foreach($grouped as $day => $schedules)
<table>
  <thead>
    <tr><th colspan="7" class="day-header">📅 {{ $day }}</th></tr>
    <tr>
      <th style="width:6%">No</th>
      <th style="width:12%">Kode MK</th>
      <th style="width:22%">Nama Mata Kuliah</th>
      <th style="width:8%">SKS</th>
      <th style="width:10%">Jam</th>
      <th style="width:14%">Ruangan</th>
      <th style="width:12%">Kelas</th>
    </tr>
  </thead>
  <tbody>
    @foreach($schedules->sortBy('start_time') as $i => $s)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $s->mataKuliah?->kode ?? '-' }}</td>
      <td>{{ $s->mataKuliah?->nama ?? '-' }}</td>
      <td style="text-align:center">{{ $s->mataKuliah?->sks ?? '-' }}</td>
      <td>{{ $s->start_time }} – {{ $s->end_time }}</td>
      <td>{{ $s->room?->code ? $s->room->code . ' / ' . $s->room->name : '-' }}</td>
      <td>
        {{ $s->class_name ?? '-' }}
        @if($s->is_locked) <span class="locked">🔒</span> @endif
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
@endforeach

<div class="footer">Dicetak: {{ $generated }}</div>
</body>
</html>