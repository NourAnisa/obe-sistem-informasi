@extends('layouts.dashboard')
@section('title', 'Ringkasan Penilaian Dosen')

@section('content')
<div style="padding:24px 16px">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
            <h1 style="font-size:20px;font-weight:800;color:#1e40af;margin:0">📊 Ringkasan Penilaian Dosen</h1>
            <p style="font-size:12px;color:#6b7280;margin:4px 0 0">{{ $mk->kode }} — {{ $mk->nama }}</p>
        </div>
        <a href="{{ route('bap.show', $mk->kode) }}"
            style="font-size:12px;background:#e5e7eb;color:#374151;padding:6px 16px;border-radius:8px;text-decoration:none;font-weight:600">
            ← Kembali ke BAP
        </a>
    </div>

    {{-- Summary Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:24px">
        @php
        $allScores = collect($summaries)->filter(fn($s) => $s['count'] > 0);
        $totalResp = $allScores->sum('count');
        $avgPed = $allScores->count() ? round($allScores->avg('pedagogik'), 2) : 0;
        $avgPro = $allScores->count() ? round($allScores->avg('profesional'), 2) : 0;
        $avgKep = $allScores->count() ? round($allScores->avg('kepribadian'), 2) : 0;
        $avgSos = $allScores->count() ? round($allScores->avg('sosial'), 2) : 0;
        $avgAll = $allScores->count() ? round($allScores->avg('rata_rata'), 2) : 0;
        @endphp
        <div style="background:#fff;border-radius:12px;border:1.5px solid #e5e7eb;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:800;color:#7c3aed">{{ $totalResp }}</div>
            <div style="font-size:11px;color:#6b7280;font-weight:600">Total Responden</div>
        </div>
        <div style="background:#eff6ff;border-radius:12px;border:1.5px solid #bfdbfe;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:800;color:#1e40af">{{ $avgPed }}</div>
            <div style="font-size:11px;color:#1e40af;font-weight:600">Avg Pedagogik</div>
        </div>
        <div style="background:#f0fdf4;border-radius:12px;border:1.5px solid #bbf7d0;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:800;color:#065f46">{{ $avgPro }}</div>
            <div style="font-size:11px;color:#065f46;font-weight:600">Avg Profesional</div>
        </div>
        <div style="background:#fffbeb;border-radius:12px;border:1.5px solid #fde68a;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:800;color:#92400e">{{ $avgKep }}</div>
            <div style="font-size:11px;color:#92400e;font-weight:600">Avg Kepribadian</div>
        </div>
        <div style="background:#fdf2f8;border-radius:12px;border:1.5px solid #fbcfe8;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:800;color:#9d174d">{{ $avgSos }}</div>
            <div style="font-size:11px;color:#9d174d;font-weight:600">Avg Sosial</div>
        </div>
        <div style="background:#f5f3ff;border-radius:12px;border:1.5px solid #c4b5fd;padding:16px;text-align:center">
            <div style="font-size:24px;font-weight:800;color:#5b21b6">{{ $avgAll }}</div>
            <div style="font-size:11px;color:#5b21b6;font-weight:600">Rata-rata Umum</div>
        </div>
    </div>

    {{-- Detail Table per Pertemuan --}}
    <div style="background:#fff;border-radius:12px;border:1.5px solid #e5e7eb;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb">
            <p style="font-size:13px;font-weight:700;color:#374151;margin:0">📋 Detail Per Pertemuan</p>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#f9fafb;font-size:11px;font-weight:600;color:#374151">
                        <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;width:60px">Prtm</th>
                        <th style="padding:10px 12px;text-align:left;border-bottom:1px solid #e5e7eb">Materi</th>
                        <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;width:80px">Responden</th>
                        <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;width:90px" style="color:#1e40af">Pedagogik</th>
                        <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;width:90px">Profesional</th>
                        <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;width:90px">Kepribadian</th>
                        <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;width:80px">Sosial</th>
                        <th style="padding:10px 12px;text-align:center;border-bottom:1px solid #e5e7eb;width:80px">Avg</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pertemuans as $pt)
                    @php $s = $summaries[$pt->id] ?? ['count'=>0,'hadir'=>0,'pedagogik'=>'-','profesional'=>'-','kepribadian'=>'-','sosial'=>'-','rata_rata'=>'-']; @endphp
                    <tr style="border-bottom:1px solid #f3f4f6">
                        <td style="padding:10px 12px;text-align:center;font-weight:700;color:#7c3aed">{{ $pt->minggu }}</td>
                        <td style="padding:10px 12px;color:#374151;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $pt->materi ?? '—' }}</td>
                        <td style="padding:10px 12px;text-align:center">
                            @if($s['count'] > 0)
                            <span style="background:#f5f3ff;color:#5b21b6;padding:2px 8px;border-radius:99px;font-weight:600">{{ $s['count'] }}</span>
                            @else
                            <span style="color:#9ca3af;font-size:10px">—</span>
                            @endif
                        </td>
                        @foreach(['pedagogik'=>'#1e40af','profesional'=>'#065f46','kepribadian'=>'#92400e','sosial'=>'#9d174d'] as $key => $color)
                        <td style="padding:10px 12px;text-align:center">
                            @if($s['count'] > 0)
                            @php $score = is_numeric($s[$key]) ? (float)$s[$key] : 0; @endphp
                            <span style="color:{{ $color }};font-weight:600">{{ number_format($score,2) }}</span>
                            <div style="height:4px;background:#e5e7eb;border-radius:2px;margin-top:3px">
                                <div style="height:4px;background:{{ $color }};border-radius:2px;width:{{ min(100,round($score/3*100)) }}%"></div>
                            </div>
                            @else
                            <span style="color:#d1d5db;font-size:10px">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td style="padding:10px 12px;text-align:center">
                            @if($s['count'] > 0)
                            @php $avg = is_numeric($s['rata_rata']) ? (float)$s['rata_rata'] : 0; @endphp
                            <span style="background:{{ $avg >= 2.5 ? '#dcfce7' : ($avg >= 1.8 ? '#fef3c7' : '#fee2e2') }};
                                color:{{ $avg >= 2.5 ? '#166534' : ($avg >= 1.8 ? '#92400e' : '#991b1b') }};
                                padding:2px 8px;border-radius:99px;font-weight:700">
                                {{ number_format($avg, 2) }}
                            </span>
                            @else
                            <span style="color:#9ca3af;font-size:10px">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f9fafb;font-weight:700;border-top:2px solid #e5e7eb">
                        <td colspan="2" style="padding:10px 12px;font-size:12px;color:#374151">Rata-rata Keseluruhan</td>
                        <td style="padding:10px 12px;text-align:center;color:#7c3aed">{{ $totalResp }}</td>
                        <td style="padding:10px 12px;text-align:center;color:#1e40af">{{ $avgPed }}</td>
                        <td style="padding:10px 12px;text-align:center;color:#065f46">{{ $avgPro }}</td>
                        <td style="padding:10px 12px;text-align:center;color:#92400e">{{ $avgKep }}</td>
                        <td style="padding:10px 12px;text-align:center;color:#9d174d">{{ $avgSos }}</td>
                        <td style="padding:10px 12px;text-align:center">
                            <span style="background:#f5f3ff;color:#5b21b6;padding:3px 10px;border-radius:99px;font-weight:800">{{ $avgAll }}</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div style="margin-top:16px;background:#f9fafb;border-radius:10px;padding:12px 16px;font-size:11px;color:#6b7280">
        <strong>Skala Penilaian:</strong> 1.00–1.79 = <span style="color:#991b1b;font-weight:600">Kurang</span> ·
        1.80–2.49 = <span style="color:#92400e;font-weight:600">Cukup</span> ·
        2.50–3.00 = <span style="color:#166534;font-weight:600">Baik</span>
    </div>
</div>
@endsection