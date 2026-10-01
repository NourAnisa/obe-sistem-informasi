@extends('layouts.dashboard')
@section('title', 'Berita Acara Perkuliahan')
@section('content')

{{-- Hero Header --}}
<div class="bg-gradient-to-r from-blue-800 to-indigo-900 py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center space-x-3 text-blue-300 text-sm mb-3">
            <a href="{{ route('dashboard') }}" class="hover:text-white">Beranda</a>
            <span>›</span><span class="text-white">Berita Acara Perkuliahan</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold text-white">📋 Berita Acara Perkuliahan</h1>
                <p class="text-blue-200 mt-2">Buat dan kelola BAP per mata kuliah. Data materi dan jadwal diambil otomatis dari RPS.</p>
            </div>
            <div>
                <span class="bg-white/10 ring-1 ring-white/20 px-4 py-2 rounded-lg text-white text-sm">
                    16 Pertemuan · Print · Export Word
                </span>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4 flex items-start space-x-3">
        <span class="text-green-500 text-xl">✅</span>
        <p class="text-green-800 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @foreach($mataKuliahs as $semester => $mks)
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                <span class="text-white font-black font-display">{{ $semester }}</span>
            </div>
            <div>
                <h2 class="font-display font-bold text-slate-800 text-lg">Semester {{ $semester }}</h2>
                <p class="text-slate-400 text-xs">{{ $mks->count() }} Mata Kuliah</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Kode</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama Mata Kuliah</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide w-16">SKS</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Dosen PJMK</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide">Status BAP</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wide w-56">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($mks as $mk)
                    @php $hasBap = in_array($mk->id, $bapIds); @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs font-semibold bg-blue-50 text-blue-700 px-2 py-1 rounded-lg">{{ $mk->kode }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-semibold text-slate-700">{{ $mk->nama }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-bold text-slate-600">{{ $mk->sks }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $mk->pjmk ?: '-' }}</td>
                        <td class="px-5 py-3.5 text-center">
                            @if($hasBap)
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">✅ Ada BAP</span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-slate-100 text-slate-500 px-2.5 py-1 rounded-full">— Belum dibuat</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('bap.show', $mk->kode) }}"
                                    class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    {{ $hasBap ? '✏️ Edit' : '➕ Buat' }} BAP
                                </a>
                                @if($hasBap)
                                <a href="{{ route('bap.word', $mk->kode) }}"
                                    class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    📝 Word
                                </a>
                                <a href="{{ route('bap.print', $mk->kode) }}" target="_blank"
                                    class="inline-flex items-center gap-1 bg-slate-600 hover:bg-slate-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    🖨️ Print
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

</div>
@endsection