@extends('layouts.dashboard')
@section('title', 'Manajemen Mahasiswa')

@section('content')
<div style="padding:24px">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <div>
            <h1 style="font-size:22px;font-weight:700;color:#1e3a5f;margin:0">👨‍🎓 Manajemen Mahasiswa</h1>
            <p style="color:#64748b;margin:4px 0 0;font-size:13px">Kelola akun mahasiswa dan pendaftaran mata kuliah</p>
        </div>
        <button onclick="document.getElementById('modalTambah').style.display='flex'"
            style="background:#1d4ed8;color:#fff;padding:10px 18px;border-radius:8px;border:none;cursor:pointer;font-weight:600;font-size:13px">
            ➕ Tambah Mahasiswa
        </button>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px">❌ {{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:8px;margin-bottom:16px">
        <strong>❌ Gagal menyimpan:</strong>
        <ul style="margin:6px 0 0 16px;padding:0">
            @foreach($errors->all() as $err)
            <li style="font-size:13px">{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('mahasiswa.index') }}" style="margin-bottom:16px;display:flex;gap:8px;align-items:center">
        <label for="q" class="sr-only">Cari NIM atau Nama</label>
        <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Cari NIM atau Nama..."
            style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;flex:1;max-width:320px">
        <button type="submit" style="padding:8px 16px;background:#1e3a5f;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer">🔍 Cari</button>
        @if(request('q'))
        <a href="{{ route('mahasiswa.index') }}" style="padding:8px 14px;background:#f1f5f9;color:#64748b;border-radius:8px;font-size:13px;text-decoration:none">✕ Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.1);overflow:hidden">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#1e3a5f;color:#fff">
                    <th style="padding:12px 14px;text-align:left">NIM</th>
                    <th style="padding:12px 14px;text-align:left">Nama</th>
                    <th style="padding:12px 14px;text-align:left">Program Studi</th>
                    <th style="padding:12px 14px;text-align:left">Email Login</th>
                    <th style="padding:12px 14px;text-align:center">Angkatan</th>
                    <th style="padding:12px 14px;text-align:left">Dosen PA</th>
                    <th style="padding:12px 14px;text-align:center">IPK / IPS</th>
                    <th style="padding:12px 14px;text-align:left">Mata Kuliah (Semester)</th>
                    <th style="padding:12px 14px;text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mahasiswas as $mhs)
                <tr style="border-bottom:1px solid #f1f5f9;{{ $loop->even ? 'background:#f8fafc' : '' }}">
                    <td style="padding:10px 14px;font-weight:600;color:#1e3a5f">{{ $mhs->nim }}</td>
                    <td style="padding:10px 14px">{{ $mhs->nama }}</td>
                    <td style="padding:10px 14px">
                        @if($mhs->program)
                        <div style="font-size:12px">
                            <div style="font-weight:600;color:#374151">{{ $mhs->program->nama }}</div>
                            <div style="color:#94a3b8;font-size:11px">{{ $mhs->program->jenjang }}</div>
                        </div>
                        @else
                        <span style="color:#d1d5db;font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="padding:10px 14px;color:#64748b;font-size:12px">{{ $mhs->user?->email ?? '—' }}</td>
                    <td style="padding:10px 14px;text-align:center">{{ $mhs->angkatan ?? '—' }}</td>
                    <td style="padding:10px 14px">
                        <div style="display:flex;align-items:center;gap:6px">
                            <span style="font-size:12px;color:{{ $mhs->dosenPa ? '#1d4ed8' : '#94a3b8' }}">
                                {{ $mhs->dosenPa?->name ?? '—' }}
                            </span>
                            <button onclick="showPaModal({{ $mhs->id }}, {{ json_encode($mhs->nama) }}, {{ $mhs->dosen_pa_id ?? 'null' }})"
                                style="background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;padding:2px 7px;border-radius:6px;font-size:10px;cursor:pointer">
                                ✏️
                            </button>
                        </div>
                    </td>
                    <td style="padding:10px 14px;text-align:center">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                            <span style="font-size:12px;color:{{ ($mhs->ipk ?? 0) >= 3.0 ? '#16a34a' : '#d97706' }};font-weight:600">
                                IPK: {{ number_format($mhs->ipk ?? 0, 2) }}
                            </span>
                            <span style="font-size:12px;color:{{ ($mhs->ips ?? 0) >= 3.0 ? '#16a34a' : '#d97706' }};font-weight:600">
                                IPS: {{ number_format($mhs->ips ?? 0, 2) }}
                            </span>
                            <button onclick="showIpkModal({{ $mhs->id }}, {{ json_encode($mhs->nama) }}, '{{ $mhs->ipk ?? 0 }}', '{{ $mhs->ips ?? 0 }}')"
                                style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;padding:2px 7px;border-radius:6px;font-size:10px;cursor:pointer;margin-top:2px">
                                ✏️ Edit
                            </button>
                        </div>
                    </td>
                    <td style="padding:10px 14px">
                        @foreach($mhs->enrollments as $enr)
                        <span style="display:inline-flex;align-items:center;gap:4px;background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:12px;font-size:11px;margin:2px">
                            {{ $enr->mataKuliah?->kode ?? '?' }}
                            @if($enr->is_pjmk)<span style="background:#fef3c7;color:#92400e;padding:1px 5px;border-radius:8px;font-size:10px">PJMK</span>@endif
                            <form method="POST" action="{{ route('mahasiswa.unenroll', $enr->id) }}" style="display:inline" onsubmit="return confirm('Hapus dari MK ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:11px;padding:0">✕</button>
                            </form>
                            @if(!$enr->is_pjmk)
                            <form method="POST" action="{{ route('mahasiswa.toggle-pjmk', $enr->id) }}" style="display:inline">
                                @csrf
                                <button type="submit" style="background:none;border:none;color:#92400e;cursor:pointer;font-size:10px;padding:0" title="Set sebagai PJMK">★</button>
                            </form>
                            @endif
                        </span>
                        @endforeach
                        <button onclick="showEnrollModal({{ $mhs->id }}, {{ json_encode($mhs->nama) }})"
                            style="background:#f0fdf4;color:#15803d;border:1px solid #86efac;padding:2px 8px;border-radius:8px;font-size:11px;cursor:pointer;margin:2px">
                            + Daftar MK
                        </button>
                    </td>
                    <td style="padding:10px 14px;text-align:center">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                            <a href="{{ route('obe.student-evaluasi', ['nim' => $mhs->nim, 'angkatan' => $mhs->angkatan]) }}"
                                style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;padding:4px 10px;border-radius:6px;font-size:11px;text-decoration:none;font-weight:600"
                                title="Lihat capaian CPL mahasiswa ini">
                                🎯 CPL
                            </a>
                            <form method="POST" action="{{ route('mahasiswa.destroy', $mhs->id) }}" onsubmit="return confirm({{ json_encode('Hapus mahasiswa ' . $mhs->nama . '?') }})">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:#fee2e2;color:#dc2626;border:none;padding:4px 10px;border-radius:6px;cursor:pointer;font-size:11px">🗑 Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="padding:40px;text-align:center;color:#94a3b8">Belum ada data mahasiswa</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($mahasiswas->hasPages())
        <div style="padding:16px 20px">{{ $mahasiswas->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Tambah Mahasiswa --}}
