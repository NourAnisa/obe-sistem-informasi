@extends('layouts.dashboard')
@section('title', 'Laporan Evaluasi')
@section('breadcrumb', 'Laporan Evaluasi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">Laporan Evaluasi</h1>
            <p class="text-gray-500 text-sm mt-0.5">
                Ketercapaian pembelajaran per mata kuliah aktif
                <span class="ml-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                    🟢 TA {{ $filterTaActive }}
                </span>
            </p>
        </div>
        <a href="{{ route('laporan-evaluasi.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow transition">
            <span class="text-base">＋</span> Buat Laporan Baru
        </a>
    </div>

    @if(session('success'))
    <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('laporan-evaluasi.index') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Mata Kuliah</label>
            <select name="mata_kuliah_id"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Semua MK</option>
                @foreach($mataKuliahs as $mk)
                <option value="{{ $mk->id }}" {{ request('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                    [{{ $mk->kode }}] {{ $mk->nama }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[130px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">Semester</label>
            <select name="semester"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Semua</option>
                <option value="Ganjil" {{ request('semester') === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                <option value="Genap" {{ request('semester') === 'Genap'  ? 'selected' : '' }}>Genap</option>
            </select>
        </div>
        <div class="min-w-[150px]">
            <label class="block text-xs font-medium text-gray-600 mb-1">
                Tahun Akademik
                <span class="text-green-600 font-bold">(Aktif: {{ $taAktif }})</span>
            </label>
            <select name="tahun_akademik"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                @foreach($tahunAkademiks as $ta)
                <option value="{{ $ta }}"
                    {{ $filterTaActive === $ta ? 'selected' : '' }}>
                    {{ $ta }}{{ $ta === $taAktif ? ' ✓ Aktif' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition">
                🔍 Filter
            </button>
            <a href="{{ route('laporan-evaluasi.index') }}"
                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition">
                Reset
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($laporans->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <div class="text-5xl mb-3">📋</div>
            <p class="font-medium">Belum ada laporan evaluasi untuk TA {{ $filterTaActive }}</p>
            <p class="text-sm mt-1">Klik "Buat Laporan Baru" untuk memulai, atau ubah filter Tahun Akademik</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-brand-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Mata Kuliah</th>
                        <th class="px-4 py-3 text-left font-semibold">Semester</th>
                        <th class="px-4 py-3 text-left font-semibold">Tahun Akademik</th>
                        <th class="px-4 py-3 text-center font-semibold">Kelas</th>
                        <th class="px-4 py-3 text-center font-semibold">Jml Mhs</th>
                        <th class="px-4 py-3 text-center font-semibold">Status</th>
                        <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($laporans as $laporan)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">
                                {{ $laporan->mataKuliah->kode ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-500">{{ $laporan->mataKuliah->nama ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $laporan->semester }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $laporan->tahun_akademik }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $laporan->kelas ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $laporan->jumlah_mahasiswa }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($laporan->status === 'final')
                            <span class="badge bg-green-100 text-green-800">✓ Final</span>
                            @else
                            <span class="badge bg-yellow-100 text-yellow-800">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                <a href="{{ route('laporan-evaluasi.show', $laporan->id) }}"
                                    class="px-2.5 py-1 bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-lg text-xs font-medium transition"
                                    title="Lihat Detail">👁 Lihat</a>
                                <a href="{{ route('laporan-evaluasi.edit', $laporan->id) }}"
                                    class="px-2.5 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-lg text-xs font-medium transition"
                                    title="Edit">✏️ Edit</a>
                                <a href="{{ route('laporan-evaluasi.export-word', $laporan->id) }}"
                                    class="px-2.5 py-1 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-medium transition"
                                    title="Export Word">📄 Word</a>
                                <form method="POST" action="{{ route('laporan-evaluasi.destroy', $laporan->id) }}"
                                    onsubmit="return confirm('Hapus laporan ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="px-2.5 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-xs font-medium transition">
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
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $laporans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection