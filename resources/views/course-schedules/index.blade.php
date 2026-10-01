@extends('layouts.dashboard')
@section('title', 'Jadwal Kuliah')
@section('breadcrumb', 'Course Schedules › Jadwal Kuliah')

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
            <h1 class="text-2xl font-bold text-gray-800 font-display">📅 Jadwal Kuliah</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manajemen jadwal perkuliahan — TA {{ $ta }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- TA Filter --}}
            <form method="GET" class="flex items-center gap-2">
                <label class="text-xs text-gray-500">TA:</label>
                <select name="ta" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-brand-500">
                    @foreach(['2025/2026','2024/2025','2023/2024'] as $year)
                    <option value="{{ $year }}" @selected($year===$ta)>{{ $year }}</option>
                    @endforeach
                </select>
                @foreach(request()->except('ta') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
            </form>
            <a href="{{ route('rooms.analytics.dashboard') }}"
                class="px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
                📊 Analytics
            </a>
            <button onclick="document.getElementById('modal-add-schedule').classList.remove('hidden')"
                class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                + Tambah Jadwal
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('course-schedules.index') }}"
        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-end gap-3">
        <input type="hidden" name="ta" value="{{ $ta }}">
        <div class="flex flex-col gap-1 min-w-[160px]">
            <label class="text-xs font-medium text-gray-500">Hari</label>
            <select name="day_of_week" onchange="this.form.submit()"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                <option value="">Semua Hari</option>
                @foreach($days as $day)
                <option value="{{ $day }}" @selected(request('day_of_week')===$day)>{{ $day }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col gap-1 min-w-[140px]">
            <label class="text-xs font-medium text-gray-500">Semester</label>
            <select name="semester" onchange="this.form.submit()"
                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-brand-500">
                <option value="">Semua Semester</option>
                @for($s = 1; $s <= 8; $s++)
                    <option value="{{ $s }}" @selected(request('semester')==$s)>Semester {{ $s }}</option>
                    @endfor
            </select>
        </div>
        @if(request()->hasAny(['day_of_week','semester']))
        <a href="{{ route('course-schedules.index', ['ta' => $ta]) }}"
            class="text-xs text-red-500 hover:underline self-end pb-2">✕ Reset</a>
        @endif
    </form>

    {{-- ── Schedule by Day ─────────────────────────────────────────── --}}
    @if($byDay->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 py-16 text-center text-gray-400">
        <div class="text-5xl mb-3">📅</div>
        <p class="font-semibold text-gray-600 text-lg">Belum ada jadwal kuliah</p>
        <p class="text-sm mt-1">Tambahkan jadwal dengan tombol "+ Tambah Jadwal" di atas.</p>
    </div>
    @else
    <div class="space-y-5">
        @foreach($byDay as $day => $schedules)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Day Header --}}
            <div class="bg-brand-700 text-white px-5 py-3 flex items-center justify-between">
                <span class="font-bold font-display text-base">📆 {{ $day }}</span>
                <span class="text-xs bg-white/20 rounded-full px-2.5 py-0.5">{{ $schedules->count() }} jadwal</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-left">
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Mata Kuliah</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ruangan</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Semester</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($schedules as $schedule)
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs text-brand-700 font-semibold">{{ $schedule->mataKuliah->kode ?? '-' }}</span>
                                <span class="block text-gray-700 font-medium text-xs mt-0.5">{{ $schedule->mataKuliah->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $schedule->class_name ?: '-' }}</td>
                            <td class="px-5 py-3.5">
                                @if($schedule->room)
                                <span class="font-mono text-xs font-semibold text-gray-700">{{ $schedule->room->code }}</span>
                                <span class="block text-xs text-gray-500 mt-0.5">{{ $schedule->room->name }}</span>
                                @else
                                <span class="text-gray-400 text-xs italic">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 font-mono text-xs">
                                {{ $schedule->start_time }} – {{ $schedule->end_time }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700">
                                    Sem {{ $schedule->semester }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('course-schedules.edit', $schedule->id) }}"
                                        class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold transition">
                                        ✏️ Edit
                                    </a>
                                    <form action="{{ route('course-schedules.destroy', $schedule->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus jadwal ini?')">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- ── Add Schedule Modal ───────────────────────────────────────────── --}}
<div id="modal-add-schedule" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 font-display">Tambah Jadwal Kuliah</h2>
            <button onclick="document.getElementById('modal-add-schedule').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 transition text-2xl leading-none">&times;</button>
        </div>
        <form action="{{ route('course-schedules.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Mata Kuliah <span class="text-red-500">*</span></label>
                <select name="mata_kuliah_id" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <option value="">-- Pilih Mata Kuliah --</option>
                    @foreach($mataKuliahs as $mk)
                    <option value="{{ $mk->id }}" @selected(old('mata_kuliah_id')==$mk->id)>
                        {{ $mk->kode }} — {{ $mk->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Kelas</label>
                    <input type="text" name="class_name" value="{{ old('class_name') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="cth. A, B, Reg (opsional)">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Ruangan</label>
                    <select name="room_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('room_id')==$room->id)>
                            {{ $room->code }} — {{ $room->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Hari <span class="text-red-500">*</span></label>
                <select name="day_of_week" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <option value="">-- Pilih Hari --</option>
                    @foreach($days as $day)
                    <option value="{{ $day }}" @selected(old('day_of_week')===$day)>{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Semester <span class="text-red-500">*</span></label>
                    <select name="semester" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Pilih --</option>
                        @for($s = 1; $s <= 8; $s++)
                            <option value="{{ $s }}" @selected(old('semester')==$s)>Semester {{ $s }}</option>
                            @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
                    <input type="text" name="academic_year" value="{{ old('academic_year', $ta) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="2025/2026">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('modal-add-schedule').classList.add('hidden')"
                    class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if($errors->any())
    document.getElementById('modal-add-schedule').classList.remove('hidden');
    @endif

    document.getElementById('modal-add-schedule').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
</script>
@endpush