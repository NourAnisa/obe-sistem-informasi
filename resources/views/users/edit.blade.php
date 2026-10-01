@extends('layouts.dashboard')
@section('title', 'Edit Pengguna')
@section('breadcrumb', 'Manajemen Pengguna / Edit')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600">←</a>
        <h1 class="text-2xl font-bold text-gray-800">Edit Pengguna</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-300 text-red-700 text-sm px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('users._form', ['user' => $user])

            {{-- Password (opsional saat edit) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password Baru <span class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                </label>
                <input type="password" name="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="Min. 6 karakter">
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
                    💾 Perbarui Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection