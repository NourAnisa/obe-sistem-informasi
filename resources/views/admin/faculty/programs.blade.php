@extends('layouts.dashboard')

@section('title', 'Manajemen Program Studi')
@section('breadcrumb', 'Program Studi')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">🎓 Program Studi</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola semua program studi per fakultas ({{ $programs->count() }} terdaftar)</p>
        </div>
        <a href="{{ route('admin.faculties.index') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
            🏛 Kelola Fakultas
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

    {{-- Tambah Program Studi --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-700 to-indigo-500 px-6 py-4">
            <h2 class="text-white font-semibold text-base">➕ Tambah Program Studi Baru</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.programs.store') }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="w-48">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Fakultas *</label>
                    <select name="faculty_id" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Pilih Fakultas --</option>
                        @foreach($faculties as $f)
                        <option value="{{ $f->id }}" {{ old('faculty_id') == $f->id ? 'selected' : '' }}>{{ $f->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Program Studi *</label>
                    <input type="text" name="nama" required value="{{ old('nama') }}"
                        placeholder="cth: Sarjana Sistem Informasi"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jenjang *</label>
                    <select name="jenjang" required
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        @foreach(['D3','D4','S1','S2','S3','Profesi'] as $j)
                        <option value="{{ $j }}" {{ old('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Prodi</label>
                    <input type="text" name="kode_prodi" value="{{ old('kode_prodi') }}" maxlength="20"
                        placeholder="cth: 57201"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                </div>
                <button type="submit"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition shrink-0">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    {{-- Daftar per Fakultas --}}
    @php
        $grouped = $programs->groupBy(fn($p) => $p->faculty->nama ?? 'Tidak Diketahui');
        $badgeColors = [
            'D3' => 'bg-orange-50 text-orange-700',
            'D4' => 'bg-amber-50 text-amber-700',
            'S1' => 'bg-blue-50 text-blue-700',
            'S2' => 'bg-indigo-50 text-indigo-700',
            'S3' => 'bg-purple-50 text-purple-700',
            'Profesi' => 'bg-green-50 text-green-700',
        ];
    @endphp

    @foreach($grouped as $facultyName => $prods)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="w-8 h-8 bg-blue-100 text-blue-700 rounded-lg flex items-center justify-center text-base">🏛</span>
                {{ $facultyName }}
            </h3>
            <span class="text-xs text-slate-400 font-medium">{{ $prods->count() }} Program Studi</span>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Nama Program Studi</th>
                    <th class="px-6 py-3 w-24 text-center">Jenjang</th>
                    <th class="px-6 py-3 w-28">Kode Prodi</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($prods as $program)
                <tr class="hover:bg-slate-50/50 transition" x-data="{ edit: false }">
                    <td class="px-6 py-3.5">
                        <span x-show="!edit" class="font-medium text-slate-800">{{ $program->nama }}</span>
                        <form x-show="edit" x-cloak method="POST"
                            action="{{ route('admin.programs.update', $program) }}"
                            class="flex flex-wrap gap-2 items-center">
                            @csrf @method('PUT')
                            <select name="faculty_id"
                                class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                @foreach($faculties as $f)
                                <option value="{{ $f->id }}" {{ $f->id == $program->faculty_id ? 'selected' : '' }}>{{ $f->nama }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="nama" value="{{ $program->nama }}" required
                                class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm w-64 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <select name="jenjang"
                                class="border border-slate-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                @foreach(['D3','D4','S1','S2','S3','Profesi'] as $j)
                                <option value="{{ $j }}" {{ $program->jenjang === $j ? 'selected' : '' }}>{{ $j }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="kode_prodi" value="{{ $program->kode_prodi }}" maxlength="20"
                                placeholder="Kode Prodi"
                                class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm w-24 focus:ring-2 focus:ring-indigo-500 outline-none">
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700">Simpan</button>
                            <button type="button" @click="edit=false" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-300">Batal</button>
                        </form>
                    </td>
                    <td class="px-6 py-3.5 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badgeColors[$program->jenjang] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $program->jenjang }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5">
                        <span class="font-mono text-xs text-slate-500">{{ $program->kode_prodi ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-3.5 text-right">
                        <div class="flex justify-end gap-2">
                            <button @click="edit = !edit"
                                class="px-3 py-1.5 text-xs bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 font-semibold transition">
                                ✏️ Edit
                            </button>
                            <form method="POST" action="{{ route('admin.programs.destroy', $program) }}"
                                onsubmit="return confirm('Hapus program studi ini? Pastikan tidak ada data terkait.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 text-xs bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 font-semibold transition">
                                    🗑 Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
</div>
@endsection
