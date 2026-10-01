@extends('layouts.public')
@section('title','Profil Lulusan')
@section('content')

@include('components.program-switcher', ['currentRoute' => 'profil-lulusan.index'])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Profil Lulusan</h1>
        <p class="text-gray-500 mt-1">{{ $program?->jenjang }} {{ $program?->nama ?? config('obe.prodi') }} &middot; {{ $profilLulusans->count() }} Profil Lulusan</p>
    </div>


    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        @foreach($profilLulusans as $pl)
        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden" x-data="{ expanded: false }">
            <div class="bg-gradient-to-r from-unism-primary to-unism-accent p-6 flex items-start space-x-4">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shrink-0">
                    <span class="text-unism-primary font-bold text-xl">{{ $pl->kode }}</span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-xl">{{ $pl->kode }}</h2>
                    <p class="text-blue-100 text-sm mt-1 leading-relaxed">{{ $pl->deskripsi }}</p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @if($pl->hard_skills)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Hard Skills</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $pl->hard_skills) as $skill)
                        <span class="badge bg-blue-100 text-blue-800">{{ trim($skill) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($pl->soft_skills)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Soft Skills</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $pl->soft_skills) as $skill)
                        <span class="badge bg-green-100 text-green-800">{{ trim($skill) }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">CPL yang Menunjang</h3>
                    <div class="flex flex-wrap gap-1">
                        @foreach($pl->cpls as $cpl)
                        <a href="{{ route('cpl.index') }}#{{ $cpl->kode }}" class="badge bg-orange-100 text-orange-800 hover:bg-orange-200 transition">{{ $cpl->kode }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection