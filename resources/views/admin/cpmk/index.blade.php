@extends('layouts.dashboard')
@section('title', 'Manajemen CPMK')
@section('breadcrumb', 'Admin / CPMK')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Capaian Pembelajaran Mata Kuliah (CPMK)</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola CPMK yang merupakan turunan dari CPL</p>
        </div>
        <div class="flex gap-2">
            <select id="filterCpl" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white" onchange="filterTable()">
                <option value="">Semua CPL</option>
                @foreach($cpls as $c)
                <option value="{{ $c->kode }}">{{ $c->kode }}</option>
                @endforeach
            </select>
            <a href="{{ route('admin.cpmk.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                ➕ Tambah CPMK
            </a>
        </div>
    </div>

    @include('admin._alerts')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm" id="cpmkTable">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left w-28">Kode CPMK</th>
                    <th class="px-4 py-3 text-left w-20">CPL</th>
                    <th class="px-4 py-3 text-left">Deskripsi</th>
                    <th class="px-4 py-3 text-left w-40">Bahan Kajian</th>
                    <th class="px-4 py-3 text-center w-20">Sub-CPMK</th>
                    <th class="px-4 py-3 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($cpmks as $cpmk)
                <tr class="hover:bg-gray-50 transition" data-cpl="{{ $cpmk->cpl?->kode }}">
                    <td class="px-4 py-3 font-bold text-blue-700">{{ $cpmk->kode }}</td>
                    <td class="px-4 py-3">
                        @if($cpmk->cpl)
                        <span class="text-xs font-semibold px-2 py-1 rounded {{ $cpmk->cpl->badge_class }}">
                            {{ $cpmk->cpl->kode }}
                        </span>
                        @else <span class="text-gray-400">–</span> @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ Str::limit($cpmk->deskripsi, 90) }}</td>
                    <td class="px-4 py-3">
                        @if($cpmk->cpl && $cpmk->cpl->bahanKajians->count())
                        <div class="flex flex-wrap gap-1">
                            @foreach($cpmk->cpl->bahanKajians as $bk)
                            <span class="text-[10px] bg-gray-100 text-gray-600 border border-gray-200 rounded px-1.5 py-0.5 font-mono">{{ $bk->kode }}</span>
                            @endforeach
                        </div>
                        @else
                        <span class="text-gray-300 text-xs">–</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $cpmk->subCpmks->count() }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.cpmk.edit', $cpmk) }}"
                                class="text-xs bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-3 py-1 rounded transition">
                                ✏️ Edit
                            </a>
                            <form method="POST" action="{{ route('admin.cpmk.destroy', $cpmk) }}"
                                onsubmit="return confirm('Hapus CPMK {{ $cpmk->kode }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded transition">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 italic">Belum ada data CPMK</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-3">Total: {{ $cpmks->count() }} CPMK</p>
</div>

<script>
    function filterTable() {
        const val = document.getElementById('filterCpl').value;
        document.querySelectorAll('#cpmkTable tbody tr[data-cpl]').forEach(tr => {
            tr.style.display = (!val || tr.dataset.cpl === val) ? '' : 'none';
        });
    }
</script>
@endsection