@extends('layouts.dashboard')
@section('title', 'Edit Mata Kuliah')
@section('breadcrumb', 'Admin / MK / Edit')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.mk.index') }}" class="text-gray-400 hover:text-gray-600 text-lg">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Edit MK — <span class="text-blue-700">{{ $mataKuliah->kode }}</span></h1>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @include('admin._alerts')
        <form method="POST" action="{{ route('admin.mk.update', $mataKuliah) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('admin.mk._form', ['mk' => $mataKuliah])
            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    💾 Perbarui Mata Kuliah
                </button>
            </div>
        </form>
    </div>
</div>
@endsection