@extends('layouts.dashboard')

@section('title', 'Manajemen Fakultas')
@section('breadcrumb', 'Manajemen Fakultas')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">🏛 Manajemen Fakultas</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola data Fakultas dan Program Studi universitas</p>
        </div>
        <a href="{{ route('admin.programs.index') }}"
            class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
            🎓 Kelola Program Studi
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
        <span>⚠️</span> {{ session('error') }}
    </div>
    @endif

    {{-- Tambah Fakultas --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-700 to-blue-600 px-6 py-4">
            <h2 class="text-white font-semibold text-base">➕ Tambah Fakultas Baru</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.faculties.store') }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Fakultas *</label>
                    <input type="text" name="nama" required value="{{ old('nama') }}"
                        placeholder="cth: Fakultas Sains dan Teknologi"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kode</label>
                    <input type="text" name="kode" value="{{ old('kode') }}" maxlength="10"
                        placeholder="FST"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none">
                </div>
                <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition shrink-0">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    {{-- Daftar Fakultas --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Daftar Fakultas ({{ $faculties->count() }})</h2>
        </div>
        @if($faculties->isEmpty())
        <p class="px-6 py-10 text-center text-slate-400 text-sm">Belum ada data fakultas.</p>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Kode</th>
                    <th class="px-6 py-3">Nama Fakultas</th>
                    <th class="px-6 py-3 text-center">Jumlah Prodi</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($faculties as $faculty)
                <tr class="hover:bg-slate-50/50 transition" x-data="{ edit: false }">
                    <td class="px-6 py-4">
                        <span class="font-mono text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-lg">{{ $faculty->kode ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span x-show="!edit" class="font-medium text-slate-800">{{ $faculty->nama }}</span>
                        <form x-show="edit" x-cloak method="POST"
                            action="{{ route('admin.faculties.update', $faculty) }}"
                            class="flex gap-2 items-center">
                            @csrf @method('PUT')
                            <input type="text" name="nama" value="{{ $faculty->nama }}" required
                                class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm w-72 focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" name="kode" value="{{ $faculty->kode }}" maxlength="10"
                                class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm w-20 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Kode">
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700">Simpan</button>
                            <button type="button" @click="edit=false" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-300">Batal</button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-semibold">
                            {{ $faculty->programs_count }} Prodi
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button @click="edit = !edit"
                                class="px-3 py-1.5 text-xs bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 font-semibold transition">
                                ✏️ Edit
                            </button>
                            @if($faculty->programs_count == 0)
                            <form method="POST" action="{{ route('admin.faculties.destroy', $faculty) }}"
                                onsubmit="return confirm('Hapus fakultas ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 text-xs bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 font-semibold transition">
                                    🗑 Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
