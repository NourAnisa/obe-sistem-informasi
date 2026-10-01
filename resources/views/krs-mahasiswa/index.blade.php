@extends('layouts.dashboard')
@section('title', 'KRS Mahasiswa')
@section('breadcrumb', 'KRS Mahasiswa')

@section('content')
<div class="space-y-5">

  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-bold text-gray-800">📋 KRS Mahasiswa</h1>
      <p class="text-xs text-gray-500 mt-1">Daftar enrollment mahasiswa per Mata Kuliah · Kelas · Tahun Akademik</p>
    </div>
    @if(in_array(auth()->user()->role ?? '', ['admin','akademik']))
    <div class="flex gap-2">
      <a href="{{ route('krs-mahasiswa.create', ['ta'=>$ta]) }}"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold">
        ➕ Tambah KRS
      </a>
    </div>
    @endif
  </div>

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">
    ✅ {{ session('success') }}
  </div>
  @endif

  {{-- Summary Cards --}}
  <div class="grid grid-cols-3 gap-4">
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
      <div class="text-2xl font-bold text-blue-700">{{ $summary['total'] }}</div>
      <div class="text-xs text-gray-500 mt-1">Total KRS (TA {{ $ta }})</div>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
      <div class="text-2xl font-bold text-green-700">{{ $summary['aktif'] }}</div>
      <div class="text-xs text-gray-500 mt-1">Mahasiswa Aktif</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
      <div class="text-2xl font-bold text-red-700">{{ $summary['drop'] }}</div>
      <div class="text-xs text-gray-500 mt-1">Drop / Mengundurkan Diri</div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="bg-white border border-gray-200 rounded-xl p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Akademik</label>
        <select name="ta" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
          @foreach($taList as $t)
          <option value="{{ $t }}" @selected($t===$ta)>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Mata Kuliah</label>
        <select name="mk_id" class="border rounded px-3 py-2 text-sm">
          <option value="">— Semua MK —</option>
          @foreach($mataKuliahs as $mk)
          <option value="{{ $mk->id }}" @selected((int)$mkId===$mk->id)>{{ $mk->kode }} — {{ Str::limit($mk->nama,30) }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Kelas</label>
        <select name="kelas" class="border rounded px-3 py-2 text-sm">
          <option value="">— Semua —</option>
          @foreach($kelasList as $k)
          <option value="{{ $k }}" @selected($kelas===$k)>{{ $k }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
        <select name="status" class="border rounded px-3 py-2 text-sm">
          <option value="aktif" @selected($status==='aktif' )>Aktif</option>
          <option value="drop" @selected($status==='drop' )>Drop</option>
          <option value="all" @selected($status==='all' )>Semua</option>
        </select>
      </div>
      <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded text-sm font-medium">🔍 Filter</button>
    </form>
  </div>

  {{-- Table --}}
  <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
      <p class="text-sm font-bold text-gray-700">Daftar KRS ({{ $krs->count() }} data)</p>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
            <th class="px-4 py-3 text-left">NIM</th>
            <th class="px-4 py-3 text-left">Nama Mahasiswa</th>
            <th class="px-4 py-3 text-left">Angkatan</th>
            <th class="px-4 py-3 text-left">Mata Kuliah</th>
            <th class="px-4 py-3 text-center">Kelas</th>
            <th class="px-4 py-3 text-center">Sem</th>
            <th class="px-4 py-3 text-center">TA</th>
            <th class="px-4 py-3 text-center">Status</th>
            @if(in_array(auth()->user()->role ?? '', ['admin','akademik']))
            <th class="px-4 py-3 text-center">Aksi</th>
            @endif
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($krs as $k)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 font-mono font-semibold text-blue-700">{{ $k->mahasiswa?->nim ?? '—' }}</td>
            <td class="px-4 py-2 font-medium text-gray-800">{{ $k->mahasiswa?->nama ?? '—' }}</td>
            <td class="px-4 py-2 text-center text-gray-600">{{ $k->mahasiswa?->angkatan ?? '—' }}</td>
            <td class="px-4 py-2">
              <span class="font-semibold text-gray-700">{{ $k->mataKuliah?->kode }}</span>
              <span class="text-gray-400 text-xs"> — {{ Str::limit($k->mataKuliah?->nama ?? '', 30) }}</span>
            </td>
            <td class="px-4 py-2 text-center font-bold text-gray-700">{{ $k->kelas }}</td>
            <td class="px-4 py-2 text-center text-gray-600">{{ $k->semester }}</td>
            <td class="px-4 py-2 text-center text-xs text-gray-500">{{ $k->tahun_akademik }}</td>
            <td class="px-4 py-2 text-center">
              @if($k->status === 'aktif')
              <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs font-semibold">Aktif</span>
              @else
              <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-semibold">Drop</span>
              @endif
            </td>
            @if(in_array(auth()->user()->role ?? '', ['admin','akademik']))
            <td class="px-4 py-2 text-center">
              <div class="flex gap-1 justify-center">
                <a href="{{ route('krs-mahasiswa.edit', $k->id) }}"
                  class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded hover:bg-yellow-200">✏️ Edit</a>
                <form method="POST" action="{{ route('krs-mahasiswa.destroy', $k->id) }}"
                  onsubmit="return confirm('Hapus KRS ini?')">
                  @csrf @method('DELETE')
                  <button class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">🗑️</button>
                </form>
              </div>
            </td>
            @endif
          </tr>
          @empty
          <tr>
            <td colspan="9" class="px-4 py-8 text-center text-gray-400 text-sm">
              Belum ada data KRS untuk filter ini.<br>
              <span class="text-xs">Tambah KRS secara manual atau gunakan fitur Import.</span>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection