@extends('layouts.dashboard')
@section('title', 'Manajemen Mata Kuliah')
@section('breadcrumb', 'Admin / Mata Kuliah')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Mata Kuliah (MK)</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola daftar mata kuliah kurikulum Sistem Informasi</p>
        </div>
        <a href="{{ route('admin.mk.create') }}"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            ➕ Tambah MK
        </a>
    </div>

    @include('admin._alerts')

    {{-- CPL filter banner --}}
    @if(isset($filterCpl) && $filterCpl)
    <div class="mb-4 flex items-center justify-between bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-3">
        <div class="text-sm text-indigo-800">
            🎯 Menampilkan MK yang memetakan CPL <strong>{{ $filterCpl->kode }}</strong>:
            <span class="text-indigo-600">{{ Str::limit($filterCpl->deskripsi, 80) }}</span>
        </div>
        <a href="{{ route('admin.mk.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 border border-indigo-200 px-3 py-1.5 rounded-lg bg-white">✕ Reset</a>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56 focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="🔍 Cari kode / nama MK...">
        <select name="semester" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="">Semua Semester</option>
            @for($i=1; $i<=8; $i++)
                <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                @endfor
        </select>
        <select name="kategori" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="">Semua Kategori</option>
            @foreach($kategoris as $k)
            <option value="{{ $k }}" {{ request('kategori') === $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">Filter</button>
        <a href="{{ route('admin.mk.index') }}" class="text-sm text-gray-500 hover:text-gray-700 px-2 py-2">Reset</a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Mata Kuliah</th>
                    <th class="px-4 py-3 text-center">Sem</th>
                    <th class="px-4 py-3 text-center">SKS</th>
                    <th class="px-4 py-3 text-center">Kategori</th>
                    <th class="px-4 py-3 text-center">CPL</th>
                    <th class="px-4 py-3 text-center">CPMK</th>
                    <th class="px-4 py-3 text-center">Dosen</th>
                    <th class="px-4 py-3 text-left">PJMK</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($mataKuliahs as $mk)
                @php
                $katColors = [
                'MKF'=>'bg-purple-100 text-purple-800','MKPU'=>'bg-blue-100 text-blue-800',
                'MKWK'=>'bg-green-100 text-green-800','MKPP'=>'bg-orange-100 text-orange-800',
                'MKP'=>'bg-yellow-100 text-yellow-800','MKKP'=>'bg-gray-100 text-gray-800',
                ];
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-700">{{ $mk->kode }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $mk->nama }}
                        @if($mk->is_mbkm)<span class="ml-1 text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded">MBKM</span>@endif
                        @if(!$mk->is_wajib)<span class="ml-1 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">Pilihan</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-600">{{ $mk->semester }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">{{ $mk->sks }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-semibold px-2 py-1 rounded {{ $katColors[$mk->kategori] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $mk->kategori }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $mk->cpls_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $mk->cpmks_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('distribusi-dosen.index', ['mk_id' => $mk->id]) }}"
                            title="Lihat distribusi dosen untuk MK ini"
                            class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg
                                {{ $mk->dosen_mata_kuliahs_count > 0 ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}">
                            👨‍🏫 {{ $mk->dosen_mata_kuliahs_count }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $mk->pjmk ? Str::limit($mk->pjmk, 30) : '–' }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('mata-kuliah.show', $mk->kode) }}"
                                class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold px-2 py-1 rounded transition" title="Lihat Detail">👁</a>
                            <a href="{{ route('admin.mk.edit', $mk) }}"
                                class="text-xs bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-2 py-1 rounded transition">✏️</a>
                            <a href="{{ route('distribusi-dosen.create', ['mk_id' => $mk->id]) }}"
                                class="text-xs bg-blue-500 hover:bg-blue-600 text-white font-semibold px-2 py-1 rounded transition" title="Tambah distribusi dosen">👨‍🏫+</a>
                            <form method="POST" action="{{ route('admin.mk.destroy', $mk) }}"
                                onsubmit="return confirm('Hapus MK {{ addslashes($mk->nama) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold px-2 py-1 rounded transition">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-10 text-center text-gray-400 italic">Belum ada data mata kuliah</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-4">
        <p class="text-xs text-gray-400">Total: {{ $mataKuliahs->total() }} MK</p>
        {{ $mataKuliahs->links() }}
    </div>
</div>
@endsection