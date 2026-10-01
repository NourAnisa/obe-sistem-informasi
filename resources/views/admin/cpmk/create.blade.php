@extends('layouts.dashboard')
@section('title', 'Tambah CPMK')
@section('breadcrumb', 'Admin / CPMK / Tambah')
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.cpmk.index') }}" class="text-gray-400 hover:text-gray-600 text-lg">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah CPMK</h1>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @include('admin._alerts')
        <form method="POST" action="{{ route('admin.cpmk.store') }}" class="space-y-5">
            @csrf
            @include('admin.cpmk._form', ['cpmk' => null])
            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    💾 Simpan CPMK
                </button>
            </div>
        </form>
    </div>
</div>
@endsection