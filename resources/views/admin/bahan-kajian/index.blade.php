@extends('layouts.dashboard')
@section('title', 'Manajemen Bahan Kajian')
@section('breadcrumb', 'Admin / Bahan Kajian')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bahan Kajian</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola bahan kajian berbasis IS2020 dan CC2020 ACM/AIS</p>
        </div>
        <a href="{{ route('admin.bahan-kajian.create') }}"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            ➕ Tambah Bahan Kajian
        </a>
    </div>

    @include('admin._alerts')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left w-20">Kode</th>
                    @if(auth()->user()->role === 'admin')
                    <th class="px-4 py-3 text-left">Program Studi</th>
                    @endif
                    <th class="px-4 py-3 text-left">Nama Bahan Kajian</th>
                    <th class="px-4 py-3 text-center w-24">Referensi</th>
                    <th class="px-4 py-3 text-center w-16">CPL</th>
                    <th class="px-4 py-3 text-center w-16">MK</th>
                    <th class="px-4 py-3 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($bahanKajians as $bk)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-bold text-unism-primary font-mono">{{ $bk->kode }}</td>
                    @if(auth()->user()->role === 'admin')
                    <td class="px-4 py-3 text-gray-700">
                        {{ $bk->program->jenjang ?? '' }} {{ $bk->program->nama ?? '-' }}
                    </td>
                    @endif
                    <td class="px-4 py-3 text-gray-800">{{ $bk->nama }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-semibold px-2 py-1 rounded
                            {{ $bk->referensi === 'IS2020' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ $bk->referensi }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $bk->cpls_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $bk->mata_kuliahs_count }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.bahan-kajian.edit', $bk) }}"
                                class="text-xs bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-3 py-1 rounded transition">
                                ✏️ Edit
                            </a>
                            <form method="POST" action="{{ route('admin.bahan-kajian.destroy', $bk) }}"
                                onsubmit="return confirm('Hapus Bahan Kajian {{ addslashes($bk->kode) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded transition">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="px-4 py-10 text-center text-gray-400 italic">Belum ada data bahan kajian</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-3">Total: {{ $bahanKajians->count() }} bahan kajian</p>
</div>
@endsection