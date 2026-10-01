<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal Dosen — {{ $dosen_name }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 10pt; color:#222; }
  .header { text-align:center; margin-bottom:12px; border-bottom:2px solid #333; padding-bottom:8px; }
  .header h2 { font-size:13pt; }
  .header p { font-size:9pt; color:#555; }
  table { width:100%; border-collapse:collapse; }
  th { background:#553c9a; color:#fff; padding:6px 8px; font-size:9pt; text-align:left; }
  td { padding:5px 8px; border:1px solid #ccc; font-size:9pt; }
  tr:nth-child(even) td { background:#f9f5ff; }
  .footer { font-size:8pt; color:#777; text-align:right; margin-top:8px; }
</style>
</head>
<body>
<div class="header">
  <h2>Jadwal Mengajar — {{ $dosen_name }}</h2>
  <p>Tahun Akademik {{ $ta }}</p>
</div>

@if($schedules->isEmpty())
  <p style="text-align:center;color:#999;padding:20px">Tidak ada jadwal yang ditemukan.</p>
@else
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Hari</th>
      <th>Jam</th>
      <th>Kode MK</th>
      <th>Nama Mata Kuliah</th>
      <th>SKS</th>
      <th>Kelas</th>
      <th>Ruangan</th>
    </tr>
  </thead>
  <tbody>
    @foreach($schedules->sortBy(['day_of_week','start_time']) as $i => $s)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $s->day_of_week }}</td>
      <td>{{ $s->start_time }} – {{ $s->end_time }}</td>
      <td>{{ $s->mataKuliah?->kode ?? '-' }}</td>
      <td>{{ $s->mataKuliah?->nama ?? '-' }}</td>
      <td style="text-align:center">{{ $s->mataKuliah?->sks ?? '-' }}</td>
      <td>{{ $s->class_name ?? '-' }}</td>
      <td>{{ $s->room?->code ?? '-' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">Dicetak: {{ $generated }}</div>
</body>
</html>