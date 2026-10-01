@extends('layouts.dashboard')
@section('title', 'Edit Sub-CPMK')
@section('breadcrumb', 'Admin / Sub-CPMK / Edit')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.sub-cpmk.index') }}" class="text-gray-400 hover:text-gray-600 text-lg">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Sub-CPMK — <span class="text-indigo-700">{{ $subCpmk->kode }}</span></h1>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @include('admin._alerts')
        <form method="POST" action="{{ route('admin.sub-cpmk.update', $subCpmk) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('admin.sub-cpmk._form', ['subCpmk' => $subCpmk])
            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    💾 Perbarui Sub-CPMK
                </button>
            </div>
        </form>
    </div>
</div>
@endsection