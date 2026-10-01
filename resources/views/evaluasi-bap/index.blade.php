@extends('layouts.dashboard')
@section('title', 'Evaluasi Berita Acara Perkuliahan')

@section('content')
<div style="padding:24px">

    {{-- Header --}}
    <div style="margin-bottom:24px">
        <h1 style="font-size:22px;font-weight:700;color:#1e3a5f;margin:0">📊 Evaluasi Berita Acara Perkuliahan</h1>
        <p style="color:#64748b;margin:6px 0 0;font-size:13px">
            Perbandingan antara Rencana Pembelajaran Semester (RPS), pelaksanaan BAP oleh dosen, dan evaluasi mahasiswa.
        </p>
    </div>

    {{-- Legend --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
        <span style="background:#dcfce7;color:#166534;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">✅ Sesuai</span>
        <span style="background:#fee2e2;color:#991b1b;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">❌ Tidak Sesuai</span>
        <span style="background:#fef9c3;color:#713f12;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">⏳ Belum Dievaluasi Mhs</span>
        <span style="background:#e0f2fe;color:#075985;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">📋 Belum Ada BAP</span>
        <span style="background:#f1f5f9;color:#64748b;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">— Belum Ada RPS</span>
    </div>

    {{-- MK Grid --}}
    <div style="display:grid;gap:14px">
        @foreach($mataKuliahs as $mk)
        @php
        $s = $summaries[$mk->id];
        $hasRps = $s['rps_count'] > 0;
        $hasBap = $s['bap_count'] > 0;
        $hasEval = $s['eval_count'] > 0;
        $avg = $s['avg_kesesuaian'];
        $statusBg = !$hasRps ? '#f1f5f9' : (!$hasBap ? '#e0f2fe' : (!$hasEval ? '#fef9c3' : ($avg >= 3.5 ? '#dcfce7' : '#fee2e2')));
        $statusText = !$hasRps ? '— Belum Ada RPS' : (!$hasBap ? '📋 Belum Ada BAP' : (!$hasEval ? '⏳ Belum Ada Evaluasi Mhs' : ($avg >= 3.5 ? '✅ Sesuai' : '❌ Perlu Perhatian')));
        $statusColor = !$hasRps ? '#64748b' : (!$hasBap ? '#075985' : (!$hasEval ? '#713f12' : ($avg >= 3.5 ? '#166534' : '#991b1b')));
        @endphp
        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                    <span style="font-weight:700;color:#1e3a5f;font-size:15px">{{ $mk->kode }}</span>
                    <span style="background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:8px;font-size:11px">Smt {{ $mk->semester }}</span>
                    <span style="background:{{ $statusBg }};color:{{ $statusColor }};padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600">{{ $statusText }}</span>
                </div>
                <p style="margin:0;color:#374151;font-size:13px">{{ $mk->nama }}</p>
                <div style="display:flex;gap:20px;margin-top:8px">
                    <span style="font-size:12px;color:#64748b">📄 RPS: <strong>{{ $s['rps_count'] }}/16</strong> pertemuan</span>
                    <span style="font-size:12px;color:#64748b">📋 BAP: <strong>{{ $s['bap_count'] }}/16</strong> pertemuan</span>
                    <span style="font-size:12px;color:#64748b">👨‍🎓 Evaluasi: <strong>{{ $s['eval_count'] }}</strong> respon</span>
                    @if($avg)
                    <span style="font-size:12px;color:#64748b">⭐ Rating: <strong>{{ number_format($avg,1) }}/5</strong></span>
                    @endif
                </div>
            </div>
            @if($hasRps)
            <a href="{{ route('evaluasi-bap.show', $mk->kode) }}"
                style="background:#1d4ed8;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;white-space:nowrap">
                🔍 Lihat Detail
            </a>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection