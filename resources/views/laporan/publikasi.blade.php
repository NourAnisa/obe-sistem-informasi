@extends('layouts.dashboard')
@section('title', 'Laporan Publikasi Dosen')

@section('content')
<div style="max-width:1100px;margin:0 auto">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#1d4ed8,#4f46e5);border-radius:14px;padding:20px 24px;margin-bottom:20px;color:#fff">
        <h1 style="font-size:20px;font-weight:800;margin:0 0 4px">📊 Laporan Publikasi Dosen</h1>
        <p style="margin:0;font-size:14px;color:#bfdbfe">Program Studi Sistem Informasi — UNISM Banjarmasin</p>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:13px">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filter form --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px">
        <form method="GET" action="{{ route('laporan.publikasi') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
            <div style="flex:1;min-width:140px">
                <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:5px">Tahun</label>
                <select name="tahun" style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px">
                    <option value="">— Semua Tahun —</option>
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ request('tahun')==$t?'selected':'' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:2;min-width:180px">
                <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:5px">Dosen</label>
                <select name="dosen_id" style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px">
                    <option value="">— Semua Dosen —</option>
                    @foreach($dosenList as $d)
                    <option value="{{ $d->id }}" {{ request('dosen_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1;min-width:140px">
                <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:5px">Tipe</label>
                <select name="tipe" style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px">
                    <option value="">— Semua Tipe —</option>
                    <option value="penelitian" {{ request('tipe')=='penelitian'?'selected':'' }}>Penelitian</option>
                    <option value="pengabdian" {{ request('tipe')=='pengabdian'?'selected':'' }}>Pengabdian</option>
                </select>
            </div>
            <button type="submit"
                style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer">
                🔍 Filter
            </button>
            <a href="{{ route('laporan.publikasi') }}"
                style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:8px;padding:9px 14px;font-size:13px;font-weight:600;text-decoration:none">
                ✖ Reset
            </a>
        </form>
    </div>

    {{-- Export buttons --}}
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
        <a href="{{ route('laporan.publikasi.export-excel', request()->all()) }}"
            style="background:#16a34a;color:#fff;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
            📥 Export Excel
        </a>
        <a href="{{ route('laporan.publikasi.export-pdf', request()->all()) }}"
            style="background:#dc2626;color:#fff;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
            📄 Export PDF
        </a>
        <span style="font-size:13px;color:#6b7280;line-height:38px">
            Total: <strong>{{ $publikasiList->count() }}</strong> publikasi
        </span>
    </div>

    {{-- Table --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:20px">
        @if($publikasiList->isEmpty())
        <p style="color:#9ca3af;font-size:13px;text-align:center;padding:32px 0">Belum ada data publikasi.</p>
        @else
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">No</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Nama Dosen</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Tahun</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Judul</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Authors</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Tipe</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Jenis</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Sumber</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Bukti</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($publikasiList as $i => $pub)
                    <tr style="border-bottom:1px solid #f0f0f0;{{ $loop->even ? 'background:#fafafa;' : '' }}">
                        <td style="padding:8px 12px;color:#6b7280">{{ $i + 1 }}</td>
                        <td style="padding:8px 12px;white-space:nowrap;font-weight:600">{{ $pub->dosen?->name ?? '-' }}</td>
                        <td style="padding:8px 12px;white-space:nowrap">{{ $pub->tahun ?? '-' }}</td>
                        <td style="padding:8px 12px">
                            <div style="font-weight:500;color:#111">{{ Str::limit($pub->judul, 70) }}</div>
                            @if($pub->sumber)
                            <div style="font-size:11px;color:#6b7280">{{ Str::limit($pub->sumber, 40) }}</div>
                            @endif
                        </td>
                        <td style="padding:8px 12px;font-size:12px;color:#4b5563;max-width:140px">{{ Str::limit($pub->authors ?? '-', 40) }}</td>
                        <td style="padding:8px 12px">
                            <span style="background:{{ $pub->tipe==='penelitian' ? '#dbeafe' : '#dcfce7' }};color:{{ $pub->tipe==='penelitian' ? '#1e3a8a' : '#14532d' }};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700">
                                {{ ucfirst($pub->tipe) }}
                            </span>
                        </td>
                        <td style="padding:8px 12px;font-size:12px">{{ $pub->jenis ? ucfirst($pub->jenis) : '-' }}</td>
                        <td style="padding:8px 12px;font-size:12px;color:#6b7280">{{ Str::limit($pub->sumber ?? '-', 30) }}</td>
                        <td style="padding:8px 12px;text-align:center">
                            @if($pub->dokumen_bukti)
                            <a href="{{ asset('storage/' . $pub->dokumen_bukti) }}" target="_blank"
                                title="Lihat dokumen bukti"
                                style="color:#1d4ed8;font-size:16px;text-decoration:none">📎</a>
                            @else
                            <span style="color:#d1d5db;font-size:12px">—</span>
                            @endif
                        </td>
                        <td style="padding:8px 12px">
                            <button type="button"
                                onclick="openEditModal({{ $pub->id }}, '{{ addslashes($pub->judul) }}', '{{ $pub->tipe }}', '{{ $pub->jenis ?? '' }}', '{{ route('dosen.publikasi.update', $pub->id) }}')"
                                style="background:#eff6ff;color:#1d4ed8;border:1.5px solid #93c5fd;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;font-weight:600;white-space:nowrap">
                                ✏️ Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

{{-- Edit Modal --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:14px;padding:28px;max-width:480px;width:92%;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative">
        <button onclick="closeEditModal()" style="position:absolute;top:12px;right:14px;font-size:22px;background:none;border:none;cursor:pointer;color:#9ca3af">×</button>
        <h3 style="font-size:16px;font-weight:700;margin:0 0 4px;color:#111">✏️ Edit Publikasi</h3>
        <p id="modalJudul" style="font-size:12px;color:#6b7280;margin:0 0 20px;line-height:1.4"></p>

        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px">Tipe Publikasi</label>
                <div style="display:flex;gap:12px">
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px">
                        <input type="radio" name="tipe" value="penelitian" id="tipe_penelitian">
                        <span style="background:#dbeafe;color:#1e3a8a;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:700">🔬 Penelitian</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px">
                        <input type="radio" name="tipe" value="pengabdian" id="tipe_pengabdian">
                        <span style="background:#dcfce7;color:#14532d;padding:3px 10px;border-radius:10px;font-size:12px;font-weight:700">🤝 Pengabdian Masyarakat</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px">Jenis Publikasi</label>
                <select name="jenis" style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px">
                    <option value="">— Pilih Jenis —</option>
                    <option value="jurnal">Jurnal</option>
                    <option value="prosiding">Prosiding</option>
                    <option value="buku">Buku</option>
                </select>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:6px">
                    📎 Dokumen Bukti
                    <span style="font-weight:400;color:#9ca3af">(PDF/DOC/Gambar, maks 5MB)</span>
                </label>
                <input type="file" name="dokumen_bukti" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px;background:#fafafa">
                <p id="existingDoc" style="font-size:11px;color:#16a34a;margin:4px 0 0;display:none">
                    📎 Sudah ada dokumen — unggah file baru untuk mengganti
                </p>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" onclick="closeEditModal()"
                    style="background:#f3f4f6;color:#374151;border:1.5px solid #d1d5db;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer">
                    Batal
                </button>
                <button type="submit"
                    style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:600;cursor:pointer">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, judul, tipe, jenis, actionUrl) {
        document.getElementById('modalJudul').textContent = judul;
        document.getElementById('editForm').action = actionUrl;
        document.querySelector(`input[name="tipe"][value="${tipe}"]`).checked = true;
        const sel = document.querySelector('select[name="jenis"]');
        sel.value = jenis || '';
        document.getElementById('existingDoc').style.display = 'none'; // reset
        document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    // Close on backdrop click
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>
@endsection

{{-- Filter form --}}
<div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px">
    <form method="GET" action="{{ route('laporan.publikasi') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
        <div style="flex:1;min-width:140px">
            <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:5px">Tahun</label>
            <select name="tahun" style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px">
                <option value="">— Semua Tahun —</option>
                @foreach($tahunList as $t)
                <option value="{{ $t }}" {{ request('tahun')==$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:2;min-width:180px">
            <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:5px">Dosen</label>
            <select name="dosen_id" style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px">
                <option value="">— Semua Dosen —</option>
                @foreach($dosenList as $d)
                <option value="{{ $d->id }}" {{ request('dosen_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1;min-width:140px">
            <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:5px">Tipe</label>
            <select name="tipe" style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 10px;font-size:13px">
                <option value="">— Semua Tipe —</option>
                <option value="penelitian" {{ request('tipe')=='penelitian'?'selected':'' }}>Penelitian</option>
                <option value="pengabdian" {{ request('tipe')=='pengabdian'?'selected':'' }}>Pengabdian</option>
            </select>
        </div>
        <button type="submit"
            style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer">
            🔍 Filter
        </button>
        <a href="{{ route('laporan.publikasi') }}"
            style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:8px;padding:9px 14px;font-size:13px;font-weight:600;text-decoration:none">
            ✖ Reset
        </a>
    </form>
</div>

{{-- Export buttons --}}
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
    <a href="{{ route('laporan.publikasi.export-excel', request()->all()) }}"
        style="background:#16a34a;color:#fff;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
        📥 Export Excel
    </a>
    <a href="{{ route('laporan.publikasi.export-pdf', request()->all()) }}"
        style="background:#dc2626;color:#fff;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
        📄 Export PDF
    </a>
    <span style="font-size:13px;color:#6b7280;line-height:38px">
        Total: <strong>{{ $publikasiList->count() }}</strong> publikasi
    </span>
</div>

{{-- Table --}}
<div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:20px">
    @if($publikasiList->isEmpty())
    <p style="color:#9ca3af;font-size:13px;text-align:center;padding:32px 0">Belum ada data publikasi.</p>
    @else
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">No</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Nama Dosen</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Tahun</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Judul</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Authors</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Tipe</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Jenis</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Sumber</th>
                    <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Link</th>
                </tr>
            </thead>
            <tbody>
                @foreach($publikasiList as $i => $pub)
                <tr style="border-bottom:1px solid #f0f0f0;{{ $loop->even ? 'background:#fafafa;' : '' }}">
                    <td style="padding:8px 12px;color:#6b7280">{{ $i + 1 }}</td>
                    <td style="padding:8px 12px;white-space:nowrap;font-weight:600">{{ $pub->dosen?->name ?? '-' }}</td>
                    <td style="padding:8px 12px;white-space:nowrap">{{ $pub->tahun ?? '-' }}</td>
                    <td style="padding:8px 12px">
                        <div style="font-weight:500;color:#111">{{ Str::limit($pub->judul, 70) }}</div>
                    </td>
                    <td style="padding:8px 12px;font-size:12px;color:#4b5563;max-width:140px">{{ Str::limit($pub->authors ?? '-', 40) }}</td>
                    <td style="padding:8px 12px">
                        <span style="background:{{ $pub->tipe==='penelitian' ? '#dbeafe' : '#dcfce7' }};color:{{ $pub->tipe==='penelitian' ? '#1e3a8a' : '#14532d' }};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700">
                            {{ ucfirst($pub->tipe) }}
                        </span>
                    </td>
                    <td style="padding:8px 12px;font-size:12px">{{ $pub->jenis ? ucfirst($pub->jenis) : '-' }}</td>
                    <td style="padding:8px 12px;font-size:12px;color:#6b7280">{{ Str::limit($pub->sumber ?? '-', 30) }}</td>
                    <td style="padding:8px 12px">
                        @if($pub->link)
                        <a href="{{ $pub->link }}" target="_blank" style="color:#1d4ed8;font-size:12px;text-decoration:none">🔗</a>
                        @else
                        <span style="color:#9ca3af;font-size:12px">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

</div>
@endsection