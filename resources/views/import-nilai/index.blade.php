@extends('layouts.dashboard')
@section('title', 'Import Nilai')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📊 Import Nilai Mahasiswa</h1>
            <p class="text-sm text-gray-500 mt-0.5">Tahun Akademik: <strong>{{ $ta }}</strong></p>
        </div>
    </div>

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

    {{-- Bobot Issues Warning --}}
    @if($bobotIssues->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">⚠️ Bobot Penilaian Belum Valid</p>
                <p class="text-xs text-amber-700 mt-1">Beberapa CPMK memiliki total bobot ≠ 100. Perbaiki sebelum import:</p>
                <div class="mt-2 overflow-x-auto">
                    <table class="text-xs text-amber-800">
                        <thead>
                            <tr class="text-left">
                                <th class="pr-4 font-semibold">MK</th>
                                <th class="pr-4 font-semibold">CPMK</th>
                                <th class="font-semibold">Total Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bobotIssues as $issue)
                            <tr>
                                <td class="pr-4">{{ $issue->mk_kode }} — {{ $issue->mk_nama }}</td>
                                <td class="pr-4">{{ $issue->cpmk_kode }}</td>
                                <td class="font-bold {{ $issue->total_bobot == 100 ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $issue->total_bobot }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Import Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-brand-600 text-white px-6 py-3 font-semibold text-sm">📥 Upload File Nilai</div>

        <div class="px-6 py-5">
            <form action="{{ route('import-nilai.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Mata Kuliah --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            Mata Kuliah <span class="text-red-500">*</span>
                        </label>
                        <select name="mk_id" id="mk_select" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($mataKuliahs as $mk)
                            <option value="{{ $mk->id }}"
                                @selected(old('mk_id') == $mk->id)
                                data-cpmk="{{ json_encode($cpmkByMk[$mk->id] ?? []) }}">
                                [Sem {{ $mk->semester }}] {{ $mk->kode }} — {{ $mk->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun Akademik --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun Akademik</label>
                        <input type="text" name="ta" value="{{ old('ta', $ta) }}"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            placeholder="2025/2026">
                    </div>
                </div>

                {{-- CPMK Preview --}}
                <div id="cpmk_preview" class="hidden">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">CPMK pada MK ini</label>
                    <div id="cpmk_list" class="flex flex-wrap gap-2 p-3 bg-blue-50 rounded-lg border border-blue-100">
                    </div>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        File Excel (.xlsx) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file" required accept=".xlsx,.xls"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <p class="text-xs text-gray-400 mt-1">
                        Format: kolom NIM, Nama, lalu satu kolom per CPMK (nilai 0–100).
                        <a id="download_template" href="#" class="text-brand-600 hover:underline hidden">Download Template →</a>
                    </p>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        📊 Import Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sync OBE --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-purple-600 text-white px-6 py-3 font-semibold text-sm">🔄 Sinkronisasi OBE</div>
        <div class="px-6 py-5">
            <p class="text-sm text-gray-600 mb-4">
                Setelah import nilai, jalankan sinkronisasi untuk menghitung pencapaian CPL/CPMK secara otomatis.
            </p>
            <form action="{{ route('sync-obe') }}" method="POST" id="sync_form">
                @csrf
                <input type="hidden" name="mkId" id="sync_mk_id" value="">
                <div class="flex items-center gap-3">
                    <select id="sync_mk_select"
                        class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Pilih MK untuk Sync --</option>
                        @foreach($mataKuliahs as $mk)
                        <option value="{{ $mk->id }}">[Sem {{ $mk->semester }}] {{ $mk->kode }} — {{ $mk->nama }}</option>
                        @endforeach
                    </select>
                    <button type="submit" onclick="document.getElementById('sync_mk_id').value = document.getElementById('sync_mk_select').value"
                        class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold shadow-sm transition whitespace-nowrap">
                        🔄 Sync OBE
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
(function () {
    const select = document.getElementById('mk_select');
    const preview = document.getElementById('cpmk_preview');
    const list = document.getElementById('cpmk_list');
    const templateLink = document.getElementById('download_template');

    select.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const cpmks = JSON.parse(opt.dataset.cpmk || '[]');
        const mkId = this.value;

        if (cpmks.length > 0) {
            list.innerHTML = cpmks.map(c =>
                `<span class="px-2.5 py-1 rounded-full text-xs font-mono font-medium bg-blue-100 text-blue-700">${c}</span>`
            ).join('');
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }

        if (mkId) {
            templateLink.href = "{{ route('import-nilai.template', ['mkId' => '__MK__']) }}".replace('__MK__', mkId);
            templateLink.classList.remove('hidden');
        } else {
            templateLink.classList.add('hidden');
        }
    });
})();
</script>
@endsection