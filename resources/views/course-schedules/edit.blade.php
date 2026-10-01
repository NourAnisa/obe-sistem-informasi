@extends('layouts.dashboard')
@section('title', 'Edit Jadwal Kuliah')
@section('breadcrumb', 'Course Schedules › Edit Jadwal')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-display">✏️ Edit Jadwal Kuliah</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $courseSchedule->mataKuliah->kode ?? '-' }} — {{ $courseSchedule->day_of_week }},
                {{ $courseSchedule->start_time }}–{{ $courseSchedule->end_time }}
            </p>
        </div>
        <a href="{{ route('course-schedules.index') }}"
            class="flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

    {{-- Edit Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-brand-600 text-white px-6 py-3 font-semibold text-sm">📋 Data Jadwal</div>

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

        <form action="{{ route('course-schedules.update', $courseSchedule->id) }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Mata Kuliah <span class="text-red-500">*</span></label>
                <select name="mata_kuliah_id" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <option value="">-- Pilih Mata Kuliah --</option>
                    @foreach($mataKuliahs as $mk)
                    <option value="{{ $mk->id }}"
                        @selected(old('mata_kuliah_id', $courseSchedule->mata_kuliah_id) == $mk->id)>
                        {{ $mk->kode }} — {{ $mk->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Kelas</label>
                    <input type="text" name="class_name"
                        value="{{ old('class_name', $courseSchedule->class_name) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="cth. A, B, Reg (opsional)">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Ruangan</label>
                    <select name="room_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Belum Ditentukan --</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}"
                            @selected(old('room_id', $courseSchedule->room_id) == $room->id)>
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
                    <option value="{{ $day }}"
                        @selected(old('day_of_week', $courseSchedule->day_of_week) === $day)>
                        {{ $day }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                    <input type="time" name="start_time"
                        value="{{ old('start_time', $courseSchedule->start_time) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                    <input type="time" name="end_time"
                        value="{{ old('end_time', $courseSchedule->end_time) }}" required
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
                            <option value="{{ $s }}"
                            @selected(old('semester', $courseSchedule->semester) == $s)>
                            Semester {{ $s }}
                            </option>
                            @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Akademik <span class="text-red-500">*</span></label>
                    <input type="text" name="academic_year"
                        value="{{ old('academic_year', $courseSchedule->academic_year) }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="2025/2026">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('course-schedules.index') }}"
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

</div>
@endsection