<div id="modalTambah" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:480px;max-width:95vw">
        <h2 style="margin:0 0 20px;font-size:18px;font-weight:700;color:#1e3a5f">➕ Tambah Mahasiswa Baru</h2>
        <form method="POST" action="{{ route('mahasiswa.store') }}">
            @csrf
            <div style="display:grid;gap:14px">
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">NIM *</label>
                    <input type="text" name="nim" required placeholder="2310101001" value="{{ old('nim') }}" style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">Nama Lengkap *</label>
                    <input type="text" name="nama" required placeholder="Nama mahasiswa" value="{{ old('nama') }}" style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">Angkatan</label>
                    <input type="number" name="angkatan" placeholder="2023" min="2000" max="2030" value="{{ old('angkatan') }}" style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box">
                </div>
                @if(auth()->user()->role === 'admin')
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">Program Studi</label>
                    <select name="program_id" style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                        <option value="">— Inherit dari user login —</option>
                        @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ old('program_id') == $prog->id ? 'selected' : '' }}>
                            {{ $prog->nama }} ({{ $prog->jenjang }}) — {{ $prog->faculty->nama ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    <p style="font-size:11px;color:#94a3b8;margin-top:4px">Kosongkan untuk otomatis menggunakan prodi Anda.</p>
                </div>
                @else
                <input type="hidden" name="program_id" value="">
                @endif
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">Email Login *</label>
                    <input type="email" name="email" required placeholder="mahasiswa@unism.ac.id" value="{{ old('email') }}" style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">Password *</label>
                    <input type="password" name="password" required placeholder="Min. 6 karakter" style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box">
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modalTambah').style.display='none'"
                    style="padding:10px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer">Batal</button>
                <button type="submit" style="padding:10px 20px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Enroll MK --}}
<div id="modalEnroll" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:440px;max-width:95vw">
        <h2 style="margin:0 0 4px;font-size:18px;font-weight:700;color:#1e3a5f">📚 Daftar ke Mata Kuliah</h2>
        <p id="enrollNama" style="margin:0 0 20px;color:#64748b;font-size:13px"></p>
        <form id="formEnroll" method="POST">
            @csrf
            <div style="display:grid;gap:14px">
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">Mata Kuliah *</label>
                    <select name="mata_kuliah_id" required style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                        @foreach($mataKuliahs as $mk)
                        <option value="{{ $mk->id }}">{{ $mk->kode }} — {{ $mk->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">Semester Aktif *</label>
                    <input type="text" name="semester_aktif" value="{{ config('obe.semester_aktif', config('obe.tahun_akademik', '2025/2026')) }}" required style="width:100%;margin-top:4px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;box-sizing:border-box">
                </div>
                <div>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
                        <input type="checkbox" name="is_pjmk" value="1"> <span>Jadikan PJMK untuk MK ini</span>
                    </label>
                </div>
            </div>
            <div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modalEnroll').style.display='none'"
                    style="padding:10px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer">Batal</button>
                <button type="submit" style="padding:10px 20px;background:#15803d;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer">✅ Daftarkan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit IPK/IPS --}}
<div id="modalIpk" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:380px;max-width:95vw">
        <h2 style="margin:0 0 4px;font-size:18px;font-weight:700;color:#1e3a5f">📊 Update IPK & IPS</h2>
        <p id="ipkMhsNama" style="margin:0 0 20px;color:#64748b;font-size:13px"></p>
        <form id="formIpk" method="POST">
            @csrf @method('PATCH')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">IPK (0.00 – 4.00)</label>
                    <input id="ipkInput" type="number" name="ipk" min="0" max="4" step="0.01"
                        style="width:100%;margin-top:6px;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;box-sizing:border-box"
                        placeholder="3.50">
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#374151">IPS (0.00 – 4.00)</label>
                    <input id="ipsInput" type="number" name="ips" min="0" max="4" step="0.01"
                        style="width:100%;margin-top:6px;padding:10px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;box-sizing:border-box"
                        placeholder="3.60">
                </div>
            </div>
            <p style="font-size:11px;color:#94a3b8;margin:0 0 20px">IPK ≥ 3.00 & IPS ≥ 3.00 → kuota 24 SKS dan akses semester lebih tinggi.</p>
            <div style="display:flex;gap:12px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modalIpk').style.display='none'"
                    style="padding:10px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer">Batal</button>
                <button type="submit" style="padding:10px 20px;background:#7c3aed;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>
<div id="modalPa" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;width:420px;max-width:95vw">
        <h2 style="margin:0 0 4px;font-size:18px;font-weight:700;color:#1e3a5f">👨‍🏫 Tetapkan Dosen PA</h2>
        <p id="paMhsNama" style="margin:0 0 20px;color:#64748b;font-size:13px"></p>
        <form id="formPa" method="POST">
            @csrf
            <div>
                <label style="font-size:13px;font-weight:600;color:#374151">Dosen Pembimbing Akademik</label>
                <select id="paDosenSelect" name="dosen_pa_id" style="width:100%;margin-top:6px;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px">
                    <option value="">— Tidak Ada —</option>
                    @foreach($dosenList as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:12px;margin-top:24px;justify-content:flex-end">
                <button type="button" onclick="document.getElementById('modalPa').style.display='none'"
                    style="padding:10px 20px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer">Batal</button>
                <button type="submit" style="padding:10px 20px;background:#1d4ed8;color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showEnrollModal(id, nama) {
        document.getElementById('enrollNama').textContent = 'Mahasiswa: ' + nama;
        document.getElementById('formEnroll').action = '/mahasiswa/' + id + '/enroll';
        document.getElementById('modalEnroll').style.display = 'flex';
    }

    function showPaModal(id, nama, currentPaId) {
        document.getElementById('paMhsNama').textContent = 'Mahasiswa: ' + nama;
        document.getElementById('formPa').action = '/mahasiswa/' + id + '/assign-pa';
        var sel = document.getElementById('paDosenSelect');
        sel.value = currentPaId || '';
        document.getElementById('modalPa').style.display = 'flex';
    }

    function showIpkModal(id, nama, ipk, ips) {
        document.getElementById('ipkMhsNama').textContent = 'Mahasiswa: ' + nama;
        document.getElementById('formIpk').action = '/mahasiswa/' + id + '/ipk-ips';
        document.getElementById('ipkInput').value = parseFloat(ipk).toFixed(2);
        document.getElementById('ipsInput').value = parseFloat(ips).toFixed(2);
        document.getElementById('modalIpk').style.display = 'flex';
    }

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalTambah').style.display = 'flex';
    });
    @endif
</script>
@endsection