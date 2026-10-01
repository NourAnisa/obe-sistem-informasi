@extends('layouts.dashboard')
@section('title', 'Edit Ruangan — ' . $room->code)
@section('breadcrumb', 'Rooms › Edit › ' . $room->code)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

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
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">✏️ Edit Ruangan</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $room->code }} — {{ $room->name }}</p>
        </div>
        <a href="{{ route('rooms.index') }}"
            class="flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

    {{-- ── Room Edit Form ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-brand-600 text-white px-6 py-3 font-semibold text-sm">📋 Data Ruangan</div>

        @if($errors->any())
        <div class="border-b border-red-100 bg-red-50 px-6 py-3">
            <p class="text-sm font-semibold text-red-700 mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside text-xs text-red-600 space-y-0.5">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('rooms.update', $room->id) }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kode <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $room->code) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $room->name) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Gedung</label>
                    <input type="text" name="building" value="{{ old('building', $room->building) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="Nama gedung">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Lantai</label>
                    <input type="number" name="floor" value="{{ old('floor', $room->floor) }}" min="1"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Kapasitas <span class="text-red-500">*</span></label>
                    <input type="number" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe <span class="text-red-500">*</span></label>
                    <select name="type" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        @foreach(['class' => 'Kelas', 'lab' => 'Lab', 'aula' => 'Aula', 'online' => 'Online', 'field' => 'Lapangan'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('type', $room->type) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none">{{ old('description', $room->description) }}</textarea>
            </div>
            <div>
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1"
                        @checked(old('is_active', $room->is_active))
                    class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-700">Ruangan Aktif</span>
                </label>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('rooms.index') }}"
                    class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- ── Block Rules Section ──────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-red-600 text-white px-6 py-3 font-semibold text-sm flex items-center gap-2">
            🚫 Aturan Blokir Ruangan
            <span class="text-xs font-normal opacity-80">— Waktu-waktu di mana ruangan tidak dapat digunakan</span>
        </div>

        {{-- Existing block rules table --}}
        @if($room->blockRules->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-left">
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Hari</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Mulai</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Selesai</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Keterangan</th>
                        <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Hapus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($room->blockRules as $rule)
                    <tr class="hover:bg-gray-50/60">
                        <td class="px-5 py-3 font-medium text-gray-700">{{ $rule->day_of_week }}</td>
                        <td class="px-5 py-3 text-gray-600 font-mono">{{ $rule->start_time }}</td>
                        <td class="px-5 py-3 text-gray-600 font-mono">{{ $rule->end_time }}</td>
                        <td class="px-5 py-3 text-gray-500">{{ $rule->description ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <form action="{{ route('rooms.block-rules.destroy', [$room->id, $rule->id]) }}" method="POST"
                                onsubmit="return confirm('Hapus aturan blokir ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-semibold transition">
                                    🗑 Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-6 text-center text-gray-400 text-sm">
            <p>Belum ada aturan blokir untuk ruangan ini.</p>
        </div>
        @endif

        {{-- Add block rule form --}}
        <div class="border-t border-gray-100 px-6 py-5 bg-gray-50/50">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">Tambah Aturan Blokir</p>
            <form action="{{ route('rooms.block-rules.store', $room->id) }}" method="POST"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Hari <span class="text-red-500">*</span></label>
                    <select name="day_of_week" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Pilih --</option>
                        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan</label>
                    <input type="text" name="description"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="Alasan blokir (opsional)">
                </div>
                <div>
                    <button type="submit"
                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        + Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection