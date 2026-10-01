@extends('layouts.public')
@section('title','MBKM & BKP')
@section('content')

@include('components.program-switcher', ['currentRoute' => 'mbkm.index'])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">MBKM & Bentuk Kegiatan Pembelajaran</h1>
        <p class="text-gray-500 mt-1">Merdeka Belajar Kampus Merdeka · Maks 20 SKS · {{ $program?->nama ?? config('obe.prodi') }}</p>
    </div>


    {{-- Info card --}}
    <div class="bg-unism-light border border-blue-200 rounded-xl p-5 mb-8 flex items-start space-x-4">
        <span class="text-3xl">🎓</span>
        <div>
            <h2 class="font-bold text-unism-primary">Kebijakan MBKM</h2>
            <p class="text-sm text-gray-700 mt-1">Mahasiswa dapat mengambil kegiatan pembelajaran di luar program studi (BKP Luar Prodi) hingga <strong>20 SKS</strong> yang dapat dikonversikan ke dalam mata kuliah kurikulum. Kegiatan dirancang untuk memperkuat kompetensi nyata dan kesiapan kerja.</p>
        </div>
    </div>

    {{-- BKP Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden mb-12">
        <h2 class="text-xl font-bold text-gray-800 p-5 border-b border-gray-100">8 Bentuk Kegiatan Pembelajaran (BKP) MBKM</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-unism-primary text-white">
                    <tr>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-12">No</th>
                        <th class="px-4 py-3 text-left font-semibold border border-unism-secondary">Bentuk Kegiatan</th>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-20">SKS Maks</th>
                        <th class="px-4 py-3 text-left font-semibold border border-unism-secondary">Deskripsi</th>
                        <th class="px-4 py-3 text-left font-semibold border border-unism-secondary">Konversi MK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bkps as $bkp)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="px-4 py-3 text-center font-bold text-unism-primary">{{ $bkp->no }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $bkp->bentuk_kegiatan }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge bg-orange-100 text-orange-700">{{ $bkp->sks_mbkm_maks }} SKS</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $bkp->deskripsi }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $bkp->konversi_mk }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Distribusi per Semester --}}
    <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Distribusi Jenis MK per Semester</h2>
        <table class="min-w-full text-xs border-collapse">
            <thead class="bg-unism-primary text-white">
                <tr>
                    <th class="px-3 py-2 font-semibold border border-unism-secondary text-left">Jenis MK</th>
                    @for($s=1;$s<=8;$s++)
                        <th class="px-3 py-2 font-semibold border border-unism-secondary text-center">Smt {{ $s }}</th>
                        @endfor
                </tr>
            </thead>
            <tbody>
                @foreach(['MKF','MKPU','MKWK','MKP','MKPP','MKKP'] as $kat)
                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                    <td class="px-3 py-2 font-semibold border border-gray-200">{{ $kat }}</td>
                    @foreach($distribusi as $sem => $groups)
                    <td class="px-3 py-2 border border-gray-200 text-center">
                        @if($groups[$kat]->isNotEmpty())
                        <div class="space-y-0.5">
                            @foreach($groups[$kat] as $mk)
                            <a href="{{ route('mata-kuliah.show', $mk->kode) }}" class="block text-unism-primary hover:underline whitespace-nowrap">{{ $mk->sks }}S</a>
                            @endforeach
                        </div>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection