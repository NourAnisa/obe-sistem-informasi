@extends('layouts.dashboard')
@section('title', 'Edit RPS – '.$mk->nama)

@push('styles')
<style>
    /* ── Field styles ──────────────────────────────────── */
    .f-input {
        width: 100%;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
        color: #111;
        transition: border-color .15s, box-shadow .15s;
        background: #fff;
    }

    .f-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
    }

    .f-textarea {
        resize: vertical;
        min-height: 64px;
    }

    /* ── Tab nav ───────────────────────────────────────── */
    .tab-btn {
        padding: 10px 20px;
        border-bottom: 3px solid transparent;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        white-space: nowrap;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
        transition: color .15s;
    }

    .tab-btn:hover {
        color: #3b82f6;
    }

    .tab-btn.active {
        color: #1d4ed8;
        border-bottom-color: #1d4ed8;
    }

    /* ── Pertemuan accordion ───────────────────────────── */
    .week-card {
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .week-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 16px;
        cursor: pointer;
        user-select: none;
        transition: background .15s;
    }

    .week-card-head:hover {
        filter: brightness(.97);
    }

    .week-card-body {
        display: none;
        padding: 16px;
        border-top: 1px solid rgba(0, 0, 0, .06);
    }

    .week-card-body.open {
        display: block;
    }

    /* ── Sidebar chips ─────────────────────────────────── */
    .chip-cpl {
        background: #f3e8ff;
        color: #6b21a8;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 4px;
        font-weight: 700;
        white-space: nowrap;
    }

    .chip-cpmk {
        background: #dbeafe;
        color: #1e3a8a;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 4px;
        font-weight: 700;
        white-space: nowrap;
    }

    .chip-sub {
        background: #dcfce7;
        color: #14532d;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 4px;
        font-weight: 700;
        white-space: nowrap;
    }

    .chip-bk {
        background: #fef9c3;
        color: #713f12;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 4px;
        font-weight: 700;
        white-space: nowrap;
    }

    /* ── Section card ──────────────────────────────────── */
    .sec-card {
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
    }

    .sec-title {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        padding-bottom: 8px;
        margin-bottom: 14px;
        border-bottom: 2px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .field-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #4b5563;
        margin-bottom: 5px;
    }

    .hint {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
    }
</style>
@endpush

@section('content')
@php
$dosenList = $detail->dosen_pengampu ?? [];
if (empty($dosenList)) $dosenList = [$mk->pjmk ?? ''];
$ketentuan = $detail->ketentuan_tambahan ?? [
'Kehadiran minimal 75% dari total pertemuan tatap muka.',
'Tugas dikumpulkan sesuai jadwal yang telah ditetapkan dosen.',
'Tidak diperkenankan melakukan kecurangan/plagiarisme (nilai 0).',
];
$jadwal = $detail->jadwal_kuliah ?? [];
$pertemuanMap = [];
foreach ($pertemuan as $pt) { $pertemuanMap[$pt->minggu] = $pt; }
$filledCount = collect($pertemuanMap)->filter(fn($p,$w) => !in_array($w,[8,16]) && ($p->indikator || $p->materi || $p->metode_sinkron))->count();

// Load publikasi for pertemuan picker
$allPublikasiForPicker = \App\Models\PublikasiDosen::with('dosen')
    ->whereIn('dosen_id', \App\Models\User::where('role','dosen')->pluck('id'))
    ->orderByDesc('tahun')->orderBy('judul')
    ->get()
    ->map(fn($p) => [
        'id'      => $p->id,
        'label'   => ($p->tahun ? "({$p->tahun}) " : '') . \Illuminate\Support\Str::limit($p->judul, 70),
        'citation'=> ($p->authors ? $p->authors . '. ' : '') . ($p->tahun ? "({$p->tahun}). " : '') . $p->judul . ($p->sumber ? '. ' . $p->sumber : ''),
        'tipe'    => $p->tipe,
    ])
    ->values();
@endphp

{{-- ═══════════════════ PAGE HEADER ═══════════════════ --}}
<div style="background:linear-gradient(135deg,#1d4ed8,#4f46e5);border-radius:14px;padding:20px 24px;margin-bottom:20px;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,.18)">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <a href="{{ route('rps.show', $mk->kode) }}"
                style="color:#bfdbfe;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:6px">
                ← Kembali ke Preview RPS
            </a>
            <h1 style="font-size:20px;font-weight:800;margin:0 0 4px">✏️ Edit RPS</h1>
            <p style="font-size:14px;color:#bfdbfe;margin:0">{{ $mk->nama }}</p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;font-size:12px;color:#bfdbfe">
                <span>📋 <strong style="color:#fff">{{ $mk->kode }}</strong></span>
                <span>📚 <strong style="color:#fff">{{ $mk->sks }} SKS</strong></span>
                <span>🎓 Semester <strong style="color:#fff">{{ $mk->semester }}</strong></span>
                <span>🏷️ <strong style="color:#fff">{{ $mk->kategori }}</strong></span>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ route('rps.show', $mk->kode) }}"
                style="background:rgba(255,255,255,.2);color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                👁️ Preview
            </a>
            <a href="{{ route('rps.kontrak', $mk->kode) }}"
                style="background:rgba(255,255,255,.15);color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                📑 Kontrak
            </a>
        </div>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1.5px solid #86efac;color:#166534;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:8px;font-size:13px">
    ✅ {{ session('success') }}
</div>
@endif

