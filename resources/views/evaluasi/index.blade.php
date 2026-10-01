@extends('layouts.dashboard')
@section('title','Evaluasi Ketercapaian CPL')
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Evaluasi Ketercapaian CPL</h1>
        <p class="text-gray-500 mt-1">Cari mahasiswa untuk melihat capaian CPL & CPMK</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <form method="POST" action="{{ route('evaluasi.cari') }}" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIM Mahasiswa</label>
                <input type="text" name="nim" value="{{ $nim ?? '' }}" placeholder="2023xxxxxx" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Angkatan</label>
                <select name="angkatan" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @for($y = 2025; $y >= 2018; $y--)
                    <option value="{{ $y }}" {{ isset($angkatan) && $angkatan == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">Cari</button>
        </form>
    </div>

    @if(isset($hasil) && count($hasil) > 0)
    <div class="space-y-6">
        @foreach($hasil as $item)
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">{{ $item['mahasiswa']->nama }}</h3>
                    <p class="text-sm text-gray-500">NIM: {{ $item['mahasiswa']->nim }} · Angkatan: {{ $item['mahasiswa']->angkatan ?? '-' }}</p>
                </div>
                @if($item['avg_cpl'] !== null)
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $item['lulus_cpl'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $item['lulus_cpl'] ? '✅ Lulus CPL' : '⚠️ Belum Lulus' }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">Rata-rata CPL: {{ number_format($item['avg_cpl'], 2) }}</p>
                </div>
                @endif
            </div>

            @if($item['cpl']->count())
            <div class="px-6 py-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Capaian CPL</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($item['cpl'] as $cpl)
                    <div class="border rounded-lg p-3 text-center {{ $cpl->lulus ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                        <p class="text-xs font-bold text-gray-600">{{ $cpl->kode }}</p>
                        <p class="text-lg font-bold {{ $cpl->lulus ? 'text-green-700' : 'text-red-700' }}">{{ number_format($cpl->nilai, 1) }}</p>
                        <span class="text-xs {{ $cpl->lulus ? 'text-green-600' : 'text-red-600' }}">{{ $cpl->lulus ? 'Lulus' : 'Belum' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($item['cpmk']->count())
            <div class="px-6 py-4 border-t">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Capaian CPMK</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($item['cpmk'] as $cpmk)
                    <div class="border rounded-lg p-2 text-center {{ $cpmk->lulus ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200' }}">
                        <p class="text-xs font-bold text-gray-600">{{ $cpmk->kode }}</p>
                        <p class="text-sm font-bold {{ $cpmk->lulus ? 'text-green-700' : 'text-yellow-700' }}">{{ number_format($cpmk->nilai, 1) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($item['cpl']->isEmpty() && $item['cpmk']->isEmpty())
            <div class="px-6 py-8 text-center text-gray-400">
                <p>Belum ada data capaian CPL/CPMK untuk mahasiswa ini.</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @elseif(isset($hasil))
    <div class="text-center py-12 text-gray-400 bg-white rounded-xl shadow">
        <p class="text-5xl mb-4">🔍</p>
        <p class="text-lg font-medium text-gray-600">Tidak ada mahasiswa ditemukan</p>
        @if(isset($nim))
        <p class="text-sm mt-2">NIM: <strong>{{ $nim }}</strong></p>
        @endif
    </div>
    @endif
</div>
@endsection