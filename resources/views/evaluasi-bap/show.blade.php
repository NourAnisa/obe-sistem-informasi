@extends('layouts.dashboard')
@section('title', 'Evaluasi BAP — ' . $mk->kode)

@section('content')
<div style="padding:24px">

    {{-- Header --}}
    <div style="margin-bottom:20px">
        <div style="font-size:12px;color:#94a3b8;margin-bottom:8px">
            <a href="{{ route('evaluasi-bap.index') }}" style="color:#3b82f6;text-decoration:none">Evaluasi BAP</a>
            <span> › </span><span>{{ $mk->kode }}</span>
        </div>
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
            <div>
                <h1 style="font-size:20px;font-weight:700;color:#1e3a5f;margin:0">📊 Evaluasi Pelaksanaan Perkuliahan</h1>
                <p style="color:#64748b;margin:4px 0 0;font-size:13px">{{ $mk->nama }} · {{ $mk->kode }} · {{ $mk->sks }} SKS</p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="{{ route('nilai-mahasiswa.show', $mk->id) }}"
                    style="background:#dcfce7;color:#15803d;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600">
                    📊 Input Nilai
                </a>
                <a href="{{ route('rps.show', $mk->kode) }}" target="_blank"
                    style="background:#eff6ff;color:#1d4ed8;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600">
                    📄 Lihat RPS
                </a>
                @if($bap)
                <a href="{{ route('bap.show', $mk->kode) }}" target="_blank"
                    style="background:#f0fdf4;color:#15803d;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600">
                    📋 Lihat BAP
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px">
        @php
        $total = 16;
        $pct = fn($n) => $total > 0 ? round($n / $total * 100) : 0;
        @endphp
        <div style="background:#dcfce7;border-radius:12px;padding:14px 16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#15803d">{{ $sesuaiCount }}</div>
            <div style="font-size:11px;color:#166534;font-weight:600">✅ Sesuai</div>
            <div style="font-size:10px;color:#166534">{{ $pct($sesuaiCount) }}%</div>
        </div>
        <div style="background:#fee2e2;border-radius:12px;padding:14px 16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#dc2626">{{ $tidakCount }}</div>
            <div style="font-size:11px;color:#991b1b;font-weight:600">❌ Tidak Sesuai</div>
            <div style="font-size:10px;color:#991b1b">{{ $pct($tidakCount) }}%</div>
        </div>
        <div style="background:#fef9c3;border-radius:12px;padding:14px 16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#92400e">{{ $noEvalCount }}</div>
            <div style="font-size:11px;color:#713f12;font-weight:600">⏳ Belum Dievaluasi</div>
        </div>
        <div style="background:#e0f2fe;border-radius:12px;padding:14px 16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#075985">{{ $belumBapCount }}</div>
            <div style="font-size:11px;color:#075985;font-weight:600">📋 Belum Ada BAP</div>
        </div>
        <div style="background:#f1f5f9;border-radius:12px;padding:14px 16px;text-align:center">
            <div style="font-size:26px;font-weight:800;color:#64748b">{{ $totalResponden }}</div>
            <div style="font-size:11px;color:#64748b;font-weight:600">👨‍🎓 Responden Mhs</div>
        </div>
    </div>

    {{-- Comparison Table --}}
    <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:1100px">
            <thead>
                <tr style="background:#1e3a5f;color:#fff">
                    <th rowspan="2" style="padding:12px 10px;text-align:center;width:45px;border-right:1px solid #2d4a6e">Prtm</th>
                    <th colspan="2" style="padding:12px 10px;text-align:center;background:#1e3a5f;border-right:2px solid #4b6cb7;border-bottom:1px solid #2d4a6e">📄 Rencana (RPS)</th>
                    <th colspan="3" style="padding:12px 10px;text-align:center;background:#1a4731;border-right:2px solid #2d6a4f;border-bottom:1px solid #1a4731">📋 Pelaksanaan (BAP Dosen)</th>
                    <th colspan="3" style="padding:12px 10px;text-align:center;background:#3b1f6e;border-bottom:1px solid #4b2a8e">👨‍🎓 Evaluasi Mahasiswa</th>
                    <th rowspan="2" style="padding:12px 10px;text-align:center;width:90px">Status</th>
                </tr>
                <tr style="font-size:11px">
                    <th style="padding:8px 10px;text-align:left;background:#2d4a6e;color:#e2e8f0;border-right:1px solid #3d5a7e">Materi / Indikator</th>
                    <th style="padding:8px 10px;text-align:center;background:#2d4a6e;color:#e2e8f0;border-right:2px solid #4b6cb7;width:110px">Metode</th>
                    <th style="padding:8px 10px;text-align:left;background:#1f5738;color:#e2e8f0;border-right:1px solid #2d6a4f">Materi Aktual</th>
                    <th style="padding:8px 10px;text-align:center;background:#1f5738;color:#e2e8f0;border-right:1px solid #2d6a4f;width:100px">Metode Aktual</th>
                    <th style="padding:8px 10px;text-align:center;background:#1f5738;color:#e2e8f0;border-right:2px solid #2d6a4f;width:70px">Hadir</th>
                    <th style="padding:8px 10px;text-align:center;background:#4a2875;color:#e2e8f0;border-right:1px solid #5a3885;width:65px">Rating<br>Materi</th>
                    <th style="padding:8px 10px;text-align:center;background:#4a2875;color:#e2e8f0;border-right:1px solid #5a3885;width:65px">Rating<br>Metode</th>
                    <th style="padding:8px 10px;text-align:left;background:#4a2875;color:#e2e8f0;border-right:2px solid #7c3aed">Catatan Mhs</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                @php
                $bgRow = $row['is_uts'] ? '#fffbeb' : ($row['is_uas'] ? '#fdf2f8' : ($loop->even ? '#f9fafb' : '#fff'));
                $statusConfig = match($row['kesesuaian']) {
                'sesuai' => ['bg'=>'#dcfce7','color'=>'#166534','label'=>'✅ Sesuai'],
                'tidak_sesuai' => ['bg'=>'#fee2e2','color'=>'#991b1b','label'=>'❌ Kurang'],
                'no_eval' => ['bg'=>'#fef9c3','color'=>'#713f12','label'=>'⏳ Belum Dinilai'],
                'belum_bap' => ['bg'=>'#e0f2fe','color'=>'#075985','label'=>'📋 Belum BAP'],
                default => ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>'— Belum RPS'],
                };
                @endphp
                <tr style="background:{{ $bgRow }};border-bottom:1px solid #e5e7eb;vertical-align:top">
                    {{-- No --}}
                    <td style="padding:10px;text-align:center;font-weight:700;color:#1e3a5f;border-right:1px solid #e5e7eb">
                        {{ $row['minggu'] }}
                        @if($row['is_uts'])<br><span style="font-size:9px;color:#92400e">UTS</span>@endif
                        @if($row['is_uas'])<br><span style="font-size:9px;color:#9d174d">UAS</span>@endif
                    </td>

                    {{-- RPS Materi --}}
                    <td style="padding:10px;border-right:1px solid #e5e7eb;max-width:180px">
                        @if($row['rps'])
                        <div style="font-size:11px;color:#374151;line-height:1.5">{{ Str::limit($row['rps']->indikator ?? $row['rps']->materi ?? '—', 150) }}</div>
                        @else
                        <span style="color:#d1d5db;font-size:11px">—</span>
                        @endif
                    </td>
                    {{-- RPS Metode --}}
                    <td style="padding:10px;text-align:center;border-right:2px solid #c7d2fe">
                        @if($row['rps'])
                        <span style="background:#f5f3ff;color:#7c3aed;padding:2px 6px;border-radius:6px;font-size:10px">{{ Str::limit($row['rps']->metode_sinkron ?? '—', 30) }}</span>
                        @else
                        <span style="color:#d1d5db">—</span>
                        @endif
                    </td>

                    {{-- BAP Materi --}}
                    <td style="padding:10px;border-right:1px solid #e5e7eb;max-width:180px">
                        @if($row['bap'])
                        @if($row['is_uts'])<strong style="font-size:10px">UTS</strong><br>@endif
                        @if($row['is_uas'])<strong style="font-size:10px">UAS</strong><br>@endif
                        <div style="font-size:11px;color:#374151;line-height:1.5">{{ Str::limit($row['bap']->materi, 150) }}</div>
                        @if($row['bap']->tanggal)
                        <div style="font-size:10px;color:#94a3b8;margin-top:3px">📅 {{ $row['bap']->tanggal->format('d/m/Y') }}</div>
                        @endif
                        @else
                        <span style="color:#d1d5db;font-size:11px">—</span>
                        @endif
                    </td>
                    {{-- BAP Metode --}}
                    <td style="padding:10px;text-align:center;border-right:1px solid #e5e7eb">
                        @if($row['bap'])
                        <span style="background:#f0fdf4;color:#15803d;padding:2px 6px;border-radius:6px;font-size:10px">{{ Str::limit($row['bap']->metode_pembelajaran, 30) }}</span>
                        @else
                        <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    {{-- BAP Hadir --}}
                    <td style="padding:10px;text-align:center;border-right:2px solid #c7d2fe">
                        @if($row['bap'])
                        <div style="font-weight:700;color:#15803d;font-size:13px">{{ $row['bap']->jumlah_hadir }}</div>
                        <div style="font-size:10px;color:#64748b">I:{{ $row['bap']->jumlah_ijin }} S:{{ $row['bap']->jumlah_sakit }}</div>
                        @else
                        <span style="color:#d1d5db">—</span>
                        @endif
                    </td>

                    {{-- Mhs Rating Materi --}}
                    <td style="padding:10px;text-align:center;border-right:1px solid #e5e7eb">
                        @if($row['avg_materi'])
                        @php $mc = $row['avg_materi'] >= 4 ? '#15803d' : ($row['avg_materi'] >= 3 ? '#92400e' : '#dc2626'); @endphp
                        <span style="font-weight:700;color:{{ $mc }};font-size:15px">{{ number_format($row['avg_materi'],1) }}</span>
                        <span style="font-size:10px;color:#94a3b8">/5</span>
                        @if($row['evals']->count())
                        <div style="font-size:10px;color:#94a3b8">{{ $row['evals']->count() }} respon</div>
                        @endif
                        @else
                        <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    {{-- Mhs Rating Metode --}}
                    <td style="padding:10px;text-align:center;border-right:1px solid #e5e7eb">
                        @if($row['avg_metode'])
                        @php $mtc = $row['avg_metode'] >= 4 ? '#15803d' : ($row['avg_metode'] >= 3 ? '#92400e' : '#dc2626'); @endphp
                        <span style="font-weight:700;color:{{ $mtc }};font-size:15px">{{ number_format($row['avg_metode'],1) }}</span>
                        <span style="font-size:10px;color:#94a3b8">/5</span>
                        @else
                        <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    {{-- Catatan --}}
                    <td style="padding:10px;border-right:2px solid #e9d5ff;max-width:160px">
                        @foreach($row['evals']->whereNotNull('catatan')->where('catatan','!=','')->take(3) as $ev)
                        <div style="font-size:10px;color:#374151;border-left:2px solid #c4b5fd;padding-left:5px;margin-bottom:3px">{{ Str::limit($ev->catatan, 60) }}</div>
                        @endforeach
                    </td>
                    {{-- Status --}}
                    <td style="padding:10px;text-align:center">
                        <span style="background:{{ $statusConfig['bg'] }};color:{{ $statusConfig['color'] }};padding:4px 8px;border-radius:8px;font-size:10px;font-weight:600;display:inline-block;line-height:1.4">{{ $statusConfig['label'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Conclusion box --}}
    <div style="margin-top:20px;background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:20px 24px">
        <h3 style="margin:0 0 12px;font-size:15px;font-weight:700;color:#1e3a5f">📝 Kesimpulan Evaluasi</h3>
        @php
        $pctSesuai = $sesuaiCount + $tidakCount > 0 ? round($sesuaiCount / ($sesuaiCount + $tidakCount) * 100) : 0;
        @endphp
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
            <div>
                <p style="margin:0 0 6px;font-size:13px;font-weight:600;color:#374151">Kesesuaian Pelaksanaan</p>
                <div style="background:#f1f5f9;border-radius:8px;overflow:hidden;height:12px">
                    <div style="background:#15803d;height:100%;width:{{ $pctSesuai }}%;transition:.3s"></div>
                </div>
                <p style="margin:4px 0 0;font-size:12px;color:#64748b">{{ $pctSesuai }}% pertemuan sesuai dengan RPS (berdasarkan evaluasi mahasiswa)</p>
            </div>
            <div>
                <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#374151">Kelengkapan Data</p>
                <p style="margin:0;font-size:12px;color:#64748b">
                    • BAP tersedia: <strong>{{ 16 - $belumBapCount }}/16</strong> pertemuan<br>
                    • Dievaluasi mhs: <strong>{{ $sesuaiCount + $tidakCount + $noEvalCount }}/16</strong> pertemuan<br>
                    • Total responden: <strong>{{ $totalResponden }}</strong> mahasiswa
                </p>
            </div>
            <div>
                <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#374151">Rekomendasi</p>
                <p style="margin:0;font-size:12px;color:#64748b">
                    @if($pctSesuai >= 80)
                    ✅ Pelaksanaan perkuliahan <strong>sangat sesuai</strong> dengan RPS. Pertahankan kualitas.
                    @elseif($pctSesuai >= 60)
                    ⚠️ Sebagian besar sesuai. Tinjau pertemuan yang kurang sesuai.
                    @elseif($pctSesuai > 0)
                    ❌ Banyak pertemuan tidak sesuai RPS. Evaluasi mendalam diperlukan.
                    @else
                    — Belum cukup data evaluasi mahasiswa untuk membuat kesimpulan.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endsection