{{-- ═══════════════════ MAIN LAYOUT ═══════════════════ --}}
<div style="display:grid;grid-template-columns:240px 1fr;gap:20px;align-items:start">

    {{-- ══ SIDEBAR (OBE Reference) ══ --}}
    <div style="position:sticky;top:16px">

        <div style="background:#f8faff;border:1.5px solid #dbeafe;border-radius:12px;padding:14px;margin-bottom:12px">
            <div style="font-size:11px;font-weight:700;color:#1e3a8a;margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">
                📚 Referensi OBE
            </div>
            <div style="font-size:11px;color:#3b82f6;font-style:italic">Data otomatis dari modul CPL, CPMK, dan Bobot.</div>
        </div>

        {{-- CPL --}}
        <div class="sec-card" style="padding:12px">
            <div class="sec-title" style="margin-bottom:8px;padding-bottom:6px;font-size:12px">🎓 CPL</div>
            @forelse($mk->cpls->sortBy('kode') as $cpl)
            <div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:6px">
                <span class="chip-cpl">{{ $cpl->kode }}</span>
                <span style="font-size:11px;color:#6b7280;line-height:1.4">{{ Str::limit($cpl->deskripsi, 80) }}</span>
            </div>
            @empty<p style="font-size:11px;color:#9ca3af;font-style:italic">Belum ada CPL</p>@endforelse
        </div>

        {{-- CPMK --}}
        <div class="sec-card" style="padding:12px">
            <div class="sec-title" style="margin-bottom:8px;padding-bottom:6px;font-size:12px">🎯 CPMK</div>
            @forelse($mk->cpmks->sortBy('kode') as $cpmk)
            <div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:6px">
                <span class="chip-cpmk">{{ $cpmk->kode }}</span>
                <span style="font-size:11px;color:#6b7280;line-height:1.4">{{ Str::limit($cpmk->deskripsi, 80) }}</span>
            </div>
            @empty<p style="font-size:11px;color:#9ca3af;font-style:italic">Belum ada CPMK</p>@endforelse
        </div>

        {{-- Sub CPMK --}}
        <div class="sec-card" style="padding:12px">
            <div class="sec-title" style="margin-bottom:8px;padding-bottom:6px;font-size:12px">📌 Sub CPMK</div>
            @forelse($allSubCpmks as $sub)
            <div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:6px">
                <span class="chip-sub">{{ $sub['kode'] }}</span>
                <span style="font-size:11px;color:#6b7280;line-height:1.4">{{ Str::limit($sub['deskripsi'], 70) }}</span>
            </div>
            @empty<p style="font-size:11px;color:#9ca3af;font-style:italic">Belum ada Sub CPMK</p>@endforelse
        </div>

        {{-- Bobot --}}
        <div class="sec-card" style="padding:12px">
            <div class="sec-title" style="margin-bottom:8px;padding-bottom:6px;font-size:12px">⚖️ Bobot Penilaian</div>
            @forelse($mk->bobotPenilaians as $bp)
            <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:6px 8px;margin-bottom:6px">
                <div style="font-size:11px;font-weight:700;color:#374151;margin-bottom:4px">{{ $bp->cpmk?->kode ?? '–' }}</div>
                <div style="display:flex;flex-wrap:wrap;gap:3px">
                    @if($bp->bobot_tugas>0)<span style="background:#ffedd5;color:#9a3412;font-size:10px;padding:1px 5px;border-radius:3px">Tugas {{ $bp->bobot_tugas }}%</span>@endif
                    @if($bp->bobot_uts>0)<span style="background:#fee2e2;color:#991b1b;font-size:10px;padding:1px 5px;border-radius:3px">UTS {{ $bp->bobot_uts }}%</span>@endif
                    @if($bp->bobot_uas>0)<span style="background:#fee2e2;color:#991b1b;font-size:10px;padding:1px 5px;border-radius:3px">UAS {{ $bp->bobot_uas }}%</span>@endif
                    @if($bp->bobot_partisipatif>0)<span style="background:#ccfbf1;color:#134e4a;font-size:10px;padding:1px 5px;border-radius:3px">Partisipatif {{ $bp->bobot_partisipatif }}%</span>@endif
                    @if($bp->bobot_proyek>0)<span style="background:#e0e7ff;color:#3730a3;font-size:10px;padding:1px 5px;border-radius:3px">Proyek {{ $bp->bobot_proyek }}%</span>@endif
                </div>
            </div>
            @empty<p style="font-size:11px;color:#9ca3af;font-style:italic">Belum ada bobot</p>@endforelse
        </div>

    </div>

    {{-- ══ MAIN FORM AREA ══ --}}
    <div>
        {{-- Tab nav --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;margin-bottom:16px;overflow:hidden">
            <div style="display:flex;border-bottom:1.5px solid #e5e7eb;padding:0 16px;background:#f9fafb">
                <button class="tab-btn active" onclick="showTab('tab-umum',this)" type="button">📋 Info Umum</button>
                <button class="tab-btn" onclick="showTab('tab-rkbm',this)" type="button">
                    📅 Rencana Pertemuan
                    <span style="background:#1d4ed8;color:#fff;font-size:10px;padding:1px 6px;border-radius:10px;margin-left:4px">{{ $filledCount }}/14</span>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('rps.update', $mk->kode) }}" id="rpsForm">
            @csrf

            {{-- ═══════════════ TAB: Info Umum ═══════════════ --}}
            <div id="tab-umum">

                {{-- Deskripsi Singkat MK --}}
                <div class="sec-card">
                    <div class="sec-title">📄 Deskripsi Singkat Mata Kuliah</div>
                    <label class="field-label" for="mk_deskripsi">Deskripsi MK <span style="color:#6b7280;font-weight:400">(Indonesia)</span></label>
                    <textarea id="mk_deskripsi" name="mk_deskripsi" rows="4" class="f-input f-textarea"
                        placeholder="Tuliskan deskripsi singkat tentang mata kuliah ini: ruang lingkup, tujuan umum, dan manfaat bagi mahasiswa...">{{ old('mk_deskripsi', $mk->deskripsi) }}</textarea>
                    <div style="margin-top:6px">
                        <button type="button" onclick="translateField('mk_deskripsi','mk_deskripsi_en')"
                            style="padding:4px 12px;font-size:12px;background:#3b82f6;color:#fff;border:none;border-radius:6px;cursor:pointer">🌐 Translate to EN</button>
                    </div>
                    <label class="field-label" for="mk_deskripsi_en" style="margin-top:10px">Course Description <span style="color:#6b7280;font-weight:400">(English)</span></label>
                    <textarea id="mk_deskripsi_en" name="mk_deskripsi_en" rows="4" class="f-input f-textarea"
                        placeholder="Brief description of this course in English...">{{ old('mk_deskripsi_en', $mk->deskripsi_en) }}</textarea>
                    <p class="hint">Deskripsi ini akan tampil di bagian header RPS dan Kontrak Pembelajaran.</p>
                </div>

                {{-- Tautan Kelas Daring --}}
                <div class="sec-card">
                    <div class="sec-title">🔗 Tautan Kelas Daring (LMS)</div>
                    <label class="field-label" for="tautan">URL Kelas Daring</label>
                    <input type="url" id="tautan" name="tautan_kelas_daring"
                        class="f-input"
                        placeholder="https://lms.unism.ac.id/course/view.php?id=..."
                        value="{{ old('tautan_kelas_daring', $detail->tautan_kelas_daring) }}">
                    <p class="hint">Isi URL kelas di Google Classroom, Moodle, atau platform LMS lainnya.</p>
                </div>

                {{-- Dosen Pengampu --}}
                <div class="sec-card">
                    <div class="sec-title">👩‍🏫 Dosen Pengampu</div>
                    <div id="dosenList" style="display:flex;flex-direction:column;gap:8px">
                        @foreach($dosenList as $i => $d)
                        <div class="dosen-row" style="display:flex;align-items:center;gap:8px">
                            <span style="min-width:20px;font-size:13px;color:#9ca3af;font-weight:600">{{ $i+1 }}.</span>
                            <input type="text" name="dosen_pengampu[]"
                                class="f-input" style="flex:1"
                                placeholder="Nama Lengkap, Gelar (Inisial)"
                                value="{{ $d }}">
                            <button type="button" onclick="removeDosen(this)"
                                style="color:#ef4444;font-size:18px;background:none;border:none;cursor:pointer;padding:0 4px;line-height:1" title="Hapus">×</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addDosen()"
                        style="margin-top:10px;color:#2563eb;font-size:13px;font-weight:600;background:none;border:1.5px dashed #93c5fd;border-radius:8px;padding:7px 16px;width:100%;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                        + Tambah Dosen
                    </button>
                    <p class="hint">Format: Nama, Gelar (Inisial) — contoh: <strong>Ahmad Budiman, S.T., M.T. (AB)</strong></p>
                </div>

                {{-- Catatan Blueprint --}}
                <div class="sec-card">
                    <div class="sec-title">📝 Catatan Blueprint Asesmen</div>
                    <label class="field-label">Rumus / Catatan Penilaian</label>
                    <textarea name="catatan_blueprint" rows="3" class="f-input f-textarea"
                        placeholder="Contoh: NA = (Tugas×30%) + (UTS×30%) + (UAS×40%). Mahasiswa wajib hadir minimal 75%...">{{ old('catatan_blueprint', $detail->catatan_blueprint) }}</textarea>
                    <p class="hint">Ditampilkan dengan format biru-italic di atas tabel blueprint pada preview RPS.</p>
                </div>

                {{-- Blueprint Rows (manual edit deskripsi & metode) --}}
                @if(!empty($blueprintRows))
                <div class="sec-card">
                    <div class="sec-title">📊 Isian Blueprint Asesmen CPL–CPMK
                        <span style="font-size:11px;font-weight:400;color:#6b7280;margin-left:6px">— Edit Deskripsi % Indikator & Metode Penilaian per baris</span>
                    </div>
                    <p style="font-size:12px;color:#6b7280;margin-bottom:12px">Baris dihasilkan otomatis dari data CPMK & Bobot. Isi kolom <strong>Deskripsi % Indikator</strong> dan <strong>Metode Penilaian & Keterangan</strong> secara manual.</p>
                    <div style="overflow-x:auto">
                        <table style="width:100%;border-collapse:collapse;font-size:12px">
                            <thead>
                                <tr style="background:#f0fdf4">
                                    <th style="border:1.5px solid #d1fae5;padding:7px 10px;text-align:left;font-size:11px;color:#065f46;font-weight:700;width:150px">Basis Evaluasi</th>
                                    <th style="border:1.5px solid #d1fae5;padding:7px 10px;text-align:left;font-size:11px;color:#065f46;font-weight:700">Komponen</th>
                                    <th style="border:1.5px solid #d1fae5;padding:7px 10px;text-align:center;font-size:11px;color:#065f46;font-weight:700;width:80px">Sub CPMK</th>
                                    <th style="border:1.5px solid #d1fae5;padding:7px 10px;text-align:center;font-size:11px;color:#065f46;font-weight:700;width:55px">Bobot%</th>
                                    <th style="border:1.5px solid #d1fae5;padding:7px 10px;text-align:left;font-size:11px;color:#1d4ed8;font-weight:700">✏️ Deskripsi % Indikator</th>
                                    <th style="border:1.5px solid #d1fae5;padding:7px 10px;text-align:left;font-size:11px;color:#1d4ed8;font-weight:700">✏️ Metode Penilaian & Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $prevBasis = ''; @endphp
                                @foreach($blueprintRows as $row)
                                @php
                                $encodedKey = rawurlencode($row['override_key']);
                                $isSameBasis = $prevBasis === $row['basis'];
                                $prevBasis = $row['basis'];
                                @endphp
                                <tr style="{{ !$isSameBasis ? 'border-top:2px solid #a7f3d0;' : '' }}">
                                    <td style="border:1px solid #e5e7eb;padding:6px 8px;font-weight:{{ !$isSameBasis ? '700' : '400' }};color:#374151;vertical-align:middle;font-size:11px">
                                        {{ !$isSameBasis ? $row['basis'] : '' }}
                                    </td>
                                    <td style="border:1px solid #e5e7eb;padding:6px 8px;color:#374151;vertical-align:middle">{{ $row['komponen'] }}</td>
                                    <td style="border:1px solid #e5e7eb;padding:6px 8px;text-align:center;color:#6b7280;font-size:11px;vertical-align:middle">{{ $row['cpmk'] }}</td>
                                    <td style="border:1px solid #e5e7eb;padding:6px 8px;text-align:center;font-weight:700;color:#1d4ed8;vertical-align:middle">{{ $row['bobot'] }}%</td>
                                    <td style="border:1px solid #e5e7eb;padding:4px 6px;vertical-align:middle">
                                        <textarea name="blueprint_override[{{ $row['override_key'] }}][deskripsi]"
                                            rows="2" class="f-input f-textarea"
                                            style="font-size:12px;min-height:52px;resize:vertical"
                                            placeholder="Misal: Mahasiswa mampu menjelaskan konsep dengan benar ≥70%">{{ old('blueprint_override.'.$row['override_key'].'.deskripsi', $row['override_deskripsi']) }}</textarea>
                                    </td>
                                    <td style="border:1px solid #e5e7eb;padding:4px 6px;vertical-align:middle">
                                        <textarea name="blueprint_override[{{ $row['override_key'] }}][metode]"
                                            rows="2" class="f-input f-textarea"
                                            style="font-size:12px;min-height:52px;resize:vertical"
                                            placeholder="{{ $row['metode'] }}">{{ old('blueprint_override.'.$row['override_key'].'.metode', $row['override_metode']) }}</textarea>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Ketentuan Tambahan --}}
                <div class="sec-card">
                    <div class="sec-title">📋 Ketentuan Tambahan</div>
                    <p style="font-size:12px;color:#6b7280;margin-bottom:10px">Butir-butir ketentuan yang akan tampil di halaman Kontrak Pembelajaran.</p>
                    <div id="ketentuanList" style="display:flex;flex-direction:column;gap:8px">
                        @foreach($ketentuan as $i => $k)
                        <div class="ketentuan-row" style="display:flex;align-items:flex-start;gap:8px">
                            <span style="min-width:20px;font-size:13px;color:#9ca3af;font-weight:600;padding-top:8px">{{ $i+1 }}.</span>
                            <textarea name="ketentuan_tambahan[]" rows="2"
                                class="f-input f-textarea" style="flex:1">{{ $k }}</textarea>
                            <button type="button" onclick="removeKetentuan(this)"
                                style="color:#ef4444;font-size:18px;background:none;border:none;cursor:pointer;padding:0 4px;padding-top:4px;line-height:1" title="Hapus">×</button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addKetentuan()"
                        style="margin-top:10px;color:#2563eb;font-size:13px;font-weight:600;background:none;border:1.5px dashed #93c5fd;border-radius:8px;padding:7px 16px;width:100%;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                        + Tambah Ketentuan
                    </button>
                </div>

                {{-- Jadwal Kuliah --}}
                <div class="sec-card">
                    <div class="sec-title">📅 Jadwal Kuliah</div>

                    {{-- ══ Smart Generator Panel ══ --}}
                    <div style="background:linear-gradient(135deg,#eff6ff,#f0fdf4);border:1.5px solid #93c5fd;border-radius:10px;padding:14px;margin-bottom:14px">
                        <div style="font-size:12px;font-weight:700;color:#1e3a8a;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                            ✨ Generator Jadwal Otomatis
                            <span style="font-size:11px;font-weight:400;color:#3b82f6">— Pilih hari & tanggal mulai, libur nasional Indonesia otomatis dilewati</span>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px;margin-bottom:10px">
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#374151;margin-bottom:4px">📆 Hari Kuliah</label>
                                <select id="gen_hari" class="f-input" style="font-size:13px">
                                    <option value="1">Senin</option>
                                    <option value="2">Selasa</option>
                                    <option value="3" selected>Rabu</option>
                                    <option value="4">Kamis</option>
                                    <option value="5">Jumat</option>
                                    <option value="6">Sabtu</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#374151;margin-bottom:4px">📅 Tanggal Kuliah Pertama</label>
                                <input type="date" id="gen_mulai" class="f-input" style="font-size:13px"
                                    value="{{ date('Y').'-08-01' }}" min="2025-01-01" max="2027-12-31">
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#374151;margin-bottom:4px">⏰ Waktu (WITA)</label>
                                <input type="text" id="gen_waktu" class="f-input" style="font-size:13px"
                                    placeholder="08.00–09.40 WITA" value="08.00–09.40 WITA">
                            </div>
                            <div>
                                <label style="display:block;font-size:11px;font-weight:600;color:#374151;margin-bottom:4px">👩‍🏫 Dosen Default</label>
                                <input type="text" id="gen_dosen" class="f-input" style="font-size:13px"
                                    placeholder="Inisial Dosen" value="{{ isset($dosenList[0]) ? preg_replace('/.*\(([A-Z]+)\).*/', '$1', $dosenList[0]) : '' }}">
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                            <button type="button" onclick="generateJadwal()"
                                id="gen_btn"
                                style="background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:#fff;font-size:13px;font-weight:700;padding:9px 20px;border-radius:8px;cursor:pointer;border:none;box-shadow:0 2px 8px rgba(29,78,216,.3);display:flex;align-items:center;gap:6px">
                                ✨ Generate 16 Jadwal Otomatis
                            </button>
                            <span id="gen_status" style="font-size:12px;color:#6b7280"></span>
                        </div>
                        {{-- Holiday info panel (hidden until generated) --}}
                        <div id="gen_libur_info" style="display:none;margin-top:10px;padding:8px 12px;background:#fef9c3;border:1px solid #fde68a;border-radius:7px;font-size:11px;color:#92400e">
                            <strong>ℹ️ Hari libur yang dilewati:</strong>
                            <span id="gen_libur_list"></span>
                        </div>
                    </div>

                    <div style="overflow-x:auto">
                        <table style="width:100%;border-collapse:collapse;font-size:13px" id="jadwalTable">
                            <thead>
                                <tr style="background:#f3f4f6">
                                    <th style="border:1.5px solid #e5e7eb;padding:8px 12px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;width:36px">#</th>
                                    <th style="border:1.5px solid #e5e7eb;padding:8px 12px;text-align:left;font-size:12px;color:#6b7280;font-weight:700">Hari / Tanggal</th>
                                    <th style="border:1.5px solid #e5e7eb;padding:8px 12px;text-align:left;font-size:12px;color:#6b7280;font-weight:700">Waktu WITA</th>
                                    <th style="border:1.5px solid #e5e7eb;padding:8px 12px;text-align:left;font-size:12px;color:#6b7280;font-weight:700">Dosen Pengampu</th>
                                    <th style="border:1.5px solid #e5e7eb;padding:8px;text-align:center;font-size:12px;color:#6b7280;font-weight:700;width:40px">–</th>
                                </tr>
                            </thead>
                            <tbody id="jadwalBody">
                                @forelse($jadwal as $i => $j)
                                @php $isLibur = isset($j['info']) && $j['info'] === 'libur'; @endphp
                                <tr class="jadwal-row" style="{{ $isLibur ? 'background:#fef9c3;' : '' }}">
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px;text-align:center;font-size:11px;color:#9ca3af;font-weight:600">{{ $i+1 }}</td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                                        <input type="text" name="jadwal_kuliah[{{ $i }}][hari]"
                                            class="f-input" style="border:none;padding:4px 0;box-shadow:none"
                                            placeholder="Senin / 01 Jan 2025"
                                            value="{{ $j['hari'] ?? '' }}">
                                    </td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                                        <input type="text" name="jadwal_kuliah[{{ $i }}][waktu]"
                                            class="f-input" style="border:none;padding:4px 0;box-shadow:none;{{ $isLibur ? 'color:#92400e;font-style:italic;' : '' }}"
                                            placeholder="08.00–09.40 WITA"
                                            value="{{ $j['waktu'] ?? '' }}">
                                    </td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                                        <input type="text" name="jadwal_kuliah[{{ $i }}][dosen]"
                                            class="f-input" style="border:none;padding:4px 0;box-shadow:none"
                                            placeholder="Nama Dosen"
                                            value="{{ $j['dosen'] ?? '' }}">
                                    </td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px;text-align:center">
                                        <button type="button" onclick="removeRow(this)"
                                            style="color:#ef4444;background:none;border:none;font-size:18px;cursor:pointer;line-height:1">×</button>
                                    </td>
                                </tr>
                                @empty
                                <tr class="jadwal-row">
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px;text-align:center;font-size:11px;color:#9ca3af">1</td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                                        <input type="text" name="jadwal_kuliah[0][hari]" class="f-input" style="border:none;padding:4px 0;box-shadow:none" placeholder="Senin / 01 Jan 2025">
                                    </td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                                        <input type="text" name="jadwal_kuliah[0][waktu]" class="f-input" style="border:none;padding:4px 0;box-shadow:none" placeholder="08.00–09.40 WITA">
                                    </td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                                        <input type="text" name="jadwal_kuliah[0][dosen]" class="f-input" style="border:none;padding:4px 0;box-shadow:none" placeholder="Nama Dosen">
                                    </td>
                                    <td style="border:1.5px solid #e5e7eb;padding:4px;text-align:center">
                                        <button type="button" onclick="removeRow(this)" style="color:#ef4444;background:none;border:none;font-size:18px;cursor:pointer;line-height:1">×</button>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <button type="button" onclick="addJadwalRow()"
                        style="margin-top:10px;color:#2563eb;font-size:13px;font-weight:600;background:none;border:1.5px dashed #93c5fd;border-radius:8px;padding:7px 16px;width:100%;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                        + Tambah Baris Jadwal
                    </button>
                </div>

                {{-- Pustaka Terkonsolidasi (sinkron dari pertemuan) --}}
                <div class="sec-card">
                    <div class="sec-title">📚 Pustaka / Referensi Perkuliahan
                        <span style="font-size:11px;font-weight:400;color:#6b7280;margin-left:8px">Otomatis terkumpul dari semua field "Materi / Pustaka" di tab Rencana Pertemuan</span>
                    </div>
                    <div style="background:#f8faff;border:1.5px solid #dbeafe;border-radius:8px;padding:12px;margin-bottom:10px">
                        <p style="font-size:12px;color:#2563eb;margin:0">
                            💡 Isi kolom <strong>Materi / Pustaka</strong> di setiap pertemuan (tab "Rencana Pertemuan"), maka ringkasannya akan tampil otomatis di sini. Satu baris per pertemuan.
                        </p>
                    </div>
                    <div id="pustakaSyncPanel" style="display:flex;flex-direction:column;gap:6px">
                        {{-- populated by JS --}}
                        @for($w = 1; $w <= 16; $w++)
                            @php $p=$pertemuanMap[$w] ?? null; @endphp
                            @if(!in_array($w, [8,16]) && $p && trim($p->materi ?? ''))
                            <div style="display:flex;gap:10px;align-items:flex-start;padding:7px 10px;background:#fff;border:1.5px solid #e5e7eb;border-radius:8px" data-wk="{{ $w }}">
                                <span style="background:#1d4ed8;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:5px;flex-shrink:0;margin-top:1px">Mg {{ $w }}</span>
                                <span class="pustaka-text-{{ $w }}" style="font-size:12px;color:#374151;line-height:1.5">{{ $p->materi }}</span>
                            </div>
                            @elseif(!in_array($w,[8,16]))
                            <div style="display:none" id="pustaka-row-{{ $w }}" data-wk="{{ $w }}">
                                <div style="display:flex;gap:10px;align-items:flex-start;padding:7px 10px;background:#fff;border:1.5px solid #e5e7eb;border-radius:8px">
                                    <span style="background:#1d4ed8;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:5px;flex-shrink:0;margin-top:1px">Mg {{ $w }}</span>
                                    <span class="pustaka-text-{{ $w }}" style="font-size:12px;color:#374151;line-height:1.5"></span>
                                </div>
                            </div>
                            @endif
                            @endfor
                    </div>
                    <p id="pustaka-empty-msg" style="font-size:12px;color:#9ca3af;font-style:italic;text-align:center;padding:12px;{{ collect(range(1,16))->filter(fn($w)=>!in_array($w,[8,16]))->filter(fn($w)=>trim(($pertemuanMap[$w]??null)?->materi??''))->count() > 0 ? 'display:none' : '' }}">
                        Belum ada materi/pustaka yang diisi. Lengkapi di tab Rencana Pertemuan.
                    </p>
                </div>

            </div>{{-- /tab-umum --}}

            {{-- ═══ PUSTAKA UTAMA (manual, saved to rps_referensi) ═══ --}}
            <div class="sec-card">
                <div class="sec-title">📖 Pustaka Utama <em style="font-weight:400;font-size:11px;color:#6b7280">/ Main References</em></div>
                <p style="font-size:12px;color:#6b7280;margin-bottom:12px">Isi daftar pustaka utama yang wajib dibaca mahasiswa. Data ini akan tampil di bagian <strong>Pustaka</strong> pada dokumen RPS.</p>
                <div id="referensiUtamaList" style="display:flex;flex-direction:column;gap:8px">
                    @forelse($mk->rpsReferensis->where('jenis','utama')->sortBy('urutan') as $i => $ref)
                    <div class="ref-row-utama" style="display:flex;gap:8px;align-items:flex-start">
                        <span style="min-width:22px;font-size:12px;color:#9ca3af;font-weight:600;padding-top:9px">{{ $i+1 }}.</span>
                        <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:6px">
                            <input type="text" name="referensi_utama[][judul]" value="{{ $ref->judul }}" placeholder="Judul buku / artikel"
                                style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:100%">
                            <input type="text" name="referensi_utama[][penulis]" value="{{ $ref->penulis }}" placeholder="Penulis / Author"
                                style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:100%">
                            <input type="text" name="referensi_utama[][penerbit]" value="{{ $ref->penerbit }}" placeholder="Penerbit / Publisher"
                                style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:100%">
                            <div style="display:flex;gap:6px">
                                <input type="text" name="referensi_utama[][tahun]" value="{{ $ref->tahun }}" placeholder="Tahun" maxlength="4"
                                    style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:80px">
                                <input type="url" name="referensi_utama[][url]" value="{{ $ref->url }}" placeholder="URL (opsional)"
                                    style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;flex:1">
                            </div>
                        </div>
                        <button type="button" onclick="this.closest('.ref-row-utama').remove()"
                            style="color:#ef4444;font-size:18px;background:none;border:none;cursor:pointer;padding:4px 4px;line-height:1;flex-shrink:0;margin-top:4px">×</button>
                    </div>
                    @empty
                    {{-- empty template row --}}
                    @endforelse
                </div>
                <button type="button" onclick="addRefUtama()"
                    style="margin-top:10px;color:#2563eb;font-size:13px;font-weight:600;background:none;border:1.5px dashed #93c5fd;border-radius:8px;padding:7px 16px;width:100%;cursor:pointer">
                    + Tambah Pustaka Utama
                </button>
            </div>

            {{-- ═══ Referensi Publikasi Dosen → Pustaka Pendukung ═══ --}}
            @php
            $selectedPubIds = \Illuminate\Support\Facades\DB::table('rps_publikasi')
            ->where('mata_kuliah_id', $mk->id)
            ->pluck('publikasi_dosen_id')
            ->toArray();
            $dosenPengampu = \App\Models\User::where('role','dosen')
            ->orderBy('name')->get();
            $publikasiOptions = \App\Models\PublikasiDosen::with('dosen')
            ->whereIn('dosen_id', $dosenPengampu->pluck('id'))
            ->orderByDesc('tahun')->orderBy('judul')
            ->get();
            @endphp
            @if($publikasiOptions->isNotEmpty())
            <div class="sec-card" style="margin-top:0">
                <div class="sec-title">📚 Pustaka Pendukung — Publikasi Dosen <em style="font-weight:400;font-size:11px;color:#6b7280">/ Supplementary References</em></div>
                <p style="font-size:12px;color:#6b7280;margin-bottom:12px">Pilih publikasi dosen yang relevan sebagai <strong>pustaka pendukung</strong>. Data ini akan tampil di bagian Pustaka pada dokumen RPS.</p>
                {{-- ⚠️ NOT a nested form — uses JS fetch() to avoid nested form issue --}}
                <input type="hidden" id="pub_save_url" value="{{ route('rps.publikasi.save', $mk->kode) }}">
                <input type="hidden" id="pub_csrf" value="{{ csrf_token() }}">
                <div style="display:flex;flex-direction:column;gap:6px;max-height:300px;overflow-y:auto;border:1.5px solid #e5e7eb;border-radius:8px;padding:12px" id="publikasiCheckboxList">
                    @foreach($publikasiOptions as $pub)
                    <label style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;font-size:13px">
                        <input type="checkbox" class="pub-chk" value="{{ $pub->id }}"
                            {{ in_array($pub->id, $selectedPubIds) ? 'checked' : '' }}
                            style="margin-top:2px;flex-shrink:0">
                        <span>
                            <span style="background:{{ $pub->tipe==='penelitian' ? '#dbeafe' : '#dcfce7' }};color:{{ $pub->tipe==='penelitian' ? '#1e3a8a' : '#14532d' }};padding:1px 6px;border-radius:8px;font-size:10px;font-weight:700;margin-right:4px">{{ ucfirst($pub->tipe) }}</span>
                            <strong>{{ $pub->tahun ?? '-' }}</strong> — {{ Str::limit($pub->judul, 80) }}
                            <span style="color:#9ca3af;font-size:11px"> · {{ $pub->dosen?->name }}</span>
                        </span>
                    </label>
                    @endforeach
                </div>
                <div style="display:flex;align-items:center;gap:10px;margin-top:10px">
                    <button type="button" onclick="savePustakaPublikasi()"
                        id="btnSavePustaka"
                        style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer">
                        💾 Simpan Referensi Publikasi
                    </button>
                    <span id="pubSaveStatus" style="font-size:12px;color:#16a34a;display:none">✅ Tersimpan!</span>
                </div>
            </div>
            @endif

            {{-- ═══════════════ TAB: RKBM Pertemuan ═══════════════ --}}
            <div id="tab-rkbm" style="display:none">

                <div class="sec-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
                        <div>
                            <div class="sec-title" style="margin-bottom:4px;padding-bottom:0;border-bottom:none">📆 Rencana Kegiatan Belajar Mengajar</div>
                            <p style="font-size:12px;color:#9ca3af">16 pertemuan — Minggu 8 (UTS) dan 16 (UAS) terkunci otomatis.</p>
                        </div>
                        <div style="display:flex;gap:6px">
                            <button type="button" onclick="expandAll()"
                                style="font-size:12px;color:#2563eb;border:1.5px solid #93c5fd;border-radius:6px;padding:5px 12px;background:#eff6ff;cursor:pointer;font-weight:600">
                                ▼ Buka Semua
                            </button>
                            <button type="button" onclick="collapseAll()"
                                style="font-size:12px;color:#6b7280;border:1.5px solid #d1d5db;border-radius:6px;padding:5px 12px;background:#f9fafb;cursor:pointer;font-weight:600">
                                ▲ Tutup Semua
                            </button>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div style="background:#e5e7eb;border-radius:99px;height:6px;margin-bottom:16px;overflow:hidden">
                        <div style="background:#22c55e;height:100%;border-radius:99px;width:{{ round($filledCount/14*100) }}%;transition:width .3s"></div>
                    </div>

                    <div id="pertemuanAccordion">
                        @for($w = 1; $w <= 16; $w++)
                            @php
                            $p=$pertemuanMap[$w] ?? null;
                            $isUts=($w==8);
                            $isUas=($w==16);
                            $isLocked=$isUts || $isUas;
                            $subIds=$p ? ($p->sub_cpmk_ids ?? []) : [];
                            $hasData = $p && ($p->indikator || $p->materi || $p->metode_sinkron);
                            $selectedCodes = collect($allSubCpmks)->filter(fn($s) => in_array($s['id'], $subIds))->pluck('kode')->implode(', ');
                            @endphp

                            @if($isLocked)
                            {{-- ── Locked UTS / UAS ── --}}
                            <div class="week-card" style="border:2px solid #fde68a;background:#fffbeb">
                                <div class="week-card-head" style="cursor:default">
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <span style="background:#f59e0b;color:#fff;font-size:11px;font-weight:800;padding:3px 10px;border-radius:6px;min-width:48px;text-align:center">Mg {{ $w }}</span>
                                        <span style="font-size:13px;font-weight:700;color:#92400e">
                                            🔒 {{ $isUts ? 'UJIAN TENGAH SEMESTER (UTS)' : 'UJIAN AKHIR SEMESTER (UAS)' }}
                                        </span>
                                    </div>
                                    <span style="font-size:11px;color:#b45309;font-style:italic">Tidak dapat diedit</span>
                                </div>
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][minggu]" value="{{ $w }}">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][cpmk_label]" value="{{ $isUts ? 'UTS' : 'UAS' }}">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][indikator]" value="{{ $isUts ? 'Mahasiswa mampu menguasai materi pertemuan 1–7' : 'Mahasiswa mampu menguasai materi pertemuan 9–15' }}">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][teknik_penilaian]" value="Tes Tertulis">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][kreteria]" value="Pedoman Penskoran">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][metode_sinkron]" value="{{ $isUts ? 'Ujian Tengah Semester (UTS)' : 'Ujian Akhir Semester (UAS)' }}">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][metode_asinkron]" value="">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][tugas]" value="">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][materi]" value="{{ $isUts ? 'Komprehensif Materi Pertemuan 1–7' : 'Komprehensif Materi Pertemuan 9–15' }}">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][bobot]" value="0">
                                <input type="hidden" name="pertemuan[{{ $w-1 }}][dosen]" value="">
                            </div>

                            @else
                            {{-- ── Editable accordion card ── --}}
                            <div class="week-card" id="wcard-{{ $w }}"
                                style="border:1.5px solid {{ $hasData ? '#bfdbfe' : '#e5e7eb' }};background:{{ $hasData ? '#f0f7ff' : '#fff' }}">

                                <div class="week-card-head"
                                    onclick="toggleCard({{ $w }})"
                                    style="background:{{ $hasData ? '#eff6ff' : '#fafafa' }}">
                                    <div style="display:flex;align-items:center;gap:10px;min-width:0">
                                        <span style="background:{{ $hasData ? '#1d4ed8' : '#9ca3af' }};color:#fff;font-size:11px;font-weight:800;padding:3px 10px;border-radius:6px;min-width:48px;text-align:center;flex-shrink:0">
                                            Mg {{ $w }}
                                        </span>
                                        <span style="font-size:13px;color:{{ $hasData ? '#1e3a8a' : '#6b7280' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:400px">
                                            @if($selectedCodes)
                                            <strong>{{ $selectedCodes }}</strong>
                                            @if($hasData && $p->materi)
                                            <span style="color:#9ca3af;font-size:11px;margin-left:6px">— {{ Str::limit($p->materi, 50) }}</span>
                                            @endif
                                            @elseif($hasData)
                                            {{ Str::limit($p->materi ?? $p->indikator ?? '–', 70) }}
                                            @else
                                            <span style="font-style:italic;color:#9ca3af">Belum diisi — klik untuk mengisi</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $hasData ? '#22c55e' : '#d1d5db' }};display:inline-block" title="{{ $hasData ? 'Sudah diisi' : 'Belum diisi' }}"></span>
                                        <span id="chevron-{{ $w }}" style="font-size:11px;color:#9ca3af;transition:transform .2s">▼</span>
                                    </div>
                                </div>

                                <div class="week-card-body" id="wbody-{{ $w }}">
                                    <input type="hidden" name="pertemuan[{{ $w-1 }}][minggu]" value="{{ $w }}">

                                    {{-- ── Row 1: Sub CPMK + Label + Bobot + Dosen ── --}}
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                                        <div>
                                            <label class="field-label">🎯 Sub CPMK yang Dicapai</label>
                                            <div style="border:1.5px solid #e5e7eb;border-radius:8px;padding:8px;background:#f9fafb;max-height:120px;overflow-y:auto;display:grid;grid-template-columns:1fr 1fr;gap:4px">
                                                @foreach($allSubCpmks as $sub)
                                                <label style="display:flex;align-items:flex-start;gap:6px;cursor:pointer;font-size:12px;line-height:1.4">
                                                    <input type="checkbox"
                                                        name="pertemuan[{{ $w-1 }}][sub_cpmk_ids][]"
                                                        value="{{ $sub['id'] }}"
                                                        style="margin-top:2px;flex-shrink:0"
                                                        {{ in_array($sub['id'], $subIds) ? 'checked' : '' }}>
                                                    <span style="font-weight:700;color:#15803d">{{ $sub['kode'] }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div style="display:flex;flex-direction:column;gap:10px">
                                            <div>
                                                <label class="field-label">🏷️ Label CPMK</label>
                                                <input type="text" name="pertemuan[{{ $w-1 }}][cpmk_label]"
                                                    class="f-input" placeholder="Contoh: SubCPMK 1.1"
                                                    value="{{ old('pertemuan.'.($w-1).'.cpmk_label', $p?->cpmk_label ?? '') }}">
                                            </div>
                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                                                <div>
                                                    <label class="field-label">⚖️ Bobot (%)</label>
                                                    <input type="number" name="pertemuan[{{ $w-1 }}][bobot]"
                                                        class="f-input" style="text-align:center"
                                                        min="0" max="100" step="0.5" placeholder="0"
                                                        value="{{ old('pertemuan.'.($w-1).'.bobot', $p?->bobot ?? '') }}">
                                                </div>
                                                <div>
                                                    <label class="field-label">👩‍🏫 Dosen</label>
                                                    <input type="text" name="pertemuan[{{ $w-1 }}][dosen]"
                                                        class="f-input" placeholder="Inisial"
                                                        value="{{ old('pertemuan.'.($w-1).'.dosen', $p?->dosen ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ── Row 2: Indikator + Materi ── --}}
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                                        <div>
                                            <label class="field-label">📊 Indikator Capaian</label>
                                            <textarea name="pertemuan[{{ $w-1 }}][indikator]" rows="3"
                                                id="ind_id_{{ $w }}"
                                                class="f-input f-textarea"
                                                placeholder="1.1 Mampu menjelaskan konsep...&#10;1.2 Mampu mengidentifikasi...">{{ old('pertemuan.'.($w-1).'.indikator', $p?->indikator ?? '') }}</textarea>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                                <label class="field-label" style="margin:0;font-size:10px;color:#1a5276">🇬🇧 EN:</label>
                                                <button type="button" onclick="translateField('ind_id_{{ $w }}','ind_en_{{ $w }}')" style="font-size:10px;padding:2px 8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:4px;cursor:pointer">🌐 Translate</button>
                                            </div>
                                            <textarea name="pertemuan[{{ $w-1 }}][indikator_en]" rows="2"
                                                id="ind_en_{{ $w }}"
                                                class="f-input f-textarea"
                                                style="background:#f0f9ff;font-style:italic;font-size:10.5pt;margin-top:2px"
                                                placeholder="English translation...">{{ old('pertemuan.'.($w-1).'.indikator_en', $p?->indikator_en ?? '') }}</textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">📖 Materi / Pustaka</label>
                                            <textarea name="pertemuan[{{ $w-1 }}][materi]" rows="3"
                                                id="mat_id_{{ $w }}"
                                                class="f-input f-textarea"
                                                placeholder="Topik materi + referensi buku...">{{ old('pertemuan.'.($w-1).'.materi', $p?->materi ?? '') }}</textarea>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                                <label class="field-label" style="margin:0;font-size:10px;color:#1a5276">🇬🇧 EN:</label>
                                                <button type="button" onclick="translateField('mat_id_{{ $w }}','mat_en_{{ $w }}')" style="font-size:10px;padding:2px 8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:4px;cursor:pointer">🌐 Translate</button>
                                            </div>
                                            <textarea name="pertemuan[{{ $w-1 }}][materi_en]" rows="2"
                                                id="mat_en_{{ $w }}"
                                                class="f-input f-textarea"
                                                style="background:#f0f9ff;font-style:italic;font-size:10.5pt;margin-top:2px"
                                                placeholder="English translation...">{{ old('pertemuan.'.($w-1).'.materi_en', $p?->materi_en ?? '') }}</textarea>
                                            {{-- Publikasi Picker Button --}}
                                            @if($allPublikasiForPicker->isNotEmpty())
                                            <div style="position:relative;margin-top:6px">
                                                <button type="button"
                                                    onclick="togglePubPicker({{ $w }}, this)"
                                                    style="font-size:11px;padding:4px 10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:6px;cursor:pointer;color:#15803d;font-weight:600">
                                                    📚 Tambah dari Publikasi
                                                </button>
                                                <div id="pub-picker-{{ $w }}"
                                                    style="display:none;position:absolute;z-index:99;background:#fff;border:1.5px solid #d1d5db;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:12px;width:420px;max-height:280px;overflow-y:auto;left:0;top:28px">
                                                    <p style="font-size:11px;color:#6b7280;margin:0 0 8px">Klik untuk menyisipkan sitasi ke field Materi / Pustaka:</p>
                                                    <div style="display:flex;flex-direction:column;gap:4px">
                                                        @foreach($allPublikasiForPicker as $pub)
                                                        <button type="button"
                                                            onclick="insertPubCitation({{ $w }}, {{ json_encode($pub['citation']) }})"
                                                            style="text-align:left;padding:6px 8px;border:1px solid #e5e7eb;border-radius:6px;background:#fafafa;font-size:11px;cursor:pointer;line-height:1.4;color:#374151"
                                                            onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fafafa'">
                                                            <span style="background:{{ $pub['tipe']==='penelitian' ? '#dbeafe' : '#dcfce7' }};color:{{ $pub['tipe']==='penelitian' ? '#1e3a8a' : '#14532d' }};padding:1px 5px;border-radius:4px;font-size:9px;font-weight:700;margin-right:4px">{{ ucfirst($pub['tipe']) }}</span>
                                                            {{ $pub['label'] }}
                                                        </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- ── Row 3: Teknik + Kreteria ── --}}
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                                        <div>
                                            <label class="field-label">📝 Teknik Penilaian</label>
                                            <textarea name="pertemuan[{{ $w-1 }}][teknik_penilaian]" rows="2"
                                                id="tek_id_{{ $w }}"
                                                class="f-input f-textarea"
                                                placeholder="Teknik Non-test: Observasi, Rubrik Penilaian">{{ old('pertemuan.'.($w-1).'.teknik_penilaian', $p?->teknik_penilaian ?? '') }}</textarea>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                                <label class="field-label" style="margin:0;font-size:10px;color:#1a5276">🇬🇧 EN:</label>
                                                <button type="button" onclick="translateField('tek_id_{{ $w }}','tek_en_{{ $w }}')" style="font-size:10px;padding:2px 8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:4px;cursor:pointer">🌐 Translate</button>
                                            </div>
                                            <textarea name="pertemuan[{{ $w-1 }}][teknik_penilaian_en]" rows="2"
                                                id="tek_en_{{ $w }}"
                                                class="f-input f-textarea"
                                                style="background:#f0f9ff;font-style:italic;font-size:10.5pt;margin-top:2px"
                                                placeholder="English...">{{ old('pertemuan.'.($w-1).'.teknik_penilaian_en', $p?->teknik_penilaian_en ?? '') }}</textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">🔍 Kreteria Penilaian</label>
                                            <textarea name="pertemuan[{{ $w-1 }}][kreteria]" rows="2"
                                                id="kret_id_{{ $w }}"
                                                class="f-input f-textarea"
                                                placeholder="Pedoman Penskoran (Marking Scheme)">{{ old('pertemuan.'.($w-1).'.kreteria', $p?->kreteria ?? '') }}</textarea>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                                <label class="field-label" style="margin:0;font-size:10px;color:#1a5276">🇬🇧 EN:</label>
                                                <button type="button" onclick="translateField('kret_id_{{ $w }}','kret_en_{{ $w }}')" style="font-size:10px;padding:2px 8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:4px;cursor:pointer">🌐 Translate</button>
                                            </div>
                                            <textarea name="pertemuan[{{ $w-1 }}][kreteria_en]" rows="2"
                                                id="kret_en_{{ $w }}"
                                                class="f-input f-textarea"
                                                style="background:#f0f9ff;font-style:italic;font-size:10.5pt;margin-top:2px"
                                                placeholder="English...">{{ old('pertemuan.'.($w-1).'.kreteria_en', $p?->kreteria_en ?? '') }}</textarea>
                                        </div>
                                    </div>

                                    {{-- ── Row 4: Sinkron + Asinkron + Tugas ── --}}
                                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
                                        <div>
                                            <label class="field-label">🏫 Metode Sinkron</label>
                                            <textarea name="pertemuan[{{ $w-1 }}][metode_sinkron]" rows="2"
                                                id="sin_id_{{ $w }}"
                                                class="f-input f-textarea"
                                                placeholder="Kuliah; Diskusi&#10;[PB: 1×(2×50')]">{{ old('pertemuan.'.($w-1).'.metode_sinkron', $p?->metode_sinkron ?? '') }}</textarea>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                                <label class="field-label" style="margin:0;font-size:10px;color:#1a5276">🇬🇧 EN:</label>
                                                <button type="button" onclick="translateField('sin_id_{{ $w }}','sin_en_{{ $w }}')" style="font-size:10px;padding:2px 8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:4px;cursor:pointer">🌐 Translate</button>
                                            </div>
                                            <textarea name="pertemuan[{{ $w-1 }}][metode_sinkron_en]" rows="2"
                                                id="sin_en_{{ $w }}"
                                                class="f-input f-textarea"
                                                style="background:#f0f9ff;font-style:italic;font-size:10.5pt;margin-top:2px"
                                                placeholder="English...">{{ old('pertemuan.'.($w-1).'.metode_sinkron_en', $p?->metode_sinkron_en ?? '') }}</textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">💻 Metode Asinkron</label>
                                            <textarea name="pertemuan[{{ $w-1 }}][metode_asinkron]" rows="2"
                                                id="asn_id_{{ $w }}"
                                                class="f-input f-textarea"
                                                placeholder="eLearning; Diskusi Asinkron&#10;[BM: 1×(2×60')]">{{ old('pertemuan.'.($w-1).'.metode_asinkron', $p?->metode_asinkron ?? '') }}</textarea>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                                <label class="field-label" style="margin:0;font-size:10px;color:#1a5276">🇬🇧 EN:</label>
                                                <button type="button" onclick="translateField('asn_id_{{ $w }}','asn_en_{{ $w }}')" style="font-size:10px;padding:2px 8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:4px;cursor:pointer">🌐 Translate</button>
                                            </div>
                                            <textarea name="pertemuan[{{ $w-1 }}][metode_asinkron_en]" rows="2"
                                                id="asn_en_{{ $w }}"
                                                class="f-input f-textarea"
                                                style="background:#f0f9ff;font-style:italic;font-size:10.5pt;margin-top:2px"
                                                placeholder="English...">{{ old('pertemuan.'.($w-1).'.metode_asinkron_en', $p?->metode_asinkron_en ?? '') }}</textarea>
                                        </div>
                                        <div>
                                            <label class="field-label">📋 Penugasan Mahasiswa</label>
                                            <textarea name="pertemuan[{{ $w-1 }}][tugas]" rows="2"
                                                id="tgs_id_{{ $w }}"
                                                class="f-input f-textarea"
                                                placeholder="Tugas-1: Menyusun ringkasan...&#10;[PT:1mgx(2sksx60')]">{{ old('pertemuan.'.($w-1).'.tugas', $p?->tugas ?? '') }}</textarea>
                                            <div style="display:flex;align-items:center;gap:6px;margin-top:4px">
                                                <label class="field-label" style="margin:0;font-size:10px;color:#1a5276">🇬🇧 EN:</label>
                                                <button type="button" onclick="translateField('tgs_id_{{ $w }}','tgs_en_{{ $w }}')" style="font-size:10px;padding:2px 8px;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:4px;cursor:pointer">🌐 Translate</button>
                                            </div>
                                            <textarea name="pertemuan[{{ $w-1 }}][tugas_en]" rows="2"
                                                id="tgs_en_{{ $w }}"
                                                class="f-input f-textarea"
                                                style="background:#f0f9ff;font-style:italic;font-size:10.5pt;margin-top:2px"
                                                placeholder="English...">{{ old('pertemuan.'.($w-1).'.tugas_en', $p?->tugas_en ?? '') }}</textarea>
                                        </div>
                                    </div>

                                </div>{{-- /wbody --}}
                            </div>{{-- /week-card --}}
                            @endif
                            @endfor
                    </div>{{-- /pertemuanAccordion --}}
                </div>

            </div>{{-- /tab-rkbm --}}

            {{-- ═══════════════ SAVE BAR ═══════════════ --}}
            <div style="position:sticky;bottom:16px;z-index:40;background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 4px 20px rgba(0,0,0,.12);margin-top:8px">
                <a href="{{ route('rps.show', $mk->kode) }}"
                    style="color:#6b7280;font-size:13px;font-weight:600;text-decoration:none;border:1.5px solid #d1d5db;padding:9px 20px;border-radius:8px;display:inline-flex;align-items:center;gap:6px">
                    ← Batal
                </a>
                <div style="display:flex;gap:8px">
                    <button type="button" onclick="document.getElementById('rpsForm').submit()"
                        style="background:#f0fdf4;color:#166534;border:1.5px solid #86efac;font-weight:700;font-size:13px;padding:9px 20px;border-radius:8px;cursor:pointer">
                        💾 Simpan Draft
                    </button>
                    <button type="submit"
                        style="background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:#fff;font-weight:700;font-size:13px;padding:9px 28px;border-radius:8px;cursor:pointer;border:none;box-shadow:0 2px 8px rgba(29,78,216,.3)">
                        💾 Simpan &amp; Preview RPS →
                    </button>
                </div>
            </div>

        </form>
    </div>{{-- /main form --}}

