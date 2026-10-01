@extends('layouts.dashboard')
@section('title', 'Tambah Pengguna')
@section('breadcrumb', 'Manajemen Pengguna / Tambah')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-300 text-red-700 text-sm px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
            @csrf
            @include('users._form', ['user' => null])
            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    💾 Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection