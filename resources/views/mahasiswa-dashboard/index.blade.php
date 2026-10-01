@extends('layouts.dashboard')
@section('title', 'Dashboard Mahasiswa')

@section('content')
<div style="padding:24px;max-width:1200px">

    {{-- Header --}}
    <div style="margin-bottom:28px">
        <h1 style="font-size:24px;font-weight:700;color:#1e3a5f;margin:0">
            🎓 Dashboard Mahasiswa
        </h1>
        @if($mahasiswa)
        <p style="color:#64748b;margin:4px 0 0;font-size:14px">
            Selamat datang, <strong>{{ $mahasiswa->nama }}</strong> — NIM: {{ $mahasiswa->nim }}
            @if($mahasiswa->angkatan) | Angkatan {{ $mahasiswa->angkatan }} @endif
        </p>
        @endif
    </div>

    @if(!$mahasiswa)
    <div style="background:#fef9c3;border:1px solid #fde047;padding:32px;border-radius:12px;text-align:center;color:#713f12">
        <div style="font-size:40px;margin-bottom:12px">⚠️</div>
        <p style="font-size:16px;font-weight:600;margin:0">Data mahasiswa belum tersedia</p>
        <p style="margin:8px 0 0;font-size:13px">Hubungi Kaprodi/Admin untuk melengkapi data Anda.</p>
    </div>
    @else

    {{-- Stats Cards --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px">
        <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 6px rgba(0,0,0,.07);border-left:4px solid #3b82f6">
            <div style="font-size:28px;font-weight:800;color:#1e40af">{{ $enrollments->count() }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px">Mata Kuliah Diikuti</div>
        </div>
        <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 6px rgba(0,0,0,.07);border-left:4px solid #8b5cf6">
            <div style="font-size:28px;font-weight:800;color:#6d28d9">{{ $cplList->count() }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px">CPL Dibebankan</div>
        </div>
        <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 6px rgba(0,0,0,.07);border-left:4px solid #10b981">
            @php
            $pct = $skkmTarget > 0 ? min(100, round($skkmApproved / $skkmTarget * 100)) : 0;
            $pctPending = $skkmTarget > 0 ? min(100 - $pct, round($skkmPending / $skkmTarget * 100)) : 0;
            @endphp
            <div style="font-size:28px;font-weight:800;color:#065f46">{{ number_format($skkmApproved) }} <span style="font-size:14px;font-weight:400;color:#94a3b8">/ {{ $skkmTarget }} poin</span></div>
            <div style="font-size:13px;color:#64748b;margin-top:2px">SKKM Disetujui (target lulus)</div>
            <div style="margin-top:8px;background:#f1f5f9;border-radius:8px;height:10px;overflow:hidden">
                <div style="display:flex;height:100%">
                    <div style="width:{{ $pct }}%;background:#10b981;transition:width .4s"></div>
                    <div style="width:{{ $pctPending }}%;background:#fcd34d;transition:width .4s"></div>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:6px;font-size:11px;color:#64748b">
                <span>🟢 Disetujui: <strong>{{ number_format($skkmApproved) }}</strong></span>
                <span>🟡 Pending: <strong>{{ number_format($skkmPending) }}</strong></span>
                <span style="margin-left:auto;color:{{ $pct >= 100 ? '#065f46' : '#9333ea' }};font-weight:600">{{ $pct }}%</span>
            </div>
        </div>
        <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 6px rgba(0,0,0,.07);border-left:4px solid #f59e0b">
            <div style="font-size:28px;font-weight:800;color:#92400e">{{ $evalCount }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px">Evaluasi BAP Diisi</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        {{-- ── SKS KELULUSAN (full-width card) ── --}}
        @php
            $sksBarColor = $sksProgress >= 100 ? '#16a34a'
                : ($sksProgress >= 70 ? '#2563eb'
                : ($sksProgress >= 40 ? '#d97706' : '#dc2626'));
            $sksBg = $sksProgress >= 100 ? '#f0fdf4'
                : ($sksProgress >= 70 ? '#eff6ff'
                : ($sksProgress >= 40 ? '#fffbeb' : '#fef2f2'));
            $sksBorder = $sksProgress >= 100 ? '#bbf7d0'
                : ($sksProgress >= 70 ? '#bfdbfe'
                : ($sksProgress >= 40 ? '#fde68a' : '#fecaca'));
        @endphp
        <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 6px rgba(0,0,0,.07);grid-column:1/-1">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                <div>
                    <h2 style="font-size:16px;font-weight:700;color:#1e3a5f;margin:0">🎓 Progress SKS Kelulusan</h2>
                    <p style="font-size:12px;color:#64748b;margin:4px 0 0">Berdasarkan total kurikulum program studi</p>
                </div>
                @if($sksProgress >= 100)
                <span style="background:#dcfce7;color:#065f46;padding:6px 16px;border-radius:20px;font-size:13px;font-weight:700">✅ Memenuhi Target SKS!</span>
                @else
                <span style="background:{{ $sksBg }};color:{{ $sksBarColor }};border:1px solid {{ $sksBorder }};padding:6px 16px;border-radius:20px;font-size:13px;font-weight:700">
                    {{ number_format($sksProgress, 1) }}% tercapai
                </span>
                @endif
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">
                {{-- Left: progress bar + stats --}}
                <div>
                    <div style="background:{{ $sksBg }};border:1px solid {{ $sksBorder }};border-radius:12px;padding:16px;margin-bottom:14px">
                        <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:10px">
                            <span style="font-size:13px;font-weight:600;color:#374151">Target: {{ $targetSks }} SKS</span>
                            <span style="font-size:22px;font-weight:800;color:{{ $sksBarColor }}">{{ $sksLulus }} <span style="font-size:13px;font-weight:400;color:#94a3b8">SKS</span></span>
                        </div>
                        <div style="background:#e2e8f0;border-radius:999px;height:14px;overflow:hidden;margin-bottom:8px">
                            <div style="height:100%;border-radius:999px;background:{{ $sksBarColor }};width:{{ min(100, $sksProgress) }}%;transition:width .5s"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b">
                            <span>✅ Lulus: <strong style="color:#065f46">{{ $sksLulus }} SKS</strong></span>
                            <span>⏳ Sisa: <strong style="color:#dc2626">{{ $sksSisa }} SKS</strong></span>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                        <div style="background:#f8fafc;border-radius:10px;padding:12px;text-align:center">
                            <div style="font-size:20px;font-weight:800;color:#1d4ed8">{{ $targetSks }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:2px">Total Kurikulum</div>
                        </div>
                        <div style="background:#f0fdf4;border-radius:10px;padding:12px;text-align:center">
                            <div style="font-size:20px;font-weight:800;color:#16a34a">{{ $sksLulus }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:2px">SKS Lulus</div>
                        </div>
                        <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center">
                            <div style="font-size:20px;font-weight:800;color:#dc2626">{{ $sksSisa }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:2px">SKS Tersisa</div>
                        </div>
                    </div>
                    @if($semesterSisa > 0)
                    <div style="margin-top:12px;padding:10px 14px;background:#f0f9ff;border-radius:8px;font-size:13px;color:#0369a1;display:flex;align-items:center;gap:8px">
                        🗓️ Estimasi semester tersisa: <strong>{{ $semesterSisa }} semester</strong>
                        <span style="color:#94a3b8;font-size:11px">(asumsi 20 SKS/semester)</span>
                    </div>
                    @else
                    <div style="margin-top:12px;padding:10px 14px;background:#f0fdf4;border-radius:8px;font-size:13px;color:#065f46;font-weight:600">
                        🎉 Selamat! Anda telah memenuhi target SKS kelulusan.
                    </div>
                    @endif
                </div>

                {{-- Right: mini table per semester MK --}}
                <div>
                    <p style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin:0 0 10px">SKS Lulus per Semester MK</p>
                    @if($sksPerSem->isNotEmpty())
                    @php $runningTotal = 0; @endphp
                    <table style="width:100%;border-collapse:collapse;font-size:12px">
                        <thead>
                            <tr style="background:#f1f5f9">
                                <th style="padding:7px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:1px solid #e5e7eb">Semester MK</th>
                                <th style="padding:7px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:1px solid #e5e7eb">SKS Lulus</th>
                                <th style="padding:7px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:1px solid #e5e7eb">Kumulatif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sksPerSem as $row)
                            @php $runningTotal += $row->sks_lulus; @endphp
                            <tr style="border-bottom:1px solid #f1f5f9">
                                <td style="padding:7px 10px;text-align:center;font-weight:700;color:#1e3a5f">Sem {{ $row->semester }}</td>
                                <td style="padding:7px 10px;text-align:center;color:#16a34a;font-weight:600">{{ $row->sks_lulus }}</td>
                                <td style="padding:7px 10px;text-align:center;color:#374151">{{ $runningTotal }}</td>
                            </tr>
                            @endforeach
                            <tr style="background:#f8fafc;font-weight:700">
                                <td style="padding:7px 10px;text-align:center;color:#374151">Total</td>
                                <td style="padding:7px 10px;text-align:center;color:#16a34a">{{ $sksLulus }}</td>
                                <td style="padding:7px 10px;text-align:center;color:#1d4ed8">{{ $sksLulus }} / {{ $targetSks }}</td>
                            </tr>
                        </tbody>
                    </table>
                    @else
                    <div style="text-align:center;padding:24px;color:#94a3b8;font-size:13px;background:#f8fafc;border-radius:10px">
                        Belum ada mata kuliah dengan status lulus.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- MK yang Diikuti --}}
        <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 6px rgba(0,0,0,.07)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <h2 style="font-size:16px;font-weight:700;color:#1e3a5f;margin:0">📚 Mata Kuliah Saya</h2>
                <a href="{{ route('bap-evaluasi.index') }}" style="font-size:12px;color:#3b82f6;text-decoration:none">Lihat Semua →</a>
            </div>
            @forelse($enrollments->take(5) as $enr)
            @php $mk = $enr->mataKuliah; @endphp
            <div style="padding:12px 0;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-weight:600;font-size:13px;color:#1e3a5f">{{ $mk?->kode }} — {{ $mk?->nama }}</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px">
                        Sem {{ $enr->semester_aktif }} | {{ $mk?->sks }} SKS
                        @if($enr->is_pjmk)<span style="color:#d97706;font-weight:600"> ★ PJMK</span>@endif
                    </div>
                </div>
                <a href="{{ route('bap-evaluasi.show', $mk?->kode) }}"
                    style="font-size:11px;padding:5px 12px;background:#eff6ff;color:#2563eb;border-radius:8px;text-decoration:none;font-weight:600">
                    Evaluasi
                </a>
            </div>
            @empty
            <p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px 0">Belum terdaftar di mata kuliah apapun.</p>
            @endforelse
        </div>

        {{-- CPL --}}
        <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 6px rgba(0,0,0,.07)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
                <h2 style="font-size:16px;font-weight:700;color:#1e3a5f;margin:0">🎯 Capaian Pembelajaran (CPL)</h2>
                <a href="{{ route('mahasiswa.cpl') }}" style="font-size:12px;color:#3b82f6;text-decoration:none">Detail →</a>
            </div>
            @forelse($cplList->take(6) as $cpl)
            <div style="padding:10px 0;border-bottom:1px solid #f1f5f9">
                <div style="display:flex;gap:10px;align-items:flex-start">
                    <span style="background:#ede9fe;color:#7c3aed;padding:3px 9px;border-radius:8px;font-size:11px;font-weight:700;flex-shrink:0">{{ $cpl->kode }}</span>
                    <p style="margin:0;font-size:12px;color:#374151;line-height:1.5">{{ Str::limit($cpl->deskripsi, 80) }}</p>
                </div>
            </div>
            @empty
            <p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px 0">Tidak ada CPL yang dibebankan.</p>
            @endforelse
        </div>

        {{-- SKKM Preview --}}
        <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 6px rgba(0,0,0,.07)">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h2 style="font-size:16px;font-weight:700;color:#1e3a5f;margin:0">🏆 SKKM Saya</h2>
                <a href="{{ route('mahasiswa.skkm') }}" style="font-size:12px;color:#3b82f6;text-decoration:none">Kelola →</a>
            </div>

            {{-- Progress Bar Target 700 --}}
            @php
            $pct = $skkmTarget > 0 ? min(100, round($skkmApproved / $skkmTarget * 100)) : 0;
            $pctPending = $skkmTarget > 0 ? min(100 - $pct, round($skkmPending / $skkmTarget * 100)) : 0;
            $sisaTarget = max(0, $skkmTarget - $skkmApproved);
            @endphp
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-bottom:14px">
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:8px">
                    <span style="font-size:13px;font-weight:600;color:#065f46">Target Kelulusan: {{ $skkmTarget }} Poin</span>
                    <span style="font-size:18px;font-weight:800;color:{{ $pct >= 100 ? '#065f46' : '#1e3a5f' }}">
                        {{ number_format($skkmApproved) }} poin
                    </span>
                </div>
                <div style="background:#e2e8f0;border-radius:999px;height:12px;overflow:hidden">
                    <div style="display:flex;height:100%">
                        <div style="width:{{ $pct }}%;background:linear-gradient(90deg,#10b981,#059669);border-radius:999px 0 0 999px;transition:width .4s"></div>
                        <div style="width:{{ $pctPending }}%;background:#fcd34d"></div>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:11px;color:#64748b">
                    <div style="display:flex;gap:10px">
                        <span>🟢 Disetujui: <strong style="color:#065f46">{{ number_format($skkmApproved) }}</strong></span>
                        <span>🟡 Pending: <strong style="color:#92400e">{{ number_format($skkmPending) }}</strong></span>
                    </div>
                    @if($pct >= 100)
                    <span style="color:#065f46;font-weight:700">✅ Target Tercapai!</span>
                    @else
                    <span style="color:#6b7280">Kurang <strong style="color:#dc2626">{{ number_format($sisaTarget) }}</strong> poin lagi</span>
                    @endif
                </div>
            </div>

            {{-- Recent SKKM list --}}
            @forelse($skkmList->take(3) as $sk)
            <div style="padding:8px 0;border-bottom:1px solid #f1f5f9">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <div style="font-weight:600;font-size:13px;color:#1e3a5f">{{ $sk->nama_kegiatan }}</div>
                        <div style="font-size:11px;color:#94a3b8">{{ $sk->kategori }} • {{ $sk->tingkat }}</div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:700;color:#065f46;font-size:13px">{{ $sk->points_awarded }} poin</div>
                        <span style="font-size:10px;padding:2px 8px;border-radius:8px;
                            {{ $sk->status==='disetujui' ? 'background:#dcfce7;color:#166534' : ($sk->status==='ditolak' ? 'background:#fee2e2;color:#991b1b' : 'background:#fef9c3;color:#713f12') }}">
                            {{ ucfirst($sk->status) }}
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:16px 0;color:#94a3b8">
                <p style="font-size:13px;margin:0">Belum ada data SKKM.</p>
                <a href="{{ route('mahasiswa.skkm') }}" style="font-size:12px;color:#3b82f6;margin-top:6px;display:inline-block">+ Tambah Kegiatan</a>
            </div>
            @endforelse
            @if($skkmList->count() > 3)
            <div style="text-align:center;margin-top:8px">
                <a href="{{ route('mahasiswa.skkm') }}" style="font-size:12px;color:#3b82f6;text-decoration:none">Lihat semua {{ $skkmList->count() }} kegiatan →</a>
            </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 1px 6px rgba(0,0,0,.07)">
            <h2 style="font-size:16px;font-weight:700;color:#1e3a5f;margin:0 0 16px">⚡ Aksi Cepat</h2>
            <div style="display:grid;gap:10px">
                <a href="{{ route('mahasiswa.krs') }}"
                    style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#f0f9ff;border-radius:10px;text-decoration:none">
                    <span style="font-size:24px">📚</span>
                    <div>
                        <div style="font-weight:600;font-size:14px;color:#0369a1">Ambil Mata Kuliah (KRS)</div>
                        <div style="font-size:12px;color:#0284c7">Daftarkan MK sesuai semester aktif</div>
                    </div>
                </a>
                <a href="{{ route('bap-evaluasi.index') }}"
                    style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#eff6ff;border-radius:10px;text-decoration:none">
                    <span style="font-size:24px">📝</span>
                    <div>
                        <div style="font-weight:600;font-size:14px;color:#1e40af">Evaluasi BAP Perkuliahan</div>
                        <div style="font-size:12px;color:#3b82f6">Isi penilaian kesesuaian materi & metode</div>
                    </div>
                </a>
                <a href="{{ route('mahasiswa.skkm') }}"
                    style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#f0fdf4;border-radius:10px;text-decoration:none">
                    <span style="font-size:24px">🏆</span>
                    <div>
                        <div style="font-weight:600;font-size:14px;color:#065f46">Input SKKM</div>
                        <div style="font-size:12px;color:#16a34a">Tambah kegiatan & kredit mahasiswa</div>
                    </div>
                </a>
                <a href="{{ route('mahasiswa.cpl') }}"
                    style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#fdf4ff;border-radius:10px;text-decoration:none">
                    <span style="font-size:24px">🎯</span>
                    <div>
                        <div style="font-weight:600;font-size:14px;color:#6d28d9">Evaluasi CPL Saya</div>
                        <div style="font-size:12px;color:#7c3aed">Lihat CPL dari MK yang diikuti</div>
                    </div>
                </a>
            </div>
        </div>

    </div>
    @endif
</div>
@endsection