</div>{{-- /main grid --}}

<script>
    // ── Tab switching ──────────────────────────────────────
    function showTab(id, btn) {
        document.querySelectorAll('#tab-umum, #tab-rkbm').forEach(t => t.style.display = 'none');
        document.getElementById(id).style.display = 'block';
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        if (id === 'tab-umum') syncPustaka();
    }

    // ── Publikasi Picker ────────────────────────────────────
    function togglePubPicker(week, btn) {
        const picker = document.getElementById('pub-picker-' + week);
        if (!picker) return;
        const isVisible = picker.style.display !== 'none';
        // close all pickers first
        document.querySelectorAll('[id^="pub-picker-"]').forEach(el => el.style.display = 'none');
        picker.style.display = isVisible ? 'none' : 'block';
        // close when clicking outside
        if (!isVisible) {
            setTimeout(() => {
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && e.target !== btn) {
                        picker.style.display = 'none';
                        document.removeEventListener('click', closePicker);
                    }
                });
            }, 100);
        }
    }

    function insertPubCitation(week, citation) {
        const ta = document.getElementById('mat_id_' + week);
        if (!ta) return;
        const current = ta.value.trim();
        // avoid duplicating
        if (current.includes(citation.substring(0, 30))) {
            document.getElementById('pub-picker-' + week).style.display = 'none';
            return;
        }
        ta.value = current ? current + '\n' + citation : citation;
        ta.dispatchEvent(new Event('input'));
        document.getElementById('pub-picker-' + week).style.display = 'none';
    }

    // ── Pustaka Sync ───────────────────────────────────────
    function translateField(srcId, tgtId) {
        const src = document.getElementById(srcId);
        const tgt = document.getElementById(tgtId);
        if (!src || !tgt || !src.value.trim()) return;
        tgt.value = '⏳ Translating...';
        const text = encodeURIComponent(src.value.trim());
        fetch(`https://api.mymemory.translated.net/get?q=${text}&langpair=id|en`)
            .then(r => r.json())
            .then(d => {
                tgt.value = d.responseData?.translatedText || '';
            })
            .catch(() => {
                tgt.value = '';
                alert('Translation failed. Please enter manually.');
            });
    }

    function syncPustaka() {
        let anyFilled = false;
        for (let w = 1; w <= 16; w++) {
            if (w === 8 || w === 16) continue;
            const ta = document.querySelector(`textarea[name="pertemuan[${w-1}][materi]"]`);
            if (!ta) continue;
            const val = ta.value.trim();
            const span = document.querySelector(`.pustaka-text-${w}`);
            const row = document.getElementById(`pustaka-row-${w}`);
            if (val) {
                anyFilled = true;
                if (span) span.textContent = val;
                if (row) row.style.display = 'block';
            } else {
                if (row) row.style.display = 'none';
            }
        }
        const emptyMsg = document.getElementById('pustaka-empty-msg');
        if (emptyMsg) emptyMsg.style.display = anyFilled ? 'none' : '';
    }

    // Auto-sync when typing in materi fields
    document.addEventListener('input', function(e) {
        const name = e.target?.name ?? '';
        if (name.includes('[materi]')) syncPustaka();
    });

    // ── Accordion ──────────────────────────────────────────
    function toggleCard(w) {
        const body = document.getElementById('wbody-' + w);
        const chevron = document.getElementById('chevron-' + w);
        const isOpen = body.classList.contains('open');
        if (isOpen) {
            body.classList.remove('open');
            chevron.style.transform = 'rotate(0deg)';
        } else {
            body.classList.add('open');
            chevron.style.transform = 'rotate(180deg)';
        }
    }

    function expandAll() {
        document.querySelectorAll('.week-card-body:not(.week-card-body[style])').forEach(b => b.classList.add('open'));
        document.querySelectorAll('[id^="wbody-"]').forEach(b => b.classList.add('open'));
        document.querySelectorAll('[id^="chevron-"]').forEach(c => c.style.transform = 'rotate(180deg)');
    }

    function collapseAll() {
        document.querySelectorAll('[id^="wbody-"]').forEach(b => b.classList.remove('open'));
        document.querySelectorAll('[id^="chevron-"]').forEach(c => c.style.transform = 'rotate(0deg)');
    }

    // ── Dosen Pengampu ─────────────────────────────────────
    function addDosen() {
        const list = document.getElementById('dosenList');
        const idx = list.querySelectorAll('.dosen-row').length + 1;
        const div = document.createElement('div');
        div.className = 'dosen-row';
        div.style.cssText = 'display:flex;align-items:center;gap:8px';
        div.innerHTML = `
        <span style="min-width:20px;font-size:13px;color:#9ca3af;font-weight:600">${idx}.</span>
        <input type="text" name="dosen_pengampu[]" class="f-input" style="flex:1"
               placeholder="Nama Lengkap, Gelar (Inisial)">
        <button type="button" onclick="removeDosen(this)"
                style="color:#ef4444;font-size:18px;background:none;border:none;cursor:pointer;padding:0 4px;line-height:1">×</button>
    `;
        list.appendChild(div);
    }

    function removeDosen(btn) {
        btn.closest('.dosen-row').remove();
        renumberList('dosenList', 'dosen-row');
    }

    // ── Pustaka Pendukung (Publikasi) — AJAX save ───────────
    function savePustakaPublikasi() {
        const url   = document.getElementById('pub_save_url')?.value;
        const csrf  = document.getElementById('pub_csrf')?.value;
        const ids   = [...document.querySelectorAll('.pub-chk:checked')].map(c => c.value);
        const btn   = document.getElementById('btnSavePustaka');
        const status = document.getElementById('pubSaveStatus');
        if (!url || !csrf) return;

        btn.disabled = true;
        btn.textContent = '⏳ Menyimpan...';
        status.style.display = 'none';

        const body = new URLSearchParams();
        body.append('_token', csrf);
        ids.forEach(id => body.append('publikasi_ids[]', id));

        fetch(url, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.ok ? r.json().catch(() => ({})) : Promise.reject(r.status))
            .then(() => {
                btn.disabled = false;
                btn.textContent = '💾 Simpan Referensi Publikasi';
                status.style.display = 'inline';
                setTimeout(() => status.style.display = 'none', 3000);
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = '💾 Simpan Referensi Publikasi';
                alert('Gagal menyimpan referensi publikasi. Error: ' + err);
            });
    }

    // ── Pustaka Utama ──────────────────────────────────────
    function addRefUtama() {
        const list = document.getElementById('referensiUtamaList');
        const idx = list.querySelectorAll('.ref-row-utama').length + 1;
        const div = document.createElement('div');
        div.className = 'ref-row-utama';
        div.style.cssText = 'display:flex;gap:8px;align-items:flex-start';
        div.innerHTML = `
        <span style="min-width:22px;font-size:12px;color:#9ca3af;font-weight:600;padding-top:9px">${idx}.</span>
        <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:6px">
            <input type="text" name="referensi_utama[][judul]" placeholder="Judul buku / artikel"
                style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:100%">
            <input type="text" name="referensi_utama[][penulis]" placeholder="Penulis / Author"
                style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:100%">
            <input type="text" name="referensi_utama[][penerbit]" placeholder="Penerbit / Publisher"
                style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:100%">
            <div style="display:flex;gap:6px">
                <input type="text" name="referensi_utama[][tahun]" placeholder="Tahun" maxlength="4"
                    style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;width:80px">
                <input type="url" name="referensi_utama[][url]" placeholder="URL (opsional)"
                    style="border:1.5px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:12px;flex:1">
            </div>
        </div>
        <button type="button" onclick="this.closest('.ref-row-utama').remove()"
            style="color:#ef4444;font-size:18px;background:none;border:none;cursor:pointer;padding:4px 4px;line-height:1;flex-shrink:0;margin-top:4px">×</button>
        `;
        list.appendChild(div);
    }

    // ── Ketentuan ──────────────────────────────────────────
    function addKetentuan() {
        const list = document.getElementById('ketentuanList');
        const idx = list.querySelectorAll('.ketentuan-row').length + 1;
        const div = document.createElement('div');
        div.className = 'ketentuan-row';
        div.style.cssText = 'display:flex;align-items:flex-start;gap:8px';
        div.innerHTML = `
        <span style="min-width:20px;font-size:13px;color:#9ca3af;font-weight:600;padding-top:8px">${idx}.</span>
        <textarea name="ketentuan_tambahan[]" rows="2" class="f-input f-textarea" style="flex:1"></textarea>
        <button type="button" onclick="removeKetentuan(this)"
                style="color:#ef4444;font-size:18px;background:none;border:none;cursor:pointer;padding:0 4px;padding-top:4px;line-height:1">×</button>
    `;
        list.appendChild(div);
    }

    function removeKetentuan(btn) {
        btn.closest('.ketentuan-row').remove();
        renumberList('ketentuanList', 'ketentuan-row');
    }

    // ── Jadwal ─────────────────────────────────────────────
    function addJadwalRow() {
        const tbody = document.getElementById('jadwalBody');
        const idx = tbody.querySelectorAll('.jadwal-row').length;
        const rowNum = idx + 1;
        const tr = document.createElement('tr');
        tr.className = 'jadwal-row';
        tr.innerHTML = `
        <td style="border:1.5px solid #e5e7eb;padding:4px 8px;text-align:center;font-size:11px;color:#9ca3af;font-weight:600">${rowNum}</td>
        <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
            <input type="text" name="jadwal_kuliah[${idx}][hari]" class="f-input" style="border:none;padding:4px 0;box-shadow:none" placeholder="Senin / 01 Jan 2025">
        </td>
        <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
            <input type="text" name="jadwal_kuliah[${idx}][waktu]" class="f-input" style="border:none;padding:4px 0;box-shadow:none" placeholder="08.00–09.40 WITA">
        </td>
        <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
            <input type="text" name="jadwal_kuliah[${idx}][dosen]" class="f-input" style="border:none;padding:4px 0;box-shadow:none" placeholder="Nama Dosen">
        </td>
        <td style="border:1.5px solid #e5e7eb;padding:4px;text-align:center">
            <button type="button" onclick="removeRow(this)" style="color:#ef4444;background:none;border:none;font-size:18px;cursor:pointer;line-height:1">×</button>
        </td>
    `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        renumberJadwal();
    }

    function renumberJadwal() {
        const tbody = document.getElementById('jadwalBody');
        tbody.querySelectorAll('.jadwal-row').forEach((tr, i) => {
            const numCell = tr.querySelector('td:first-child');
            if (numCell) numCell.textContent = i + 1;
            // Re-index input names
            tr.querySelectorAll('input[name]').forEach(inp => {
                inp.name = inp.name.replace(/jadwal_kuliah\[\d+\]/, `jadwal_kuliah[${i}]`);
            });
        });
    }

    async function generateJadwal() {
        const hari = document.getElementById('gen_hari').value;
        const mulai = document.getElementById('gen_mulai').value;
        const waktu = document.getElementById('gen_waktu').value;
        const dosen = document.getElementById('gen_dosen').value;
        const btn = document.getElementById('gen_btn');
        const status = document.getElementById('gen_status');

        if (!mulai) {
            alert('Pilih tanggal kuliah pertama terlebih dahulu.');
            return;
        }

        btn.disabled = true;
        btn.textContent = '⏳ Memproses...';
        status.textContent = '';
        document.getElementById('gen_libur_info').style.display = 'none';

        try {
            const url = `/api/jadwal-generator?hari=${hari}&mulai=${encodeURIComponent(mulai)}&waktu=${encodeURIComponent(waktu)}&dosen=${encodeURIComponent(dosen)}`;
            const res = await fetch(url);
            if (!res.ok) throw new Error('Gagal menghubungi server.');
            const data = await res.json();

            if (!data.jadwal || !data.jadwal.length) {
                status.textContent = '⚠️ Tidak ada jadwal dihasilkan.';
                return;
            }

            // Clear existing rows
            const tbody = document.getElementById('jadwalBody');
            tbody.innerHTML = '';

            let realIdx = 0;
            const liburList = [];

            data.jadwal.forEach(row => {
                if (row.info === 'libur') {
                    liburList.push(`${row.hari} (${(row.waktu||'').replace('— Libur: ','') || 'Libur'})`);
                    return; // skip libur rows from form (they are not actual meetings)
                }
                const tr = document.createElement('tr');
                tr.className = 'jadwal-row';
                const infoLower = (row.info || '').toLowerCase();
                const isSpecial = infoLower === 'uts' || infoLower === 'uas';
                const specialLabel = infoLower === 'uts' ? ' 🎓 UTS' : (infoLower === 'uas' ? ' 🎓 UAS' : '');
                const displayWaktu = isSpecial ? (document.getElementById('gen_waktu').value + ' ' + row.info) : row.waktu;
                const bg = isSpecial ? 'background:#f0fdf4;' : '';
                tr.style.cssText = bg;
                tr.innerHTML = `
                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px;text-align:center;font-size:11px;color:#9ca3af;font-weight:600">${realIdx + 1}</td>
                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                        <input type="text" name="jadwal_kuliah[${realIdx}][hari]" class="f-input"
                            style="border:none;padding:4px 0;box-shadow:none;${isSpecial?'font-weight:700;color:#166534;':''}"
                            value="${row.hari}${specialLabel}">
                    </td>
                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                        <input type="text" name="jadwal_kuliah[${realIdx}][waktu]" class="f-input"
                            style="border:none;padding:4px 0;box-shadow:none;${isSpecial?'font-weight:700;color:#166534;':''}" value="${displayWaktu}">
                    </td>
                    <td style="border:1.5px solid #e5e7eb;padding:4px 8px">
                        <input type="text" name="jadwal_kuliah[${realIdx}][dosen]" class="f-input"
                            style="border:none;padding:4px 0;box-shadow:none" value="${row.dosen}">
                    </td>
                    <td style="border:1.5px solid #e5e7eb;padding:4px;text-align:center">
                        <button type="button" onclick="removeRow(this)" style="color:#ef4444;background:none;border:none;font-size:18px;cursor:pointer;line-height:1">×</button>
                    </td>
                `;
                tbody.appendChild(tr);
                realIdx++;
            });

            const skipped = data.jadwal.filter(r => r.info === 'libur').length;
            status.innerHTML = `<span style="color:#16a34a;font-weight:700">✅ ${realIdx} pertemuan dihasilkan</span>${skipped ? ` <span style="color:#92400e">• ${skipped} hari libur dilewati</span>` : ''}`;

            if (liburList.length) {
                document.getElementById('gen_libur_list').textContent = ' ' + liburList.join(' | ');
                document.getElementById('gen_libur_info').style.display = 'block';
            }

        } catch (e) {
            status.textContent = '❌ Error: ' + e.message;
        } finally {
            btn.disabled = false;
            btn.innerHTML = '✨ Generate 16 Jadwal Otomatis';
        }
    }

    // ── Helpers ────────────────────────────────────────────
    function renumberList(listId, rowClass) {
        const list = document.getElementById(listId);
        if (!list) return;
        list.querySelectorAll('.' + rowClass).forEach((row, i) => {
            const span = row.querySelector('span');
            if (span) span.textContent = (i + 1) + '.';
        });
    }
</script>
@endsection