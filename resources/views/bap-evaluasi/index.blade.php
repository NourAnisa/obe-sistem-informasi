@extends('layouts.dashboard')
@section('title', 'Evaluasi BAP Mahasiswa')

@section('content')
<div style="padding:24px">
    <div style="margin-bottom:24px">
        <h1 style="font-size:22px;font-weight:700;color:#1e3a5f;margin:0">📋 Evaluasi Perkuliahan</h1>
        <p style="color:#64748b;margin:4px 0 0;font-size:13px">Halo, <strong>{{ $mahasiswa->nama }}</strong> — pilih mata kuliah untuk mengisi evaluasi</p>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px">✅ {{ session('success') }}</div>
    @endif

    @if($enrollments->isEmpty())
    <div style="background:#fef9c3;border:1px solid #fde047;padding:24px;border-radius:12px;text-align:center;color:#713f12">
        <p style="font-size:16px;margin:0">Anda belum terdaftar di mata kuliah apapun.</p>
        <p style="margin:8px 0 0;font-size:13px">Hubungi Kaprodi / Admin untuk pendaftaran.</p>
    </div>
    @else
    <div style="display:grid;gap:14px">
        @foreach($enrollments as $enr)
        @php $mk = $enr->mataKuliah; @endphp
        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.08);padding:20px;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                    <span style="font-weight:700;color:#1e3a5f;font-size:15px">{{ $mk?->kode }}</span>
                    @if($enr->is_pjmk)
                    <span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:600">★ PJMK</span>
                    @endif
                </div>
                <p style="margin:0;color:#374151;font-size:14px">{{ $mk?->nama }}</p>
                <p style="margin:4px 0 0;color:#94a3b8;font-size:12px">{{ $enr->semester_aktif }} · {{ $mk?->sks ?? '—' }} SKS</p>
            </div>
            <a href="{{ route('bap-evaluasi.show', $mk->kode) }}"
                style="background:#1d4ed8;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600">
                ✏️ Isi Evaluasi
            </a>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection