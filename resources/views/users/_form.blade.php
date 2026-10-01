{{-- Shared form fields for create & edit --}}
@php
$roles = [
'admin'         => '👑 Admin',
'dekan'         => '🏛 Dekan',
'wakildekan'    => '🏫 Wakil Dekan',
'kaprodi'       => '🎓 Kaprodi',
'dosen'         => '📚 Dosen',
'akademik'      => '🗂 Akademik',
'kemahasiswaan' => '🤝 Kemahasiswaan',
'viewer'        => '👁 Viewer',
];
$selectedProgramId = old('program_id', $user?->program_id);
$selectedFacultyId = old('faculty_id', $user?->program?->faculty_id);
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap (beserta gelar) <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $user?->name) }}" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="Contoh: Nor Anisa, S.Kom., M.Kom.">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
    <input type="email" name="email" value="{{ old('email', $user?->email) }}" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="nama@unism.ac.id">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
    <select name="role" required id="roleSelect"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"
        onchange="toggleProdiField(this.value)">
        @foreach($roles as $val => $label)
        <option value="{{ $val }}" {{ old('role', $user?->role) === $val ? 'selected' : '' }}>
            {{ $label }}
        </option>
        @endforeach
    </select>
</div>

{{-- ── Fakultas & Program Studi ── --}}
<div id="prodiSection" class="border border-blue-100 bg-blue-50 rounded-lg p-4 space-y-3">
    <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">🏛 Penugasan Program Studi</p>

    <div class="grid grid-cols-2 gap-3">
        {{-- Pilih Fakultas --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Fakultas</label>
            <select id="facultySelect" name="faculty_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"
                onchange="loadProdiOptions(this.value, null)">
                <option value="">— Pilih Fakultas —</option>
                @foreach($faculties as $f)
                <option value="{{ $f->id }}" {{ $selectedFacultyId == $f->id ? 'selected' : '' }}>
                    {{ $f->nama }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Pilih Program Studi --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Program Studi</label>
            <select id="programSelect" name="program_id"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                <option value="">— Pilih Prodi —</option>
                @if($selectedFacultyId)
                    @foreach($faculties->firstWhere('id', $selectedFacultyId)?->programs ?? [] as $p)
                    <option value="{{ $p->id }}" {{ $selectedProgramId == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->jenjang }})
                    </option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
    <p class="text-xs text-blue-500">Wajib diisi untuk role Kaprodi dan Dosen agar data terfilter per prodi.</p>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
    <input type="text" name="jabatan" value="{{ old('jabatan', $user?->jabatan) }}"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="Contoh: Ketua Program Studi Sistem Informasi">
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
        <input type="text" name="nik" value="{{ old('nik', $user?->nik) }}" maxlength="20"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="Nomor Induk Karyawan">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">NUPTK</label>
        <input type="text" name="nuptk" value="{{ old('nuptk', $user?->nuptk) }}" maxlength="20"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="Nomor Unik PTK">
    </div>
</div>

@if(!$user)
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
    <input type="password" name="password" required minlength="6"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="Min. 6 karakter">
</div>
@endif

@once
@push('scripts')
<script>
// Semua data prodi per fakultas (digenerate dari server, tidak perlu AJAX)
const prodiData = {!! json_encode($faculties->mapWithKeys(fn($f) => [$f->id => $f->programs->map(fn($p) => ['id' => $p->id, 'nama' => $p->nama, 'jenjang' => $p->jenjang])->values()])) !!};

function loadProdiOptions(facultyId, selectedProdiId) {
    const sel = document.getElementById('programSelect');
    sel.innerHTML = '<option value="">— Pilih Prodi —</option>';
    if (!facultyId || !prodiData[facultyId]) return;
    prodiData[facultyId].forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.text = p.nama + ' (' + p.jenjang + ')';
        if (selectedProdiId && p.id == selectedProdiId) opt.selected = true;
        sel.appendChild(opt);
    });
}

function toggleProdiField(role) {
    // Tampilkan section prodi untuk semua role (berguna untuk filtering)
    // Hanya sembunyikan untuk role yang tidak relevan (mahasiswa — tidak punya akun user biasa)
    const section = document.getElementById('prodiSection');
    if (section) section.style.display = 'block';
}

// Init on page load
document.addEventListener('DOMContentLoaded', function () {
    toggleProdiField(document.getElementById('roleSelect')?.value);
    // Jika ada faculty yang sudah terpilih tapi prodi dropdown kosong, isi ulang
    const fac = document.getElementById('facultySelect');
    const prog = document.getElementById('programSelect');
    if (fac && fac.value && prog && prog.options.length <= 1) {
        loadProdiOptions(fac.value, {{ $selectedProgramId ?? 'null' }});
    }
});
</script>
@endpush
@endonce