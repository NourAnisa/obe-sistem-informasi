@extends('layouts.dashboard')

@section('title', isset($distribusiDosen) ? 'Edit Distribusi Dosen' : 'Tambah Distribusi Dosen')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ isset($distribusiDosen) ? '✏️ Edit Distribusi Dosen' : '➕ Tambah Distribusi Dosen' }}
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">
            {{ isset($distribusiDosen) ? 'Perbarui penugasan dosen ke mata kuliah' : 'Tugaskan dosen ke mata kuliah untuk semester ini' }}
        </p>
    </div>

    @if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <p class="font-semibold mb-1">❌ Terdapat kesalahan:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST"
            action="{{ isset($distribusiDosen)
                         ? route('distribusi-dosen.update', $distribusiDosen)
                         : route('distribusi-dosen.store') }}">
            @csrf
            @if(isset($distribusiDosen)) @method('PUT') @endif

            {{-- Dosen --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Dosen <span class="text-red-500">*</span>
                </label>
                <select name="dosen_id" required
                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none @error('dosen_id') border-red-400 @enderror">
                    <option value="">— Pilih Dosen —</option>
                    @foreach($dosens as $d)
                    <option value="{{ $d->id }}"
                        {{ old('dosen_id', $distribusiDosen->dosen_id ?? '') == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}{{ $d->nik ? ' (' . $d->nik . ')' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('dosen_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Mata Kuliah --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Mata Kuliah <span class="text-red-500">*</span>
                </label>
                <select name="mata_kuliah_id" required
                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none @error('mata_kuliah_id') border-red-400 @enderror">
                    <option value="">— Pilih Mata Kuliah —</option>
                    @foreach($mataKuliahs->groupBy('semester') as $smt => $mks)
                    <optgroup label="Semester {{ $smt }}">
                        @foreach($mks as $mk)
                        <option value="{{ $mk->id }}"
                            {{ old('mata_kuliah_id', $distribusiDosen->mata_kuliah_id ?? $preselectedMkId ?? '') == $mk->id ? 'selected' : '' }}>
                            {{ $mk->kode }} — {{ $mk->nama }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('mata_kuliah_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Row: Kelas + Semester --}}
            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kelas</label>
                    <input type="text" name="kelas" maxlength="10" placeholder="A / B / ..."
                        value="{{ old('kelas', $distribusiDosen->kelas ?? '') }}"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none @error('kelas') border-red-400 @enderror">
                    @error('kelas')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Semester <span class="text-red-500">*</span>
                    </label>
                    <select name="semester" required
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none @error('semester') border-red-400 @enderror">
                        @for($s = 1; $s <= 8; $s++)
                            <option value="{{ $s }}"
                            {{ old('semester', $distribusiDosen->semester ?? '') == $s ? 'selected' : '' }}>
                            Semester {{ $s }}
                            </option>
                            @endfor
                    </select>
                    @error('semester')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Tahun Akademik --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Tahun Akademik <span class="text-red-500">*</span>
                </label>
                <input type="text" name="tahun_akademik" placeholder="2025/2026" maxlength="20"
                    value="{{ old('tahun_akademik', $distribusiDosen->tahun_akademik ?? $ta) }}"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none @error('tahun_akademik') border-red-400 @enderror">
                @error('tahun_akademik')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Peran --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Peran <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="peran" value="pengampu"
                            {{ old('peran', $distribusiDosen->peran ?? 'pengampu') === 'pengampu' ? 'checked' : '' }}
                            class="text-brand-600 focus:ring-brand-400">
                        <span class="text-sm text-gray-700">Pengampu</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="peran" value="pengembang_rps"
                            {{ old('peran', $distribusiDosen->peran ?? '') === 'pengembang_rps' ? 'checked' : '' }}
                            class="text-brand-600 focus:ring-brand-400">
                        <span class="text-sm text-gray-700">Pengembang RPS</span>
                    </label>
                </div>
                @error('peran')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Row: Jumlah SKS + Status --}}
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jumlah SKS</label>
                    <input type="number" name="jumlah_sks" min="1" max="6" placeholder="misal: 3"
                        value="{{ old('jumlah_sks', $distribusiDosen->jumlah_sks ?? '') }}"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none @error('jumlah_sks') border-red-400 @enderror">
                    @error('jumlah_sks')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                    <select name="status" required
                        class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-400 focus:outline-none @error('status') border-red-400 @enderror">
                        <option value="aktif"    {{ old('status', $distribusiDosen->status ?? 'aktif') === 'aktif'    ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $distribusiDosen->status ?? '')      === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('distribusi-dosen.index', ['ta' => $ta]) }}"
                    class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">
                    ← Batal
                </a>
                <button type="submit"
                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm">
                    {{ isset($distribusiDosen) ? '💾 Simpan Perubahan' : '✅ Simpan Distribusi' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection