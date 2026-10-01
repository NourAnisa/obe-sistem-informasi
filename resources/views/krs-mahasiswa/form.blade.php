@extends('layouts.dashboard')
@section('title', isset($krsMahasiswa) ? 'Edit KRS' : 'Tambah KRS')
@section('breadcrumb', isset($krsMahasiswa) ? 'Edit KRS Mahasiswa' : 'Tambah KRS Mahasiswa')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

  <div class="flex items-center gap-3">
    <a href="{{ route('krs-mahasiswa.index') }}" class="text-blue-600 hover:underline text-sm">← Kembali</a>
    <h1 class="text-xl font-bold text-gray-800">
      {{ isset($krsMahasiswa) ? '✏️ Edit KRS Mahasiswa' : '➕ Tambah KRS Mahasiswa' }}
    </h1>
  </div>

  <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
    <form method="POST"
      action="{{ isset($krsMahasiswa) ? route('krs-mahasiswa.update', $krsMahasiswa->id) : route('krs-mahasiswa.store') }}">
      @csrf
      @if(isset($krsMahasiswa)) @method('PUT') @endif

      <div class="grid grid-cols-1 gap-4">

        @if(!isset($krsMahasiswa))
        {{-- Mahasiswa --}}
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Mahasiswa <span class="text-red-500">*</span></label>
          <select name="mahasiswa_id" required class="w-full border rounded px-3 py-2 text-sm">
            <option value="">— Pilih Mahasiswa —</option>
            @foreach($mahasiswas as $m)
            <option value="{{ $m->id }}" @selected(old('mahasiswa_id')==$m->id)>
              {{ $m->nim }} — {{ $m->nama }} ({{ $m->angkatan }})
            </option>
            @endforeach
          </select>
        </div>

        {{-- Mata Kuliah --}}
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Mata Kuliah <span class="text-red-500">*</span></label>
          <select name="mata_kuliah_id" required class="w-full border rounded px-3 py-2 text-sm">
            <option value="">— Pilih Mata Kuliah —</option>
            @foreach($mataKuliahs as $mk)
            <option value="{{ $mk->id }}" @selected(old('mata_kuliah_id')==$mk->id)>
              {{ $mk->kode }} — {{ $mk->nama }} (Sem {{ $mk->semester }})
            </option>
            @endforeach
          </select>
        </div>

        {{-- Tahun Akademik --}}
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
          <input type="text" name="tahun_akademik" value="{{ old('tahun_akademik', $ta ?? '2025/2026') }}"
            placeholder="2025/2026" required class="w-full border rounded px-3 py-2 text-sm">
        </div>
        @else
        {{-- Edit mode: show locked values --}}
        <div class="bg-gray-50 border rounded-lg p-3 text-sm text-gray-600">
          <div><strong>Mahasiswa:</strong> {{ $krsMahasiswa->mahasiswa?->nim }} — {{ $krsMahasiswa->mahasiswa?->nama }}</div>
          <div><strong>Mata Kuliah:</strong> {{ $krsMahasiswa->mataKuliah?->kode }} — {{ $krsMahasiswa->mataKuliah?->nama }}</div>
          <div><strong>Tahun Akademik:</strong> {{ $krsMahasiswa->tahun_akademik }}</div>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
          {{-- Kelas --}}
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas <span class="text-red-500">*</span></label>
            <select name="kelas" required class="w-full border rounded px-3 py-2 text-sm">
              @foreach($kelasList as $k)
              <option value="{{ $k }}" @selected(old('kelas', $krsMahasiswa->kelas ?? 'A')===$k)>{{ $k }}</option>
              @endforeach
            </select>
          </div>

          {{-- Semester --}}
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Semester <span class="text-red-500">*</span></label>
            <select name="semester" required class="w-full border rounded px-3 py-2 text-sm">
              @for($s = 1; $s <= 8; $s++)
                <option value="{{ $s }}" @selected(old('semester', $krsMahasiswa->semester ?? 1)==$s)>Semester {{ $s }}</option>
                @endfor
            </select>
          </div>
        </div>

        {{-- Status --}}
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
          <select name="status" required class="w-full border rounded px-3 py-2 text-sm">
            <option value="aktif" @selected(old('status', $krsMahasiswa->status ?? 'aktif')==='aktif')>✅ Aktif</option>
            <option value="drop" @selected(old('status', $krsMahasiswa->status ?? '')==='drop')>❌ Drop</option>
          </select>
        </div>

      </div>

      @if($errors->any())
      <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
      </div>
      @endif

      <div class="flex gap-3 pt-2">
        <button type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded font-semibold text-sm">
          💾 Simpan
        </button>
        <a href="{{ route('krs-mahasiswa.index') }}"
          class="bg-gray-100 text-gray-700 px-5 py-2 rounded font-medium text-sm hover:bg-gray-200">
          Batal
        </a>
      </div>
    </form>
  </div>

</div>
@endsection