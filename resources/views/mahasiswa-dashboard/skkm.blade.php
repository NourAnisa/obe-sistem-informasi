@extends('layouts.dashboard')
@section('title', 'SKKM Saya')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">🏆 SKKM — Satuan Kredit Kegiatan Mahasiswa</h1>
            <p class="text-sm text-brand-300 mt-1">
                {{ $mahasiswa->nama }}
                — Total Poin: <strong class="text-emerald-400 text-base">{{ number_format($skkmTotal, 0) }} poin</strong>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('mahasiswa.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">
                ← Dashboard
            </a>
            <button onclick="document.getElementById('modalSkkm').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg font-semibold transition">
                ➕ Tambah Kegiatan
            </button>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/40 text-green-300 px-4 py-3 rounded-lg text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/20 border border-red-500/40 text-red-300 px-4 py-3 rounded-lg text-sm">❌ {{ session('error') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-500/20 border border-red-500/40 text-red-300 px-4 py-3 rounded-lg text-sm">
        <strong>❌ Error:</strong>
        <ul class="mt-1 ml-4 list-disc text-xs">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Stats --}}
    @php
    $byStatus = $skkmList->groupBy('status');
    $pending = $byStatus->get('pending', collect())->count();
    $approved = $byStatus->get('approved', collect())->count();
    $rejected = $byStatus->get('rejected', collect())->count();
    $totalApprovedPts = $byStatus->get('approved', collect())->sum('points_awarded');
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-blue-400">{{ $skkmList->count() }}</div>
            <div class="text-xs text-brand-400 mt-1">Total Kegiatan</div>
        </div>
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-amber-400">{{ $pending }}</div>
            <div class="text-xs text-brand-400 mt-1">Menunggu Verifikasi</div>
        </div>
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-green-400">{{ $approved }}</div>
            <div class="text-xs text-brand-400 mt-1">Disetujui</div>
        </div>
        <div class="bg-brand-800/60 border border-brand-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-extrabold text-emerald-300">{{ number_format($totalApprovedPts, 0) }}</div>
            <div class="text-xs text-brand-400 mt-1">Poin Terverifikasi</div>
        </div>
    </div>

    {{-- SKKM Table --}}
    <div class="bg-brand-800/60 rounded-xl border border-brand-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-700">
            <h2 class="text-base font-semibold text-white">📋 Daftar Kegiatan SKKM</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-brand-200">
                <thead class="bg-brand-700/60 text-brand-300 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Nama Kegiatan</th>
                        <th class="px-4 py-3 text-left">Jenis Kegiatan</th>
                        <th class="px-4 py-3 text-left">Peran / Tingkat</th>
                        <th class="px-4 py-3 text-center">Poin</th>
                        <th class="px-4 py-3 text-center">TA / Sem</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-700/40">
                    @forelse($skkmList as $i => $sk)
                    <tr class="hover:bg-brand-700/20 transition">
                        <td class="px-4 py-3 text-brand-400">{{ $i+1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $sk->nama_kegiatan }}</div>
                            @if($sk->lokasi)
                            <div class="text-xs text-brand-500">📍 {{ $sk->lokasi }}</div>
                            @endif
                            @if($sk->nomor_sk)
                            <div class="text-xs text-brand-500">SK: {{ $sk->nomor_sk }}</div>
                            @endif
                            @if($sk->google_drive_link)
                            <a href="{{ $sk->google_drive_link }}" target="_blank"
                                class="text-xs text-blue-400 hover:underline">🔗 Drive</a>
                            @endif
                            @if($sk->file_bukti)
                            <a href="{{ Storage::url($sk->file_bukti) }}" target="_blank"
                                class="text-xs text-green-400 hover:underline ml-1">📎 Bukti</a>
                            @endif
                            @if($sk->keterangan)
                            <div class="text-xs text-brand-500 mt-0.5 italic">{{ $sk->keterangan }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($sk->activity_name)
                            <div class="text-xs">
                                <span class="inline-block px-1.5 py-0.5 bg-brand-700 text-brand-300 rounded text-xs mb-0.5">
                                    Kat. {{ $sk->activity_category ?? '-' }}
                                </span>
                                <div class="text-brand-200">{{ $sk->activity_name }}</div>
                            </div>
                            @else
                            <span class="text-brand-400">{{ $sk->kategori ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($sk->role)
                            <div class="text-brand-200 font-medium">{{ $sk->role }}</div>
                            <div class="text-brand-400">{{ $sk->level ?? $sk->tingkat }}</div>
                            @else
                            <div>{{ $sk->prestasi ?? '-' }}</div>
                            <div class="text-brand-400">{{ $sk->tingkat ?? '-' }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-12 h-8 rounded-lg
                                bg-emerald-500/20 text-emerald-300 font-bold text-sm">
                                {{ number_format($sk->points_awarded ?? $sk->sks_ekuivalen, 0) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-brand-400">
                            {{ $sk->tahun_akademik ?? '-' }}<br>{{ $sk->semester ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                            $statusCfg = match($sk->status) {
                            'approved' => ['bg-green-500/20 text-green-300 border-green-500/30', '✅ Disetujui'],
                            'rejected' => ['bg-red-500/20 text-red-300 border-red-500/30', '❌ Ditolak'],
                            default => ['bg-amber-500/20 text-amber-300 border-amber-500/30', '⏳ Menunggu'],
                            };
                            @endphp
                            <span class="inline-block px-2 py-1 rounded text-xs border {{ $statusCfg[0] }}">
                                {{ $statusCfg[1] }}
                            </span>
                            @if($sk->catatan_kemahasiswaan ?? false)
                            <div class="text-xs text-red-400 mt-0.5">{{ $sk->catatan_kemahasiswaan }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($sk->status === 'pending')
                            <form method="POST" action="{{ route('mahasiswa.skkm.destroy', $sk->id) }}"
                                onsubmit="return confirm('Hapus kegiatan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 bg-red-500/20 hover:bg-red-500/40 text-red-300 text-xs rounded transition">
                                    Hapus
                                </button>
                            </form>
                            @else
                            <span class="text-brand-500 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-brand-400">
                            <div class="text-4xl mb-2">🏆</div>
                            <p>Belum ada kegiatan SKKM. Klik <strong>➕ Tambah Kegiatan</strong> untuk mulai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($skkmList->count() > 0)
                <tfoot class="bg-brand-700/30 border-t-2 border-brand-600">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-brand-300 font-semibold text-sm">Total Poin:</td>
                        <td class="px-4 py-3 text-center font-extrabold text-emerald-300 text-base">
                            {{ number_format($skkmTotal, 0) }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl px-5 py-3 text-xs text-blue-300">
        ℹ️ <strong>Info:</strong> Kegiatan yang sudah disetujui tidak dapat dihapus. Poin SKKM dihitung berdasarkan jenis kegiatan, peran, dan tingkat sesuai pedoman {{ config('obe.universitas_singkat', config('obe.universitas')) }}.
    </div>

</div>

{{-- Modal Tambah SKKM --}}
<div id="modalSkkm" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
    <div class="bg-brand-800 border border-brand-600 rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold text-white mb-5">➕ Tambah Kegiatan SKKM</h2>
        <form method="POST" action="{{ route('mahasiswa.skkm.store') }}" id="skkmForm" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">

                {{-- Nama Kegiatan --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-1">Nama Kegiatan / Prestasi *</label>
                    <input type="text" name="nama_kegiatan" required value="{{ old('nama_kegiatan') }}"
                        placeholder="Contoh: Juara 1 Hackathon Regional 2025"
                        class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                </div>

                {{-- Category Filter --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-2">Kategori Kegiatan *</label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="_category" value="A" class="accent-blue-500" checked onchange="filterTypes(this.value)">
                            <span class="text-sm text-brand-200">
                                <span class="font-semibold text-blue-400">A</span> — Akademik & Keilmuan
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="_category" value="B" class="accent-blue-500" onchange="filterTypes(this.value)">
                            <span class="text-sm text-brand-200">
                                <span class="font-semibold text-amber-400">B</span> — Kemahasiswaan & Minat Bakat
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Jenis Kegiatan (Activity Type) --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-1">Jenis Kegiatan *</label>
                    <select id="activityTypeSelect"
                        class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
                        onchange="loadRoles(this.value)">
                        <option value="">-- Pilih Jenis Kegiatan --</option>
                        @foreach($activityTypes as $at)
                        <option value="{{ $at->id }}" data-category="{{ $at->category }}">
                            {{ $at->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Peran/Role --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-1">Peran / Status *</label>
                    <select id="roleSelect"
                        class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
                        onchange="loadLevels()" disabled>
                        <option value="">-- Pilih jenis kegiatan dahulu --</option>
                    </select>
                </div>

                {{-- Tingkat/Level --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-1">Tingkat *</label>
                    <select id="levelSelect" name="point_rule_id"
                        class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500"
                        onchange="showPoints()" disabled required>
                        <option value="">-- Pilih peran dahulu --</option>
                    </select>
                </div>

                {{-- Poin Preview --}}
                <div id="pointPreview" class="hidden bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-center">
                    <div class="text-xs text-emerald-400 mb-1">Poin SKKM yang akan diperoleh</div>
                    <div class="text-3xl font-extrabold text-emerald-300" id="pointValue">0</div>
                    <div class="text-xs text-emerald-400 mt-0.5">poin</div>
                </div>

                {{-- TA & Semester --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-brand-300 mb-1">Tahun Akademik</label>
                        <select name="tahun_akademik"
                            class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            @foreach(['2024/2025', '2025/2026', '2026/2027'] as $ta)
                            <option value="{{ $ta }}" {{ $ta === '2025/2026' ? 'selected' : '' }}>{{ $ta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-300 mb-1">Semester</label>
                        <select name="semester"
                            class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <option value="">--</option>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>

                {{-- Lokasi & Nomor SK --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-brand-300 mb-1">Lokasi Kegiatan</label>
                        <input type="text" name="lokasi" value="{{ old('lokasi') }}"
                            placeholder="Kota / Universitas"
                            class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-300 mb-1">Jenis Anggota <span class="text-red-400">*</span></label>
                        <select name="jenis_anggota" required
                            class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                            <option value="Personal" {{ old('jenis_anggota') === 'Personal' ? 'selected' : '' }}>Personal</option>
                            <option value="Kelompok" {{ old('jenis_anggota') === 'Kelompok' ? 'selected' : '' }}>Kelompok</option>
                        </select>
                    </div>
                </div>

                {{-- Nomor SK --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-1">Nomor SK / Sertifikat</label>
                    <input type="text" name="nomor_sk" value="{{ old('nomor_sk') }}"
                        placeholder="No. SK atau sertifikat"
                        class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                </div>

                {{-- Tanggal SK & Google Drive --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-brand-300 mb-1">Tanggal SK / Sertifikat</label>
                        <input type="date" name="tanggal_sk" value="{{ old('tanggal_sk') }}"
                            class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-300 mb-1">Link Google Drive (Bukti)</label>
                        <input type="url" name="google_drive_link" value="{{ old('google_drive_link') }}"
                            placeholder="https://drive.google.com/..."
                            class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                {{-- Upload Bukti --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-1">
                        Upload Bukti <span class="text-brand-500 font-normal">(PDF/JPG/PNG, maks. 2 MB)</span>
                    </label>
                    <input type="file" name="file_bukti" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full text-sm text-brand-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer cursor-pointer bg-brand-900 border border-brand-600 rounded-lg px-3 py-2">
                    <p class="text-xs text-brand-500 mt-1">Atau gunakan link Google Drive di bawah jika file terlalu besar.</p>
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="block text-sm font-medium text-brand-300 mb-1">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="2"
                        placeholder="Keterangan opsional..."
                        class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">{{ old('keterangan') }}</textarea>
                </div>

            </div>
            <div class="flex gap-3 mt-6 justify-end">
                <button type="button" onclick="document.getElementById('modalSkkm').classList.add('hidden')"
                    class="px-5 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg font-semibold transition">
                    💾 Simpan Kegiatan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const allTypes = @json($activityTypes);

    function filterTypes(category) {
        const sel = document.getElementById('activityTypeSelect');
        const current = sel.value;
        sel.innerHTML = '<option value="">-- Pilih Jenis Kegiatan --</option>';
        allTypes.filter(t => t.category === category).forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            sel.appendChild(opt);
        });
        sel.value = '';
        document.getElementById('roleSelect').innerHTML = '<option value="">-- Pilih jenis kegiatan dahulu --</option>';
        document.getElementById('roleSelect').disabled = true;
        document.getElementById('levelSelect').innerHTML = '<option value="">-- Pilih peran dahulu --</option>';
        document.getElementById('levelSelect').disabled = true;
        document.getElementById('pointPreview').classList.add('hidden');
    }

    function loadRoles(typeId) {
        const roleSelect = document.getElementById('roleSelect');
        roleSelect.innerHTML = '<option value="">⏳ Memuat...</option>';
        roleSelect.disabled = true;
        document.getElementById('levelSelect').innerHTML = '<option value="">-- Pilih peran dahulu --</option>';
        document.getElementById('levelSelect').disabled = true;
        document.getElementById('pointPreview').classList.add('hidden');

        if (!typeId) {
            roleSelect.innerHTML = '<option value="">-- Pilih jenis kegiatan dahulu --</option>';
            return;
        }

        fetch('/api/skkm/roles/' + typeId)
            .then(r => r.json())
            .then(roles => {
                roleSelect.innerHTML = '<option value="">-- Pilih Peran --</option>';
                roles.forEach(role => {
                    const opt = document.createElement('option');
                    opt.value = role;
                    opt.textContent = role;
                    roleSelect.appendChild(opt);
                });
                roleSelect.disabled = false;
            });
    }

    function loadLevels() {
        const typeId = document.getElementById('activityTypeSelect').value;
        const role = document.getElementById('roleSelect').value;
        const levSel = document.getElementById('levelSelect');
        levSel.innerHTML = '<option value="">⏳ Memuat...</option>';
        levSel.disabled = true;
        document.getElementById('pointPreview').classList.add('hidden');

        if (!typeId || !role) {
            levSel.innerHTML = '<option value="">-- Pilih peran dahulu --</option>';
            return;
        }

        fetch('/api/skkm/levels/' + typeId + '/' + encodeURIComponent(role))
            .then(r => r.json())
            .then(levels => {
                levSel.innerHTML = '<option value="">-- Pilih Tingkat --</option>';
                levels.forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = l.id;
                    opt.dataset.points = l.points;
                    opt.textContent = l.level + ' — ' + l.points + ' poin';
                    levSel.appendChild(opt);
                });
                levSel.disabled = false;
            });
    }

    function showPoints() {
        const sel = document.getElementById('levelSelect');
        const opt = sel.options[sel.selectedIndex];
        const pts = opt ? opt.dataset.points : null;
        if (pts) {
            document.getElementById('pointValue').textContent = pts;
            document.getElementById('pointPreview').classList.remove('hidden');
        } else {
            document.getElementById('pointPreview').classList.add('hidden');
        }
    }

    // Initialize with category A
    filterTypes('A');

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalSkkm').classList.remove('hidden');
    });
    @endif

    // Close modal on backdrop click
    document.getElementById('modalSkkm').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
@endsection