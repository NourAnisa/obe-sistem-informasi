@extends('layouts.dashboard')
@section('title', 'Edit CPMK')
@section('breadcrumb', 'Admin / CPMK / Edit')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.cpmk.index') }}" class="text-gray-400 hover:text-gray-600 text-lg">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Edit CPMK — <span class="text-blue-700">{{ $cpmk->kode }}</span></h1>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @include('admin._alerts')
        <form method="POST" action="{{ route('admin.cpmk.update', $cpmk) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('admin.cpmk._form', ['cpmk' => $cpmk])
            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    💾 Perbarui CPMK
                </button>
            </div>
        </form>
    </div>
</div>
@endsection