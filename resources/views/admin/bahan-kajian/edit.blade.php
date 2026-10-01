@extends('layouts.dashboard')
@section('title', 'Edit Bahan Kajian')
@section('breadcrumb', 'Admin / Bahan Kajian / Edit')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.bahan-kajian.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Kembali</a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Bahan Kajian <span class="text-blue-600">{{ $bahanKajian->kode }}</span></h1>
    </div>

    @include('admin._alerts')

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.bahan-kajian.update', $bahanKajian) }}" class="space-y-5">
            @csrf @method('PUT')
            @php $bk = $bahanKajian; @endphp
            @include('admin.bahan-kajian._form')
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2 rounded-lg transition">
                    💾 Perbarui
                </button>
                <a href="{{ route('admin.bahan-kajian.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold text-sm px-6 py-2 rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection