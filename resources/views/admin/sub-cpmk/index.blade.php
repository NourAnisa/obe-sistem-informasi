@extends('layouts.dashboard')
@section('title', 'Manajemen Sub-CPMK')
@section('breadcrumb', 'Admin / Sub-CPMK')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kemampuan Akhir Tiap Tahapan Belajar (Sub-CPMK)</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola Sub-CPMK sebagai turunan dari CPMK</p>
        </div>
        <div class="flex gap-2">
            <select id="filterCpmk" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white" onchange="filterTable()">
                <option value="">Semua CPMK</option>
                @foreach($cpmks as $c)
                <option value="{{ $c->kode }}">{{ $c->kode }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.sub-cpmk.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                ➕ Tambah Sub-CPMK
            </a>
        </div>
    </div>

    @include('admin._alerts')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm" id="subTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left w-32">Kode Sub-CPMK</th>
                    <th class="px-4 py-3 text-left w-28">CPMK</th>
                    <th class="px-4 py-3 text-left w-20">CPL</th>
                    <th class="px-4 py-3 text-left">Deskripsi</th>
                    <th class="px-4 py-3 text-left">Catatan</th>
                    <th class="px-4 py-3 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($subCpmks as $sub)
                <tr class="hover:bg-gray-50 transition" data-cpmk="{{ $sub->cpmk?->kode }}">
                    <td class="px-4 py-3 font-bold text-indigo-700 text-xs">{{ $sub->kode }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold bg-blue-100 text-blue-700 px-2 py-1 rounded">
                            {{ $sub->cpmk?->kode ?? '–' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-semibold {{ $sub->cpmk?->cpl?->badge_class ?? 'bg-gray-100 text-gray-700' }} px-2 py-1 rounded">
                            {{ $sub->cpmk?->cpl?->kode ?? '–' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-700 text-xs">{{ Str::limit($sub->deskripsi, 80) }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs italic">{{ $sub->catatan ? Str::limit($sub->catatan, 50) : '–' }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.sub-cpmk.edit', $sub) }}"
                                class="text-xs bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-3 py-1 rounded transition">
                                ✏️ Edit
                            </a>
                            <form method="POST" action="{{ route('admin.sub-cpmk.destroy', $sub) }}"
                                onsubmit="return confirm('Hapus Sub-CPMK {{ $sub->kode }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded transition">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 italic">Belum ada data Sub-CPMK</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-3">Total: {{ $subCpmks->count() }} Sub-CPMK</p>
</div>
<script>
    function filterTable() {
        const val = document.getElementById('filterCpmk').value;
        document.querySelectorAll('#subTable tbody tr[data-cpmk]').forEach(tr => {
            tr.style.display = (!val || tr.dataset.cpmk === val) ? '' : 'none';
        });
    }
</script>
@endsection