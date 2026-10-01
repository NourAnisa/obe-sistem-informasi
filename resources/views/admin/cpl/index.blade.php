@extends('layouts.dashboard')
@section('title', 'Manajemen CPL')
@section('breadcrumb', 'Admin / CPL')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Capaian Pembelajaran Lulusan (CPL)</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola daftar CPL Program Studi Sistem Informasi</p>
        </div>
        <a href="{{ route('admin.cpl.create') }}"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            ➕ Tambah CPL
        </a>
    </div>

    @include('admin._alerts')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left w-24">Kode</th>
                    <th class="px-4 py-3 text-left">Deskripsi</th>
                    <th class="px-4 py-3 text-center w-28">Kategori</th>
                    <th class="px-4 py-3 text-center w-24">Skor Maks</th>
                    <th class="px-4 py-3 text-center w-20">CPMK</th>
                    <th class="px-4 py-3 text-center w-20">MK</th>
                    <th class="px-4 py-3 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cpls as $cpl)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-bold text-blue-700">{{ $cpl->kode }}</td>
                    <td class="px-4 py-3 text-gray-700 max-w-lg">{{ Str::limit($cpl->deskripsi, 100) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-semibold px-2 py-1 rounded {{ $cpl->badge_class }}">
                            {{ $cpl->kategori }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center font-mono">{{ $cpl->total_skor_maks }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $cpl->cpmks_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">
                        <a href="{{ route('admin.mk.index', ['cpl_id' => $cpl->id]) }}"
                            class="hover:text-blue-600 hover:underline font-medium"
                            title="Lihat MK yang memetakan CPL {{ $cpl->kode }}">
                            {{ $cpl->mata_kuliahs_count }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.cpl.edit', $cpl) }}"
                                class="text-xs bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-3 py-1 rounded transition">
                                ✏️ Edit
                            </a>
                            <form method="POST" action="{{ route('admin.cpl.destroy', $cpl) }}"
                                onsubmit="return confirm('Hapus CPL {{ $cpl->kode }}? Data CPMK terkait mungkin terpengaruh.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded transition">
                                    🗑
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 italic">Belum ada data CPL</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-3">Total: {{ $cpls->count() }} CPL</p>
</div>
@endsection