@extends('layouts.dashboard')
@section('title', 'Daftar Ruangan')
@section('breadcrumb', 'Rooms › Daftar Ruangan')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 01-2 0V9zm1-5.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" clip-rule="evenodd" />
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">🏢 Daftar Ruangan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola ruangan kuliah, lab, dan fasilitas — TA {{ $ta }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('rooms.analytics.dashboard') }}"
                class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
                📊 Analytics
            </a>
            <button onclick="document.getElementById('modal-add-room').classList.remove('hidden')"
                class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                + Tambah Ruangan
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('rooms.index') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-end gap-3">
        <div class="flex flex-col gap-1 min-w-[140px]">
            <label class="text-xs font-medium text-gray-500">Tipe Ruangan</label>
            <select name="type" onchange="this.form.submit()"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                <option value="">Semua Tipe</option>
                @foreach(['class' => 'Kelas', 'lab' => 'Lab', 'aula' => 'Aula', 'online' => 'Online', 'field' => 'Lapangan'] as $val => $label)
                <option value="{{ $val }}" @selected(request('type')===$val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1 min-w-[160px]">
            <label class="text-xs font-medium text-gray-500">Gedung</label>
            <select name="building" onchange="this.form.submit()"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                <option value="">Semua Gedung</option>
                @foreach($buildings as $b)
                <option value="{{ $b }}" @selected(request('building')===$b)>{{ $b }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-gray-500">Status</label>
            <div class="flex items-center gap-3 text-sm">
                @foreach(['' => 'Semua', '1' => 'Aktif', '0' => 'Nonaktif'] as $val => $label)
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" name="active" value="{{ $val }}"
                        @checked(request('active', '' )===$val)
                        onchange="this.form.submit()"
                        class="text-brand-600 focus:ring-brand-500">
                    <span>{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @if(request()->hasAny(['type','building','active']))
        <a href="{{ route('rooms.index') }}" class="text-xs text-red-500 hover:underline self-end pb-2">✕ Reset</a>
        @endif
    </form>

    {{-- Rooms Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($errors->any())
        <div class="border-b border-red-100 bg-red-50 px-5 py-3">
            <p class="text-sm font-semibold text-red-700 mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside text-xs text-red-600 space-y-0.5">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kode</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nama</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Gedung</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Lantai</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kapasitas</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tipe</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jadwal</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rooms as $i => $room)
                    @php
                    $typeBadge = match($room->type) {
                    'class' => ['bg-blue-100 text-blue-700', 'Kelas'],
                    'lab' => ['bg-purple-100 text-purple-700','Lab'],
                    'aula' => ['bg-amber-100 text-amber-700', 'Aula'],
                    'online' => ['bg-cyan-100 text-cyan-700', 'Online'],
                    'field' => ['bg-green-100 text-green-700', 'Lapangan'],
                    default => ['bg-gray-100 text-gray-700', ucfirst($room->type ?? '-')],
                    };
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-5 py-3.5 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-3.5 font-mono font-semibold text-gray-800">{{ $room->code }}</td>
                        <td class="px-5 py-3.5 text-gray-700 font-medium">{{ $room->name }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $room->building ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-gray-600 text-center">{{ $room->floor ?? '-' }}</td>
                        <td class="px-5 py-3.5 text-gray-600 text-center">{{ $room->capacity }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeBadge[0] }}">
                                {{ $typeBadge[1] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($room->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="font-semibold text-brand-600">{{ $room->course_schedules_count ?? 0 }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('rooms.edit', $room->id) }}"
                                    class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold transition">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('rooms.destroy', $room->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus ruangan {{ addslashes($room->name) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition">
                                        🗑 Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-12 text-center text-gray-400">
                            <div class="text-4xl mb-2">🏢</div>
                            <p class="font-medium">Belum ada ruangan</p>
                            <p class="text-xs mt-1">Tambah ruangan pertama dengan tombol di atas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rooms instanceof \Illuminate\Pagination\LengthAwarePaginator && $rooms->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $rooms->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>

{{-- ── Add Room Modal ───────────────────────────────────────────────── --}}
<div id="modal-add-room" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 font-display">Tambah Ruangan</h2>
            <button onclick="document.getElementById('modal-add-room').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 transition text-2xl leading-none">&times;</button>
        </div>
        <form action="{{ route('rooms.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kode <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="cth. RK-101">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="cth. Ruang Kuliah 101">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Gedung</label>
                    <input type="text" name="building" value="{{ old('building') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        list="building-list" placeholder="Nama gedung">
                    <datalist id="building-list">
                        @foreach($buildings as $b)
                        <option value="{{ $b }}">
                            @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Lantai</label>
                    <input type="number" name="floor" value="{{ old('floor') }}" min="1"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="1">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kapasitas <span class="text-red-500">*</span></label>
                    <input type="number" name="capacity" value="{{ old('capacity') }}" min="1" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe <span class="text-red-500">*</span></label>
                    <select name="type" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Pilih Tipe --</option>
                        @foreach(['class' => 'Kelas', 'lab' => 'Lab', 'aula' => 'Aula', 'online' => 'Online', 'field' => 'Lapangan'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('type')===$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="2"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
                    placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                        class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-700">Ruangan Aktif</span>
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-add-room').classList.add('hidden')"
                    class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    Simpan Ruangan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Re-open modal if validation errors exist
    @if($errors->any())
    document.getElementById('modal-add-room').classList.remove('hidden');
    @endif

    // Close modal on backdrop click
    document.getElementById('modal-add-room').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
@endpush