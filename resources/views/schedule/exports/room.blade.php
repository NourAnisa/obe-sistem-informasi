<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal Ruangan {{ $room->code }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; font-size: 10pt; color:#222; }
  .header { text-align:center; margin-bottom:12px; border-bottom:2px solid #333; padding-bottom:8px; }
  .header h2 { font-size:13pt; }
  .header p { font-size:9pt; color:#555; }
  table { width:100%; border-collapse:collapse; margin-bottom:14px; }
  th { background:#276749; color:#fff; padding:6px 8px; font-size:9pt; text-align:center; }
  td { padding:5px 8px; border:1px solid #ccc; font-size:9pt; vertical-align:middle; text-align:center; }
  td.slot { text-align:left; font-weight:bold; background:#f0fff4; }
  tr:nth-child(even) td { background:#f7f9fc; }
  .footer { font-size:8pt; color:#777; text-align:right; margin-top:8px; }
  .locked { color:#c53030; font-size:8pt; }
</style>
</head>
<body>
<div class="header">
  <h2>Jadwal Ruangan — {{ $room->code }} ({{ $room->name }})</h2>
  <p>Gedung: {{ $room->building ?? '-' }} &nbsp;|&nbsp; Lantai: {{ $room->floor ?? '-' }} &nbsp;|&nbsp; Kapasitas: {{ $room->capacity }} orang</p>
  <p>Tahun Akademik {{ $ta }}</p>
</div>

<table>
  <thead>
    <tr>
      <th style="width:12%">Waktu</th>
      @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $day)
        <th>{{ $day }}</th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    @foreach($slots as $slot)
    <tr>
      <td class="slot">{{ $slot }}</td>
      @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $day)
        @php
          $match = $grid[$day]->first(fn($s) => $s->start_time === $slot);
        @endphp
        <td>
          @if($match)
            <strong>{{ $match->mataKuliah?->kode }}</strong><br>
            <small>{{ $match->mataKuliah?->nama }}</small><br>
            <small>{{ $match->start_time }}–{{ $match->end_time }}</small>
            @if($match->is_locked) <span class="locked">🔒</span> @endif
          @else
            <span style="color:#ccc">—</span>
          @endif
        </td>
      @endforeach
    </tr>
    @endforeach
  </tbody>
</table>

<p style="font-size:9pt">Total jadwal di ruangan ini: <strong>{{ $schedules->count() }}</strong></p>
<div class="footer">Dicetak: {{ $generated }}</div>
</body>
</html>