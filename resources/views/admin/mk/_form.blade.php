@php
$kategoris = ['MKF' => 'MKF – Mata Kuliah Fakultas', 'MKPU' => 'MKPU – MK Penciri Universitas', 'MKWK' => 'MKWK – MK Wajib Kurikulum', 'MKPP' => 'MKPP – MK Penciri Program Studi', 'MKP' => 'MKP – MK Pilihan', 'MKKP' => 'MKKP – MK Keahlian Khusus Prodi'];
@endphp

{{-- Hanya admin yang bisa pilih prodi --}}
@if(auth()->user()->role === 'admin')
<div class="border border-amber-100 bg-amber-50 rounded-lg p-4 mb-2">
    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">🏛 Program Studi (Admin Override)</p>
    <select name="program_id"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
        <option value="">— Prodi saya sendiri (default) —</option>
        @foreach(\App\Models\Program::with('faculty')->orderBy('nama')->get() as $prog)
        <option value="{{ $prog->id }}"
            {{ old('program_id', $mk?->program_id) == $prog->id ? 'selected' : '' }}>
            {{ $prog->nama }} ({{ $prog->jenjang }}) — {{ $prog->faculty->nama ?? '' }}
        </option>
        @endforeach
    </select>
    <p class="text-xs text-amber-500 mt-1">Kosongkan untuk menggunakan prodi Anda sendiri.</p>
</div>
@endif

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode MK <span class="text-red-500">*</span></label>
        <input type="text" name="kode" value="{{ old('kode', $mk?->kode) }}" required maxlength="30"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="Contoh: {{ config('obe.universitas_singkat', 'UNIV') }}.{{ auth()->user()->program->kode_prodi ?? 'MK' }}001">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
        <select name="kategori" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">— Pilih —</option>
            @foreach($kategoris as $val => $label)
            <option value="{{ $val }}" {{ old('kategori', $mk?->kategori) === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mata Kuliah <span class="text-red-500">*</span></label>
    <input type="text" name="nama" value="{{ old('nama', $mk?->nama) }}" required maxlength="150"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="Contoh: Algoritma dan Struktur Data">
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Semester <span class="text-red-500">*</span></label>
        <select name="semester" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
            @for($i=1; $i<=8; $i++)
                <option value="{{ $i }}" {{ old('semester', $mk?->semester) == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                @endfor
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Total SKS <span class="text-red-500">*</span></label>
        <input type="number" name="sks" value="{{ old('sks', $mk?->sks ?? 3) }}" required min="1" max="6"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">SKS Teori</label>
        <input type="number" name="sks_teori" value="{{ old('sks_teori', $mk?->sks_teori) }}" min="0" max="6"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">SKS Praktikum</label>
        <input type="number" name="sks_praktikum" value="{{ old('sks_praktikum', $mk?->sks_praktikum ?? 0) }}" min="0" max="6"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Dosen PJMK</label>
        <input type="text" name="pjmk" value="{{ old('pjmk', $mk?->pjmk) }}" maxlength="150"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="Nama dosen penanggung jawab">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi MK</label>
    <textarea name="deskripsi" rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-y"
        placeholder="Deskripsi singkat mata kuliah...">{{ old('deskripsi', $mk?->deskripsi) }}</textarea>
</div>

<div class="flex items-center gap-6">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_mbkm" value="1" {{ old('is_mbkm', $mk?->is_mbkm) ? 'checked' : '' }}
            class="rounded border-gray-300 text-blue-600">
        <span class="text-sm text-gray-700">Mata Kuliah MBKM</span>
    </label>
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_wajib" value="1" {{ old('is_wajib', $mk?->is_wajib ?? true) ? 'checked' : '' }}
            class="rounded border-gray-300 text-blue-600">
        <span class="text-sm text-gray-700">Mata Kuliah Wajib</span>
    </label>
</div>