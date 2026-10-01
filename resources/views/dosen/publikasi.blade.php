@extends('layouts.dashboard')
@section('title', 'Publikasi – ' . $dosen->name)

@section('content')
<div style="max-width:960px;margin:0 auto">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#1d4ed8,#4f46e5);border-radius:14px;padding:20px 24px;margin-bottom:20px;color:#fff">
        <h1 style="font-size:20px;font-weight:800;margin:0 0 4px">📚 Publikasi Dosen</h1>
        <p style="margin:0;font-size:14px;color:#bfdbfe">{{ $dosen->name }} &nbsp;·&nbsp; {{ $dosen->jabatan ?? '-' }}</p>
        @if($dosen->last_sync_at)
        <p style="margin:4px 0 0;font-size:12px;color:#bfdbfe">Terakhir sync: {{ \Carbon\Carbon::parse($dosen->last_sync_at)->format('d M Y H:i') }}</p>
        @endif
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#166534;font-size:13px">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#991b1b;font-size:13px">
        {{ session('error') }}
    </div>
    @endif

    {{-- Google Scholar ID form --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px">
        <h3 style="font-size:14px;font-weight:700;color:#374151;margin:0 0 14px">🔗 Google Scholar ID</h3>
        <form method="POST" action="{{ route('dosen.publikasi.update-scholar-id', $dosen->id) }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
            @csrf @method('PATCH')
            <div style="flex:1;min-width:200px">
                <label style="display:block;font-size:12px;font-weight:600;color:#4b5563;margin-bottom:5px">Scholar ID</label>
                <input name="google_scholar_id" value="{{ $dosen->google_scholar_id }}"
                    placeholder="Contoh: XXXXXXXXXX"
                    style="width:100%;border:1.5px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px">
                <p style="font-size:11px;color:#9ca3af;margin:4px 0 0">Lihat dari URL profil Google Scholar Anda</p>
            </div>
            <button type="submit"
                style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer">
                💾 Simpan
            </button>
        </form>

        {{-- Sync button --}}
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f0f0f0">
            @if($dosen->google_scholar_id)
            <form method="POST" action="{{ route('dosen.publikasi.sync', $dosen->id) }}" style="display:inline">
                @csrf
                <button type="submit"
                    style="background:#059669;color:#fff;border:none;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px">
                    🔄 Sync Google Scholar
                </button>
            </form>
            @else
            <button disabled
                style="background:#d1d5db;color:#6b7280;border:none;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;cursor:not-allowed">
                🔄 Sync Google Scholar (isi Scholar ID dulu)
            </button>
            @endif
            <span style="font-size:12px;color:#6b7280;margin-left:10px">
                Total publikasi tersimpan: <strong>{{ $publikasi->count() }}</strong>
            </span>
        </div>
    </div>

    {{-- Publikasi table --}}
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:20px">
        <h3 style="font-size:14px;font-weight:700;color:#374151;margin:0 0 14px">📄 Daftar Publikasi</h3>

        @if($publikasi->isEmpty())
        <p style="color:#9ca3af;font-size:13px;text-align:center;padding:24px 0">
            Belum ada data publikasi. Klik "Sync Google Scholar" untuk mengambil data otomatis.
        </p>
        @else
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Tahun</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Judul</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Authors</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Tipe</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Jenis</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb">Link</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Bukti</th>
                        <th style="padding:8px 12px;text-align:left;border-bottom:2px solid #e5e7eb;white-space:nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($publikasi as $pub)
                    <tr style="border-bottom:1px solid #f0f0f0;{{ $loop->even ? 'background:#fafafa;' : '' }}">
                        <td style="padding:8px 12px;white-space:nowrap">{{ $pub->tahun ?? '-' }}</td>
                        <td style="padding:8px 12px">
                            <div style="font-weight:600;color:#111">{{ Str::limit($pub->judul, 80) }}</div>
                            @if($pub->sumber)
                            <div style="font-size:11px;color:#6b7280">{{ $pub->sumber }}</div>
                            @endif
                        </td>
                        <td style="padding:8px 12px;font-size:12px;color:#4b5563;max-width:160px">{{ Str::limit($pub->authors ?? '-', 50) }}</td>
                        <td style="padding:8px 12px">
                            <span style="background:{{ $pub->tipe==='penelitian' ? '#dbeafe' : '#dcfce7' }};color:{{ $pub->tipe==='penelitian' ? '#1e3a8a' : '#14532d' }};padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700">
                                {{ ucfirst($pub->tipe) }}
                            </span>
                        </td>
                        <td style="padding:8px 12px;font-size:12px">{{ $pub->jenis ? ucfirst($pub->jenis) : '-' }}</td>
                        <td style="padding:8px 12px">
                            @if($pub->link)
                            <a href="{{ $pub->link }}" target="_blank"
                                style="color:#1d4ed8;font-size:12px;text-decoration:none">🔗 Lihat</a>
                            @else
                            <span style="color:#9ca3af;font-size:12px">-</span>
                            @endif
                        </td>
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
                            <div style="display:flex;gap:5px;flex-wrap:nowrap">
                            <button type="button"
                                onclick="openEditModal({{ $pub->id }}, '{{ addslashes($pub->judul) }}', '{{ $pub->tipe }}', '{{ $pub->jenis ?? '' }}', {{ $pub->dokumen_bukti ? 'true' : 'false' }}, '{{ route('dosen.publikasi.update', $pub->id) }}')"
                                style="background:#eff6ff;color:#1d4ed8;border:1.5px solid #93c5fd;border-radius:6px;padding:4px 8px;font-size:12px;cursor:pointer;font-weight:600">
                                ✏️
                            </button>
                            <form method="POST" action="{{ route('dosen.publikasi.destroy', $pub->id) }}"
                                onsubmit="return confirm('Hapus publikasi ini?')" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer">
                                    🗑️
                                </button>
                            </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Back link --}}
    <div style="margin-top:16px">
        <a href="{{ route('users.index') }}" style="color:#1d4ed8;font-size:13px;text-decoration:none">← Kembali ke Daftar Pengguna</a>
        &nbsp;|&nbsp;
        <a href="{{ route('laporan.publikasi') }}" style="color:#1d4ed8;font-size:13px;text-decoration:none">📊 Laporan Publikasi</a>
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
function openEditModal(id, judul, tipe, jenis, hasDok, actionUrl) {
    document.getElementById('modalJudul').textContent = judul;
    document.getElementById('editForm').action = actionUrl;
    document.querySelector(`input[name="tipe"][value="${tipe}"]`).checked = true;
    document.querySelector('select[name="jenis"]').value = jenis || '';
    document.getElementById('existingDoc').style.display = hasDok ? 'block' : 'none';
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>
@endsection