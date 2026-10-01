@extends('layouts.dashboard')
@section('title', 'Input Nilai Mahasiswa')
@section('breadcrumb', 'Input Nilai Mahasiswa')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">Input Nilai Mahasiswa</h1>
            <p class="text-gray-500 text-sm mt-0.5">
                Nilai per komponen untuk evaluasi otomatis laporan
                <span class="ml-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                    🟢 TA {{ $ta }}
                </span>
            </p>
        </div>
        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('import-nilai.index') }}"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow transition flex items-center gap-1.5">
                📥 Import Excel
            </a>
            {{-- TA filter --}}
            <form method="GET" class="flex items-center gap-2">
                <select name="ta" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500">
                    @foreach(['2025/2026','2024/2025','2023/2024'] as $opt)
                    <option value="{{ $opt }}" {{ $ta === $opt ? 'selected' : '' }}>TA {{ $opt }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
        <span>✅</span> {{ session('success') }}
    </div>
    @endif

    @if($mataKuliahs->isEmpty())
    <div class="text-center py-20 text-gray-400">
        <div class="text-6xl mb-4">📊</div>
        <p class="font-semibold text-lg">Belum ada mata kuliah aktif</p>
        <p class="text-sm mt-1">Mahasiswa belum mendaftarkan KRS pada TA {{ $ta }}</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($mataKuliahs as $mk)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
            <div class="h-1.5 {{ $mk->persen_input >= 100 ? 'bg-green-500' : ($mk->persen_input > 0 ? 'bg-amber-400' : 'bg-gray-200') }}"></div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div>
                        <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-full">{{ $mk->kode }}</span>
                        <h3 class="font-semibold text-gray-800 mt-1.5 leading-tight">{{ $mk->nama }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Semester {{ $mk->semester }} · {{ $mk->sks }} SKS</p>
                    </div>
                    @if($mk->persen_input >= 100)
                    <span class="text-green-600 text-lg flex-shrink-0">✅</span>
                    @elseif($mk->persen_input > 0)
                    <span class="text-amber-500 text-lg flex-shrink-0">⏳</span>
                    @else
                    <span class="text-gray-300 text-lg flex-shrink-0">📝</span>
                    @endif
                </div>

                {{-- Progress bar --}}
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>{{ $mk->sudah_input }}/{{ $mk->total_mahasiswa }} mahasiswa</span>
                        <span class="font-semibold {{ $mk->persen_input >= 100 ? 'text-green-600' : '' }}">
                            {{ $mk->persen_input }}%
                        </span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all
                            {{ $mk->persen_input >= 100 ? 'bg-green-500' : ($mk->persen_input > 0 ? 'bg-amber-400' : 'bg-gray-300') }}"
                            style="width: {{ $mk->persen_input }}%"></div>
                    </div>
                </div>

                <a href="{{ route('nilai-mahasiswa.show', ['mk' => $mk->id, 'ta' => $ta]) }}"
                    class="block w-full text-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition">
                    {{ $mk->total_mahasiswa === 0 ? '👁 Lihat' : ($mk->sudah_input === 0 ? '📝 Input Nilai' : '✏️ Edit Nilai') }}
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection