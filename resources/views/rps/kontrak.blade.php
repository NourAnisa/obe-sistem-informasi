@extends('layouts.dashboard')
@section('title', 'Kontrak Pembelajaran — ' . $rps['header']['kode_mk'])
@section('breadcrumb', 'Kontrak — ' . $rps['header']['kode_mk'])

@push('styles')
<style>
    @media screen {
        body {
            background: #e5e7eb;
        }
    }

    .rps-wrapper {
        padding: 16px 8px;
    }

    .doc-portrait {
        font-family: 'Times New Roman', Times, serif;
        font-size: 10pt;
        line-height: 1.4;
        color: #000;
        background: #fff;
        width: 210mm;
        max-width: 210mm;
        margin: 0 auto;
        padding: 18mm 20mm;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
    }

    .doc-portrait table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.5pt;
        color: #000;
    }

    .doc-portrait table td,
    .doc-portrait table th {
        border: 1px solid #000;
        padding: 3px 6px;
        vertical-align: top;
    }

    .cell-label {
        font-weight: bold;
        vertical-align: middle;
        white-space: nowrap;
        background: #fff;
    }

    .blueprint-inner {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
    }

    .blueprint-inner th,
    .blueprint-inner td {
        border: 1px solid #555;
        padding: 2px 4px;
        vertical-align: top;
    }

    .blueprint-inner th {
        background: #d9d9d9;
        text-align: center;
        font-weight: bold;
    }

    .section-page-title {
        font-size: 12pt;
        font-weight: bold;
        text-align: center;
        margin: 6px 0 4px;
        color: #000;
    }

    .no-print {
        position: sticky;
        top: 0;
        z-index: 50;
        background: #7c3aed;
        color: #fff;
        padding: 7px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        width: 210mm;
        max-width: 210mm;
        margin: 0 auto 10px;
        border-radius: 0 0 8px 8px;
        box-sizing: border-box;
    }

    .no-print a,
    .no-print button {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        font-family: system-ui, sans-serif;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 18mm 20mm;
        }

        .no-print {
            display: none !important;
        }

        aside,
        header.topbar,
        nav {
            display: none !important;
        }

        body {
            background: white;
        }

        main {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }

        .rps-wrapper {
            padding: 0;
        }

        .doc-portrait {
            box-shadow: none;
            padding: 0;
            width: 100%;
            max-width: 100%;
            margin: 0;
        }
    }
</style>
@endpush

@section('content')
@php
$h = $rps['header'];
$kontrak = $rps['kontrak'];
$referensiU = $rps['referensiUtama'];
$referensiP = $rps['referensiPendukung'];
$rencanaTugas = $rps['rencanaTugas'];
$detail = $rps['detail'] ?? null;
$dosenList = $detail?->dosen_pengampu ?? [];
if (empty($dosenList)) $dosenList = [$h['pjmk']];
@endphp

