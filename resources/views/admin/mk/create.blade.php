@extends('layouts.dashboard')
@section('title', 'Tambah Mata Kuliah')
@section('breadcrumb', 'Admin / MK / Tambah')
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.mk.index') }}" class="text-gray-400 hover:text-gray-600 text-lg">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Mata Kuliah</h1>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @include('admin._alerts')
        <form method="POST" action="{{ route('admin.mk.store') }}" class="space-y-5">
            @csrf
            @include('admin.mk._form', ['mk' => null])
            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    💾 Simpan Mata Kuliah
                </button>
            </div>
        </form>
    </div>
</div>
@endsection