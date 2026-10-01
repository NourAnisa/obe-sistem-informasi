@extends('layouts.dashboard')
@section('title', 'Tambah Evidence')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📝 Tambah Evidence</h1>
            <p class="text-sm text-gray-500 mt-0.5">Unggah bukti pencapaian CPMK Anda</p>
        </div>
        <a href="{{ route('portfolio.evidences.index') }}"
            class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-brand-600 text-white px-6 py-3 font-semibold text-sm">📋 Data Evidence</div>

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

        <form action="{{ route('portfolio.evidences.store') }}" method="POST" enctype="multipart/form-data"
            class="px-6 py-5 space-y-5">
            @csrf

            {{-- Title --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Judul Evidence <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                    placeholder="Contoh: Database Design Assignment">
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
                    placeholder="Jelaskan isi dan tujuan evidence ini...">{{ old('description') }}</textarea>
            </div>

            {{-- Evidence Type --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Tipe Evidence <span class="text-red-500">*</span>
                </label>
                <select name="evidence_type" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <option value="">-- Pilih Tipe --</option>
                    @foreach($evidenceTypes as $type)
                    @php
                    $labels = [
                        'assignment'    => '📝 Tugas / Assignment',
                        'project'       => '📊 Proyek',
                        'certification' => '🏆 Sertifikasi',
                        'reflection'    => '💭 Refleksi',
                        'peer_feedback' => '👥 Feedback Sejawat',
                    ];
                    @endphp
                    <option value="{{ $type }}" @selected(old('evidence_type') === $type)>
                        {{ $labels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Sub-CPMK --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Sub-CPMK</label>
                <select name="sub_cpmk_id"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <option value="">-- Pilih Sub-CPMK (opsional) --</option>
                    @foreach($subCpmks as $subCpmk)
                    <option value="{{ $subCpmk->id }}" @selected(old('sub_cpmk_id') == $subCpmk->id)>
                        {{ $subCpmk->kode }} — {{ Str::limit($subCpmk->deskripsi ?? '', 60) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- CPMK Mappings --}}
            @if($cpmks->isNotEmpty())
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">CPMK yang Dipetakan</label>
                <div class="border border-gray-200 rounded-lg p-3 space-y-1 max-h-40 overflow-y-auto">
                    @foreach($cpmks as $cpmk)
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="cpmk_ids[]" value="{{ $cpmk->id }}"
                            @checked(in_array($cpmk->id, old('cpmk_ids', [])))
                            class="w-4 h-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-xs text-gray-700">
                            <span class="font-mono font-medium">{{ $cpmk->kode }}</span>
                            @if($cpmk->deskripsi) — {{ Str::limit($cpmk->deskripsi, 70) }} @endif
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- File Upload --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Upload File</label>
                    <input type="file" name="file"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.zip">
                    <p class="text-xs text-gray-400 mt-1">PDF, Word, Excel, Gambar, ZIP — maks. 10MB</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">atau Link Eksternal</label>
                    <input type="url" name="external_link" value="{{ old('external_link') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="https://drive.google.com/...">
                </div>
            </div>

            {{-- Reflection --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Refleksi</label>
                <textarea name="reflection_notes" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none"
                    placeholder="Jelaskan bagaimana evidence ini menunjukkan pencapaian CPMK...">{{ old('reflection_notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('portfolio.evidences.index') }}"
                    class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    💾 Simpan Evidence
                </button>
            </div>
        </form>
    </div>
</div>
@endsection