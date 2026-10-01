@extends('layouts.dashboard')
@section('title', 'Manajemen Pengguna')
@section('breadcrumb', 'Manajemen Pengguna')

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun, role, NIK, dan NUPTK pengguna sistem</p>
        </div>
        <a href="{{ route('users.create') }}"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
            ➕ Tambah Pengguna
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-300 text-green-800 text-sm px-4 py-3 rounded-lg">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-300 text-red-800 text-sm px-4 py-3 rounded-lg">
        ❌ {{ session('error') }}
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Program Studi</th>
                    <th class="px-4 py-3 text-left">Jabatan</th>
                    <th class="px-4 py-3 text-left">NIK</th>
                    <th class="px-4 py-3 text-left">NUPTK</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                        <span class="ml-1 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Anda</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @php
                        $roleColors = [
                        'admin'         => 'bg-red-100 text-red-700',
                        'dekan'         => 'bg-amber-100 text-amber-700',
                        'wakildekan'    => 'bg-yellow-100 text-yellow-700',
                        'kaprodi'       => 'bg-purple-100 text-purple-700',
                        'dosen'         => 'bg-blue-100 text-blue-700',
                        'akademik'      => 'bg-green-100 text-green-700',
                        'kemahasiswaan' => 'bg-orange-100 text-orange-700',
                        'viewer'        => 'bg-gray-100 text-gray-600',
                        ];
                        $roleLabels = [
                        'admin'         => '👑 Admin',
                        'dekan'         => '🏛 Dekan',
                        'wakildekan'    => '🏫 Wakil Dekan',
                        'kaprodi'       => '🎓 Kaprodi',
                        'dosen'         => '📚 Dosen',
                        'akademik'      => '🗂 Akademik',
                        'kemahasiswaan' => '🤝 Kemahasiswaan',
                        'viewer'        => '👁 Viewer',
                        ];
                        @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $roleLabels[$user->role] ?? $user->role }}
                        </span>
                    </td>
                    {{-- Program Studi --}}
                    <td class="px-4 py-3">
                        @if($user->program)
                        <div class="text-xs">
                            <div class="font-medium text-gray-700">{{ $user->program->nama }}</div>
                            <div class="text-gray-400">{{ $user->program->faculty->nama ?? '' }}</div>
                        </div>
                        @else
                        <span class="text-xs text-gray-300 italic">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->jabatan ?? '–' }}</td>
                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $user->nik ?: '–' }}</td>
                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $user->nuptk ?: '–' }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('users.edit', $user) }}"
                                class="text-xs bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-3 py-1 rounded transition">
                                ✏️ Edit
                            </a>
                            @if($user->role === 'dosen')
                            <a href="{{ route('dosen.publikasi.index', $user->id) }}"
                                class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold px-3 py-1 rounded transition">
                                📚 Publikasi
                            </a>
                            @endif
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                onsubmit="return confirm('Hapus pengguna {{ addslashes($user->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="text-xs bg-red-500 hover:bg-red-600 text-white font-semibold px-3 py-1 rounded transition">
                                    🗑 Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-400 italic">Belum ada pengguna</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400 mt-3">Total: {{ $users->count() }} pengguna</p>
</div>
@endsection