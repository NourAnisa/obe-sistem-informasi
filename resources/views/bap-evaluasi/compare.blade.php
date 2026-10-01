@extends('layouts.dashboard')
@section('title', 'Perbandingan BAP vs Evaluasi Mahasiswa — ' . $mk->kode)

@section('content')
<div style="padding:24px">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
            <div style="font-size:12px;color:#94a3b8;margin-bottom:8px">
                <a href="{{ route('bap.show', $mk->kode) }}" style="color:#3b82f6;text-decoration:none">BAP</a>
                <span> › </span><span>{{ $mk->kode }} › Evaluasi Mahasiswa</span>
            </div>
            <h1 style="font-size:20px;font-weight:700;color:#1e3a5f;margin:0">📊 Perbandingan BAP vs Evaluasi Mahasiswa</h1>
            <p style="color:#64748b;margin:4px 0 0;font-size:13px">{{ $mk->nama }} · {{ $bap->semester_aktif }}</p>
        </div>
        <a href="{{ route('bap.show', $mk->kode) }}"
            style="padding:9px 18px;border:1px solid #d1d5db;border-radius:8px;color:#374151;text-decoration:none;font-size:13px">
            ← Kembali ke BAP
        </a>
    </div>

    {{-- Summary cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px">
        @php
        $allEvals = $bap->bapPertemuans->flatMap->mahasiswaEvaluasis;
        $totalResponden = $allEvals->pluck('mahasiswa_id')->unique()->count();
        $avgMateri = $allEvals->avg('kesesuaian_materi');
        $avgMetode = $allEvals->avg('kesesuaian_metode');
        $kehadiranDist = $allEvals->groupBy('kehadiran');
        @endphp
        <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.08);text-align:center">
            <div style="font-size:28px;font-weight:800;color:#1d4ed8">{{ $totalMahasiswa }}</div>
            <div style="font-size:12px;color:#64748b">Mhs Terdaftar</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.08);text-align:center">
            <div style="font-size:28px;font-weight:800;color:#15803d">{{ $totalResponden }}</div>
            <div style="font-size:12px;color:#64748b">Mhs Mengisi Evaluasi</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.08);text-align:center">
            <div style="font-size:28px;font-weight:800;color:#92400e">{{ $avgMateri ? number_format($avgMateri,1) : '—' }}</div>
            <div style="font-size:12px;color:#64748b">Rata-rata Kesesuaian Materi</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.08);text-align:center">
            <div style="font-size:28px;font-weight:800;color:#7c3aed">{{ $avgMetode ? number_format($avgMetode,1) : '—' }}</div>
            <div style="font-size:12px;color:#64748b">Rata-rata Kesesuaian Metode</div>
        </div>
    </div>

    {{-- Per-pertemuan table --}}
    <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:1000px">
            <thead>
                <tr style="background:#1e3a5f;color:#fff">
                    <th style="padding:12px 10px;text-align:center;width:45px">Prtm</th>
                    <th style="padding:12px 10px;text-align:center;width:80px">Tanggal</th>
                    <th colspan="2" style="padding:12px 10px;text-align:center;background:#1e3a5f;border-right:2px solid #4b6cb7">📄 Data Dosen (BAP)</th>
                    <th colspan="4" style="padding:12px 10px;text-align:center;background:#15415c">👨‍🎓 Evaluasi Mahasiswa</th>
                </tr>
                <tr style="background:#2d4a6e;color:#e2e8f0;font-size:11px">
                    <th style="padding:8px 10px"></th>
                    <th style="padding:8px 10px"></th>
                    <th style="padding:8px 10px;text-align:left;border-right:1px solid #4b6cb7">Materi / Metode Dosen</th>
                    <th style="padding:8px 10px;text-align:center;border-right:2px solid #4b6cb7">Hadir / I / S / TK</th>
                    <th style="padding:8px 10px;text-align:center">Responden</th>
                    <th style="padding:8px 10px;text-align:center">Rating Materi</th>
                    <th style="padding:8px 10px;text-align:center">Rating Metode</th>
                    <th style="padding:8px 10px;text-align:left">Catatan Mahasiswa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bap->bapPertemuans->sortBy('minggu') as $pt)
                @php
                $isUts = (int)$pt->minggu === 8;
                $isUas = (int)$pt->minggu === 16;
                $rowBg = $isUts ? '#fffbeb' : ($isUas ? '#fdf2f8' : ($loop->even ? '#f9fafb' : '#fff'));
                $evals = $pt->mahasiswaEvaluasis;
                $avgM = $evals->avg('kesesuaian_materi');
                $avgMtd = $evals->avg('kesesuaian_metode');
                $hadirCnt = $evals->where('kehadiran','hadir')->count();
                $ijinCnt = $evals->where('kehadiran','ijin')->count();
                $sakitCnt = $evals->where('kehadiran','sakit')->count();
                $tkCnt = $evals->where('kehadiran','tk')->count();
                @endphp
                <tr style="background:{{ $rowBg }};border-bottom:1px solid #e5e7eb;vertical-align:top">
                    <td style="padding:10px;text-align:center;font-weight:700;color:#1e3a5f">
                        {{ $pt->minggu }}
                        @if($isUts)<br><span style="font-size:9px;color:#92400e">UTS</span>@endif
                        @if($isUas)<br><span style="font-size:9px;color:#9d174d">UAS</span>@endif
                    </td>
                    <td style="padding:10px;text-align:center;font-size:11px;color:#64748b">
                        {{ $pt->tanggal ? $pt->tanggal->format('d/m/Y') : '—' }}
                    </td>
                    <td style="padding:10px;border-right:1px solid #e5e7eb">
                        @if($isUts)<strong>UTS</strong><br>@elseif($isUas)<strong>UAS</strong><br>@endif
                        <span style="font-size:11px">{{ Str::limit($pt->materi, 150) }}</span>
                        <br><span style="font-size:10px;color:#7c3aed;background:#f5f3ff;padding:1px 6px;border-radius:6px">{{ $pt->metode_pembelajaran }}</span>
                    </td>
                    <td style="padding:10px;text-align:center;border-right:2px solid #c7d2fe">
                        <div style="font-size:13px;font-weight:700;color:#15803d">{{ $pt->jumlah_hadir }}</div>
                        <div style="font-size:10px;color:#64748b">I:{{ $pt->jumlah_ijin }} S:{{ $pt->jumlah_sakit }} TK:{{ $pt->jumlah_tk }}</div>
                    </td>
                    <td style="padding:10px;text-align:center">
                        @if($evals->count())
                        <div style="font-weight:700;color:#1d4ed8">{{ $evals->count() }}</div>
                        <div style="font-size:10px;color:#64748b">Hadir:{{ $hadirCnt }} I:{{ $ijinCnt }} S:{{ $sakitCnt }} TK:{{ $tkCnt }}</div>
                        @else
                        <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    <td style="padding:10px;text-align:center">
                        @if($avgM)
                        @php $color = $avgM >= 4 ? '#15803d' : ($avgM >= 3 ? '#92400e' : '#dc2626'); @endphp
                        <span style="font-weight:700;color:{{ $color }};font-size:16px">{{ number_format($avgM,1) }}</span>
                        <span style="font-size:10px;color:#94a3b8">/5</span>
                        @else <span style="color:#d1d5db">—</span> @endif
                    </td>
                    <td style="padding:10px;text-align:center">
                        @if($avgMtd)
                        @php $color2 = $avgMtd >= 4 ? '#15803d' : ($avgMtd >= 3 ? '#92400e' : '#dc2626'); @endphp
                        <span style="font-weight:700;color:{{ $color2 }};font-size:16px">{{ number_format($avgMtd,1) }}</span>
                        <span style="font-size:10px;color:#94a3b8">/5</span>
                        @else <span style="color:#d1d5db">—</span> @endif
                    </td>
                    <td style="padding:10px;font-size:11px;color:#374151">
                        @foreach($evals->whereNotNull('catatan')->where('catatan','!=','') as $ev)
                        <div style="border-left:2px solid #bfdbfe;padding-left:6px;margin-bottom:4px">
                            <span style="color:#94a3b8;font-size:10px">{{ $ev->mahasiswa?->nim }}</span><br>
                            {{ $ev->catatan }}
                        </div>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Detail per mahasiswa --}}
    <div style="margin-top:24px;background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:20px">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;color:#1e3a5f">👤 Detail Materi per Mahasiswa</h3>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#f1f5f9">
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Mahasiswa</th>
                        @foreach($bap->bapPertemuans->sortBy('minggu') as $pt)
                        <th style="padding:8px 6px;text-align:center;border-bottom:2px solid #e5e7eb;min-width:36px;font-size:10px">{{ $pt->minggu }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                    $allMhs = $bap->bapPertemuans->flatMap->mahasiswaEvaluasis->pluck('mahasiswa')->unique('id')->filter()->sortBy('nama');
                    @endphp
                    @foreach($allMhs as $mhs)
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:8px 12px;font-size:11px">
                            <span style="font-weight:600">{{ $mhs->nim }}</span><br>
                            <span style="color:#64748b">{{ Str::limit($mhs->nama, 30) }}</span>
                        </td>
                        @foreach($bap->bapPertemuans->sortBy('minggu') as $pt)
                        @php
                        $ev = $pt->mahasiswaEvaluasis->firstWhere('mahasiswa_id', $mhs->id);
                        $kdBg = $ev ? match($ev->kehadiran) {
                        'hadir' => '#dcfce7', 'ijin' => '#fef9c3', 'sakit' => '#dbeafe', 'tk' => '#fee2e2', default => '#f9fafb'
                        } : '#f9fafb';
                        $kdText = $ev ? strtoupper(substr($ev->kehadiran,0,1)) : '—';
                        @endphp
                        <td style="padding:6px;text-align:center;background:{{ $kdBg }};font-size:10px;font-weight:600" title="{{ $ev?->materi_dirasakan }}">{{ $kdText }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                    @if($allMhs->isEmpty())
                    <tr>
                        <td colspan="{{ $bap->bapPertemuans->count() + 1 }}" style="padding:20px;text-align:center;color:#94a3b8">Belum ada mahasiswa yang mengisi evaluasi</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div style="margin-top:10px;font-size:11px;color:#94a3b8">
            <span style="background:#dcfce7;padding:2px 6px;border-radius:4px">H</span> Hadir &nbsp;
            <span style="background:#fef9c3;padding:2px 6px;border-radius:4px">I</span> Ijin &nbsp;
            <span style="background:#dbeafe;padding:2px 6px;border-radius:4px">S</span> Sakit &nbsp;
            <span style="background:#fee2e2;padding:2px 6px;border-radius:4px">T</span> Tanpa Keterangan
        </div>
    </div>
</div>
@endsection