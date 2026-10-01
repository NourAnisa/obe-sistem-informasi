@extends('layouts.dashboard')
@section('title', 'Evaluasi BAP — ' . $mk->kode)

@section('content')
<div style="padding:24px">

    {{-- Header --}}
    <div style="margin-bottom:20px">
        <div style="font-size:12px;color:#94a3b8;margin-bottom:8px">
            <a href="{{ route('bap-evaluasi.index') }}" style="color:#3b82f6;text-decoration:none">Evaluasi</a>
            <span> › </span><span>{{ $mk->kode }}</span>
        </div>
        <h1 style="font-size:20px;font-weight:700;color:#1e3a5f;margin:0">📝 Evaluasi Perkuliahan</h1>
        <p style="color:#64748b;margin:4px 0 0;font-size:13px">{{ $mk->nama }} · {{ $enrollment->semester_aktif }}
            @if($enrollment->is_pjmk)<span style="background:#fef3c7;color:#92400e;padding:1px 8px;border-radius:10px;font-size:11px;margin-left:6px">★ Anda PJMK</span>@endif
        </p>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px">✅ {{ session('success') }}</div>
    @endif

    <div style="background:#eff6ff;border:1px solid #bfdbfe;padding:14px 16px;border-radius:10px;margin-bottom:20px;font-size:13px;color:#1e40af">
        💡 Isi evaluasi berdasarkan yang <strong>benar-benar Anda rasakan</strong> di setiap pertemuan. Data ini digunakan untuk perbandingan dengan rencana dosen (RPS/BAP).
    </div>

    <form method="POST" action="{{ route('bap-evaluasi.update', $mk->kode) }}">
        @csrf @method('PUT')

        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:900px">
                <thead>
                    <tr style="background:#1e3a5f;color:#fff">
                        <th style="padding:12px 10px;text-align:center;width:45px">Prtm</th>
                        <th style="padding:12px 10px;text-align:left;width:80px">Tanggal</th>
                        <th style="padding:12px 10px;text-align:left">Materi Dosen (BAP)</th>
                        <th style="padding:12px 10px;text-align:center;width:70px">Kehadiran</th>
                        <th style="padding:12px 10px;text-align:left">Materi yang Anda Rasakan</th>
                        <th style="padding:12px 10px;text-align:center;width:80px">Kesesuaian<br>Materi (1-5)</th>
                        <th style="padding:12px 10px;text-align:center;width:80px">Kesesuaian<br>Metode (1-5)</th>
                        <th style="padding:12px 10px;text-align:left;width:130px">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bap->bapPertemuans->sortBy('minggu') as $pt)
                    @php
                    $isUts = (int)$pt->minggu === 8;
                    $isUas = (int)$pt->minggu === 16;
                    $evalku = $pt->mahasiswaEvaluasis->first();
                    $rowBg = $isUts ? '#fffbeb' : ($isUas ? '#fdf2f8' : ($loop->even ? '#f9fafb' : '#fff'));
                    @endphp
                    <tr style="background:{{ $rowBg }};border-bottom:1px solid #e5e7eb">
                        <td style="padding:8px 10px;text-align:center;font-weight:700;color:#1e3a5f">
                            {{ $pt->minggu }}
                            @if($isUts)<br><span style="font-size:9px;color:#92400e">UTS</span>@endif
                            @if($isUas)<br><span style="font-size:9px;color:#9d174d">UAS</span>@endif
                        </td>
                        <td style="padding:8px 10px;font-size:11px;color:#64748b">
                            {{ $pt->tanggal ? $pt->tanggal->format('d/m/Y') : '—' }}
                        </td>
                        <td style="padding:8px 10px;font-size:11px;color:#374151;max-width:200px">
                            @if($isUts)<strong>UTS</strong><br>@elseif($isUas)<strong>UAS</strong><br>@endif
                            {{ Str::limit($pt->materi, 120) }}
                        </td>
                        <td style="padding:8px 10px;text-align:center">
                            <select name="pertemuan[{{ $pt->id }}][kehadiran]" style="font-size:11px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px">
                                @foreach(['hadir'=>'Hadir','ijin'=>'Ijin','sakit'=>'Sakit','tk'=>'TK'] as $val => $lbl)
                                <option value="{{ $val }}" {{ ($evalku?->kehadiran ?? 'hadir') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="padding:8px 10px">
                            <textarea name="pertemuan[{{ $pt->id }}][materi_dirasakan]" rows="2"
                                placeholder="Apa yang Anda pelajari di pertemuan ini..."
                                style="width:100%;font-size:11px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;resize:vertical">{{ $evalku?->materi_dirasakan }}</textarea>
                        </td>
                        <td style="padding:8px 10px;text-align:center">
                            <select name="pertemuan[{{ $pt->id }}][kesesuaian_materi]" style="font-size:11px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;width:55px">
                                @for($i=1;$i<=5;$i++)
                                    <option value="{{ $i }}" {{ ($evalku?->kesesuaian_materi ?? 3) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                            </select>
                        </td>
                        <td style="padding:8px 10px;text-align:center">
                            <select name="pertemuan[{{ $pt->id }}][kesesuaian_metode]" style="font-size:11px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;width:55px">
                                @for($i=1;$i<=5;$i++)
                                    <option value="{{ $i }}" {{ ($evalku?->kesesuaian_metode ?? 3) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                            </select>
                        </td>
                        <td style="padding:8px 10px">
                            <input type="text" name="pertemuan[{{ $pt->id }}][catatan]"
                                value="{{ $evalku?->catatan }}"
                                placeholder="Catatan..."
                                style="width:100%;font-size:11px;padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;box-sizing:border-box">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;text-align:right">
            <a href="{{ route('bap-evaluasi.index') }}" style="padding:10px 20px;border:1px solid #d1d5db;border-radius:8px;color:#374151;text-decoration:none;margin-right:12px;font-size:13px">← Kembali</a>
            <button type="submit" style="background:#1d4ed8;color:#fff;padding:10px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px">💾 Simpan Evaluasi</button>
        </div>
    </form>
</div>
@endsection