<div class="rps-wrapper">

    {{-- Action Bar --}}
    <div class="no-print">
        <span style="font-size:13px;font-weight:700;font-family:system-ui,sans-serif;margin-right:4px">
            📑 Kontrak: {{ $h['kode_mk'] }}
        </span>
        <a href="{{ route('rps.show', $mk->kode) }}"
            style="background:#1e3a8a;color:#fff">← RPS</a>
        <a href="{{ route('rps.edit', $mk->kode) }}"
            style="background:#f59e0b;color:#000">✏️ Edit</a>
        <button onclick="window.print()"
            style="background:#10b981;color:#fff">🖨️ Cetak</button>
        <a href="{{ route('rps.index') }}"
            style="background:#6b7280;color:#fff">← Daftar RPS</a>
    </div>

    {{-- ══ KONTRAK PEMBELAJARAN ══ --}}
    <div class="doc-portrait">
        <p class="section-page-title">KONTRAK PEMBELAJARAN</p>

        <table>
            <tr>
                <td width="80" style="text-align:center;vertical-align:middle;border:none">
                    <img src="{{ asset('images/logo_unism.png') }}" alt="Logo UNISM" style="height:70px;width:auto">
                </td>
                <td style="text-align:center;vertical-align:middle">
                    <div style="font-size:13pt;font-weight:bold">UNIVERSITAS SARI MULIA</div>
                    <div style="font-size:10pt;font-weight:bold">FAKULTAS {{ strtoupper($h['fakultas']) }}</div>
                    <div style="font-size:10pt;font-weight:bold">PROGRAM STUDI {{ strtoupper($h['prodi']) }}</div>
                    <div style="font-size:10pt">TAHUN AKADEMIK {{ $h['tahun_akademik'] }}</div>
                </td>
                <td width="130" style="text-align:center;vertical-align:middle">
                    <div style="font-size:10pt;font-weight:bold;line-height:1.4">KONTRAK<br>PEMBELAJARAN</div>
                </td>
            </tr>
        </table>

        <table style="margin-top:-1px">
            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>MATA KULIAH</strong></td>
            </tr>
            <tr>
                <td width="130" class="cell-label">Nama Mata Kuliah</td>
                <td>{{ $h['nama_mk'] }}</td>
            </tr>
            <tr>
                <td class="cell-label">Kode</td>
                <td>{{ $h['kode_mk'] }}</td>
            </tr>
            <tr>
                <td class="cell-label">Bobot SKS</td>
                <td>{{ $h['sks'] }} SKS ({{ $h['sks_teori'] ?? $h['sks'] }}T + {{ $h['sks_praktikum'] ?? 0 }}P)</td>
            </tr>
            <tr>
                <td class="cell-label">Semester</td>
                <td>Semester {{ $h['semester'] }}</td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>DESKRIPSI MATA KULIAH</strong></td>
            </tr>
            <tr>
                <td colspan="2">{{ $h['deskripsi'] ?: '–' }}</td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>CAPAIAN PEMBELAJARAN LULUSAN (CPL)</strong></td>
            </tr>
            @foreach($kontrak['cpls'] as $cpl)
            <tr>
                <td style="font-weight:bold;white-space:nowrap">{{ $cpl->kode }}</td>
                <td>{{ $cpl->deskripsi }}</td>
            </tr>
            @endforeach

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>CAPAIAN PEMBELAJARAN MATA KULIAH (CPMK)</strong></td>
            </tr>
            @foreach($kontrak['cpmks'] as $cpmk)
            <tr>
                <td style="font-weight:bold;white-space:nowrap">{{ $cpmk->kode }}</td>
                <td>{{ $cpmk->deskripsi }}</td>
            </tr>
            @endforeach

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>SUB CAPAIAN PEMBELAJARAN MATA KULIAH (SUB CPMK)</strong></td>
            </tr>
            @foreach($kontrak['sub_cpmks'] as $sub)
            <tr>
                <td style="font-weight:bold;white-space:nowrap">{{ $sub->kode }}</td>
                <td>{{ $sub->deskripsi }}</td>
            </tr>
            @endforeach

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>MATERI PEMBELAJARAN</strong></td>
            </tr>
            <tr>
                <td colspan="2">{{ $kontrak['bahan_kajian'] ?: '–' }}</td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>PUSTAKA UTAMA</strong></td>
            </tr>
            <tr>
                <td colspan="2">@forelse($referensiU as $i => $ref){{ $i+1 }}. {{ $ref->citation }}<br>@empty<em style="color:#999">–</em>@endforelse</td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>PUSTAKA PENDUKUNG</strong></td>
            </tr>
            <tr>
                <td colspan="2">@forelse($referensiP as $i => $ref){{ $i+1 }}. {{ $ref->citation }}<br>@empty<em style="color:#999">–</em>@endforelse</td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>MATA KULIAH SYARAT</strong></td>
            </tr>
            <tr>
                <td colspan="2">{{ $h['prasyarat'] ?? '–' }}</td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>TAUTAN PEMBELAJARAN</strong></td>
            </tr>
            <tr>
                <td colspan="2">{{ $detail?->tautan_kelas_daring ?: '–' }}</td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>DOSEN PENGAMPU</strong></td>
            </tr>
            <tr>
                <td colspan="2">
                    <table class="blueprint-inner" style="max-width:400px">
                        <tr>
                            <th style="width:30px;text-align:center">No</th>
                            <th>Nama Dosen</th>
                            <th style="width:60px;text-align:center">Inisial</th>
                        </tr>
                        @foreach($dosenList as $i => $d)
                        @php preg_match('/\(([A-Z]{1,3})\)\s*$/', $d, $m); $inisial = $m[1] ?? ''; $namaD = preg_replace('/\s*\([A-Z]{1,3}\)\s*$/', '', $d); @endphp
                        <tr>
                            <td style="text-align:center">{{ $i+1 }}</td>
                            <td>{{ $namaD }}</td>
                            <td style="text-align:center">{{ $inisial }}</td>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>KETENTUAN TAMBAHAN</strong></td>
            </tr>
            <tr>
                <td colspan="2">
                    @php $ketentuan = $detail?->ketentuan_tambahan ?? $kontrak['tata_tertib']; @endphp
                    @foreach($ketentuan as $i => $k){{ $i+1 }}. {{ $k }}<br>@endforeach
                </td>
            </tr>

            <tr style="background:#d9d9d9">
                <td colspan="2"><strong>JADWAL PEMBELAJARAN</strong></td>
            </tr>
            <tr>
                <td colspan="2">
                    @php $jadwalKuliah = $detail?->jadwal_kuliah ?? []; @endphp
                    @if(count($jadwalKuliah))
                    <table class="blueprint-inner">
                        <tr>
                            <th>Hari / Tanggal</th>
                            <th>Waktu WITA</th>
                            <th>Dosen Pengampu</th>
                        </tr>
                        @foreach($jadwalKuliah as $j)
                        <tr>
                            <td>{{ $j['hari'] ?? '' }}</td>
                            <td>{{ $j['waktu'] ?? '' }}</td>
                            <td>{{ $j['dosen'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </table>
                    @else
                    <em style="color:#999">Belum diisi</em>
                    @endif
                </td>
            </tr>
        </table>

        <br>
        <table>
            <tr>
                <td style="text-align:center;width:33%">
                    <strong>Dosen Pengembang RPS,</strong>
                    <div style="height:70px"></div>
                    <div>({{ $dosenList[0] ?? '..........................' }})</div>
                    <div>NIK.</div>
                </td>
                <td style="text-align:center;width:33%">
                    <div style="font-size:9.5pt">Banjarmasin, {{ now()->translatedFormat('d F Y') }}</div>
                    <br><strong>Mahasiswa PJMK MK {{ $h['kode_mk'] }}</strong>
                    <div style="height:70px"></div>
                    <div>(..................................)</div>
                    <div>NIM.</div>
                </td>
                <td style="text-align:center;width:33%">
                    <div style="font-size:9.5pt">Mengetahui,</div>
                    <br><strong>Ketua Program Studi {{ $h['prodi'] }}</strong>
                    <div style="height:70px"></div>
                    <div>({{ $h['kaprodi'] }})</div>
                    <div>NIK. {{ $h['nik_kaprodi'] ?? '' }}</div>
                </td>
            </tr>
        </table>

        {{-- ══ RENCANA TUGAS MAHASISWA ══ --}}
        <div style="page-break-before:always;margin-top:24px">
            <p class="section-page-title">RENCANA TUGAS MAHASISWA</p>
            <table>
                <tr>
                    <td width="80" style="text-align:center;vertical-align:middle;border:none">
                        <img src="{{ asset('images/logo_unism.png') }}" alt="Logo UNISM" style="height:70px;width:auto">
                    </td>
                    <td style="text-align:center;vertical-align:middle">
                        <div style="font-size:13pt;font-weight:bold">UNIVERSITAS SARI MULIA</div>
                        <div style="font-size:10pt;font-weight:bold">FAKULTAS {{ strtoupper($h['fakultas']) }}</div>
                        <div style="font-size:10pt;font-weight:bold">PROGRAM STUDI {{ strtoupper($h['prodi']) }}</div>
                    </td>
                    <td width="130" style="text-align:center;vertical-align:middle">
                        <div style="font-size:10pt;font-weight:bold;line-height:1.4">RENCANA<br>TUGAS<br>MAHASISWA</div>
                    </td>
                </tr>
            </table>
            <table style="margin-top:-1px">
                <tr>
                    <th style="background:#d9d9d9;text-align:center">Mata Kuliah</th>
                    <th style="background:#d9d9d9;text-align:center">Kode</th>
                    <th style="background:#d9d9d9;text-align:center">SKS</th>
                    <th style="background:#d9d9d9;text-align:center">Semester</th>
                    <th style="background:#d9d9d9;text-align:center">Dosen Pengampu</th>
                </tr>
                <tr>
                    <td>{{ $h['nama_mk'] }}</td>
                    <td style="text-align:center">{{ $h['kode_mk'] }}</td>
                    <td style="text-align:center">{{ $h['sks'] }} SKS</td>
                    <td style="text-align:center">{{ $h['semester'] }}</td>
                    <td>{{ implode(', ', $dosenList) }}</td>
                </tr>
            </table>

            @forelse($rencanaTugas as $tugas)
            <table style="margin-top:-1px">
                <tr style="background:#d9d9d9">
                    <td colspan="2"><strong>TUGAS {{ $tugas['no'] }}: {{ strtoupper($tugas['judul']) }}</strong></td>
                </tr>
                <tr>
                    <td width="200" class="cell-label">Bentuk Tugas</td>
                    <td>{{ $tugas['jenis'] }}</td>
                </tr>
                <tr>
                    <td class="cell-label">Judul Tugas</td>
                    <td>{{ $tugas['judul'] }}</td>
                </tr>
                <tr>
                    <td class="cell-label">Sub Capaian Pembelajaran MK</td>
                    <td>{{ $tugas['sub_kode'] }}: {{ $tugas['sub_cpmk']->deskripsi }}</td>
                </tr>
                <tr>
                    <td class="cell-label">Deskripsi Tugas</td>
                    <td>{{ $tugas['deskripsi'] }}</td>
                </tr>
                <tr>
                    <td class="cell-label">Metode Pengerjaan</td>
                    <td>{{ $tugas['metode'] }}</td>
                </tr>
                <tr>
                    <td class="cell-label">Bentuk dan Format Luaran</td>
                    <td>Laporan tertulis / presentasi sesuai ketentuan dosen</td>
                </tr>
                <tr>
                    <td class="cell-label">Indikator dan Bobot Penilaian</td>
                    <td>{{ $tugas['kriteria'] }} Bobot: <strong>{{ $tugas['bobot'] }}</strong></td>
                </tr>
                <tr>
                    <td class="cell-label">Jadwal Pelaksanaan</td>
                    <td>{{ $tugas['waktu'] }}</td>
                </tr>
                <tr>
                    <td class="cell-label">Daftar Rujukan</td>
                    <td>@foreach($referensiU->take(3) as $i => $ref){{ $i+1 }}. {{ $ref->citation }}<br>@endforeach</td>
                </tr>
            </table>
            @empty
            <div style="padding:10px;font-style:italic;color:#999;border:1px solid #ddd;text-align:center;font-size:10pt">
                Belum ada Sub CPMK.
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── DAFTAR MAHASISWA (dari KRS) ── --}}
@if(isset($mahasiswaList) && $mahasiswaList->count())
<div style="margin-top:24px;padding:0 20px">
    <div style="border:1px solid #d1d5db;border-radius:10px;overflow:hidden">
        <div style="background:#1e40af;color:#fff;padding:10px 16px;font-size:13px;font-weight:700">
            📋 Daftar Mahasiswa Peserta (KRS Aktif — TA {{ $ta }})
            <span style="font-size:11px;font-weight:normal;margin-left:8px;opacity:.8">
                {{ $mahasiswaList->count() }} mahasiswa
            </span>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:#eff6ff;font-size:11px;color:#374151">
                    <th style="padding:7px 10px;text-align:center;border:1px solid #e5e7eb;width:40px">No</th>
                    <th style="padding:7px 10px;text-align:left;border:1px solid #e5e7eb;width:130px">NIM</th>
                    <th style="padding:7px 10px;text-align:left;border:1px solid #e5e7eb">Nama Mahasiswa</th>
                    <th style="padding:7px 10px;text-align:center;border:1px solid #e5e7eb;width:70px">Angkatan</th>
                    <th style="padding:7px 10px;text-align:center;border:1px solid #e5e7eb;width:60px">Kelas</th>
                    <th style="padding:7px 10px;text-align:center;border:1px solid #e5e7eb;width:80px">Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mahasiswaList as $i => $krs)
                <tr style="{{ $loop->even ? 'background:#f9fafb' : 'background:#fff' }}">
                    <td style="padding:6px 10px;text-align:center;border:1px solid #e5e7eb;color:#6b7280">{{ $i + 1 }}</td>
                    <td style="padding:6px 10px;border:1px solid #e5e7eb;font-family:monospace;color:#1e40af;font-weight:600">{{ $krs->mahasiswa?->nim }}</td>
                    <td style="padding:6px 10px;border:1px solid #e5e7eb;font-weight:500">{{ $krs->mahasiswa?->nama }}</td>
                    <td style="padding:6px 10px;text-align:center;border:1px solid #e5e7eb;color:#6b7280">{{ $krs->mahasiswa?->angkatan }}</td>
                    <td style="padding:6px 10px;text-align:center;border:1px solid #e5e7eb;font-weight:bold">{{ $krs->kelas }}</td>
                    <td style="padding:6px 10px;border:1px solid #e5e7eb"></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection