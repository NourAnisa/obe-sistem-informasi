@extends('layouts.dashboard')
@section('title', 'Portfolio Saya')
@section('breadcrumb', 'Portfolio')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🗂️ Portfolio Saya</h1>
            @if($portfolio->mahasiswa)
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $portfolio->mahasiswa->nama }} — NIM: {{ $portfolio->mahasiswa->nim }}
                    &nbsp;|&nbsp; TA: {{ $ta }}
                </p>
            @endif
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm">
        ⚠️ {{ session('warning') }}
    </div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Stats Cards --}}
    @php
        $total          = $summary['total_evidences']      ?? 0;
        $approved       = $summary['approved_evidences']   ?? 0;
        $pct            = $summary['completion_percentage'] ?? 0;
        $cpmkTotal      = $summary['cpmk_total']           ?? 0;
        $cpmkCovered    = $summary['cpmk_covered']         ?? 0;
        $cpmkPct        = $summary['cpmk_coverage_pct']    ?? 0;
        $submittedCnt   = $evidencesByStatus->get('submitted',    collect())->count();
        $underReviewCnt = $evidencesByStatus->get('under_review', collect())->count();
        $rejectedCnt    = $evidencesByStatus->get('rejected',     collect())->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" style="border-left:4px solid #3b82f6">
            <div class="text-2xl font-bold text-blue-500">{{ $total }}</div>
            <div class="text-xs text-gray-500 mt-1">Total Evidence</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" style="border-left:4px solid #10b981">
            <div class="text-2xl font-bold text-green-500">{{ $approved }}</div>
            <div class="text-xs text-gray-500 mt-1">Disetujui</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" style="border-left:4px solid #60a5fa">
            <div class="text-2xl font-bold text-blue-400">{{ $submittedCnt }}</div>
            <div class="text-xs text-gray-500 mt-1">Submitted</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" style="border-left:4px solid #f59e0b">
            <div class="text-2xl font-bold text-amber-500">{{ $underReviewCnt }}</div>
            <div class="text-xs text-gray-500 mt-1">Under Review</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" style="border-left:4px solid #ef4444">
            <div class="text-2xl font-bold text-red-500">{{ $rejectedCnt }}</div>
            <div class="text-xs text-gray-500 mt-1">Ditolak</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" style="border-left:4px solid #0ea5e9">
            <div class="text-2xl font-bold text-brand-600">{{ $pct }}%</div>
            <div class="text-xs text-gray-500 mt-1">Kelengkapan</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- CPMK Coverage --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-brand-600 text-white px-6 py-3 font-semibold text-sm">🎯 Capaian CPMK</div>
            <div class="px-6 py-5">
                <div class="flex items-end gap-2 mb-3">
                    <span class="text-4xl font-bold text-brand-600">{{ $cpmkCovered }}</span>
                    <span class="text-gray-400 text-sm mb-1">/ {{ $cpmkTotal }} CPMK tercakup</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden mb-2">
                    <div class="h-3 rounded-full bg-brand-500" style="width:{{ $cpmkPct }}%"></div>
                </div>
                <p class="text-xs text-gray-500">{{ $cpmkPct }}% CPMK telah memiliki evidence</p>
            </div>
        </div>

        {{-- Evidence by Status --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-purple-600 text-white px-6 py-3 font-semibold text-sm">📊 Status Evidence</div>
            <div class="px-6 py-5 space-y-2">
                @foreach(['draft'=>['Draft','gray'], 'submitted'=>['Submitted','blue'], 'under_review'=>['Under Review','amber'], 'approved'=>['Disetujui','green'], 'rejected'=>['Ditolak','red']] as $st=>[$label,$color])
                @php $cnt = $evidencesByStatus->get($st, collect())->count(); @endphp
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ $cnt }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- CPMK List --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-3 font-semibold text-sm">📋 CPMK Semester Ini</div>
            <div class="px-4 py-3 max-h-48 overflow-y-auto space-y-1">
                @forelse($cpmkList as $c)
                <div class="flex items-start gap-2 py-1 text-xs">
                    <span class="font-semibold text-brand-700 shrink-0">{{ $c->kode }}</span>
                    <span class="text-gray-600 line-clamp-2">{{ $c->deskripsi }}</span>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada CPMK terdaftar semester ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- FORM TAMBAH EVIDENCE --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
         x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="w-full flex items-center justify-between px-6 py-4 bg-brand-50 hover:bg-brand-100 transition">
            <span class="font-semibold text-brand-800 text-sm">➕ Tambah Evidence Baru</span>
            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-brand-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-collapse>
            <form action="{{ route('portfolio.evidence.store') }}" method="POST" enctype="multipart/form-data"
                  class="px-6 py-5 space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Title --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Judul Evidence <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                               placeholder="contoh: Tugas Besar Pemrograman Web">
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tipe Evidence <span class="text-red-500">*</span></label>
                        <select name="evidence_type" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option value="">-- Pilih Tipe --</option>
                            @foreach(['assignment'=>'Tugas (Assignment)', 'project'=>'Proyek', 'certification'=>'Sertifikat', 'reflection'=>'Refleksi', 'peer_feedback'=>'Peer Feedback'] as $val=>$lbl)
                            <option value="{{ $val }}" {{ old('evidence_type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- File --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">File Pendukung <span class="text-gray-400">(opsional, maks 10 MB)</span></label>
                        <input type="file" name="file"
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                               class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    </div>

                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" maxlength="2000"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                  placeholder="Deskripsikan evidence ini secara singkat">{{ old('description') }}</textarea>
                    </div>

                    {{-- Reflection Notes --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Refleksi</label>
                        <textarea name="reflection_notes" rows="2" maxlength="3000"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                                  placeholder="Apa yang Anda pelajari dari evidence ini?">{{ old('reflection_notes') }}</textarea>
                    </div>

                    {{-- External Link --}}
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Link Eksternal <span class="text-gray-400">(opsional)</span></label>
                        <input type="url" name="external_link" value="{{ old('external_link') }}" maxlength="500"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"
                               placeholder="https://github.com/...">
                    </div>
                </div>

                {{-- CPMK Mapping --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        CPMK yang Dicapai <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(pilih minimal 1)</span>
                    </label>
                    @if($cpmkList->isEmpty())
                        <p class="text-xs text-gray-400">Belum ada CPMK yang tersedia untuk semester ini.</p>
                    @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
                        @foreach($cpmkList as $c)
                        <div class="flex items-start gap-2 p-2 rounded-lg hover:bg-gray-50"
                             x-data="{ checked: {{ in_array($c->id, old('cpmk_ids', [])) ? 'true' : 'false' }} }">
                            <input type="checkbox" name="cpmk_ids[]" id="cpmk_{{ $c->id }}"
                                   value="{{ $c->id }}"
                                   x-model="checked"
                                   {{ in_array($c->id, old('cpmk_ids', [])) ? 'checked' : '' }}
                                   class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <div class="flex-1 min-w-0">
                                <label for="cpmk_{{ $c->id }}" class="cursor-pointer">
                                    <span class="text-xs font-semibold text-brand-700">{{ $c->kode }}</span>
                                    <span class="text-xs text-gray-500 ml-1">{{ $c->mk_nama }}</span>
                                    <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">{{ $c->deskripsi }}</p>
                                </label>
                                {{-- Demonstration level (show only when checked) --}}
                                <div x-show="checked" x-collapse class="mt-1">
                                    <label class="text-xs text-gray-500">Tingkat Penguasaan (1–5):</label>
                                    <input type="number" name="demonstration_level[{{ $c->id }}]"
                                           min="1" max="5" value="{{ old('demonstration_level.'.$c->id, 3) }}"
                                           class="w-16 ml-2 border border-gray-300 rounded px-2 py-0.5 text-xs focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                        💾 Simpan Evidence
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════ --}}
    {{-- DAFTAR EVIDENCE --}}
    {{-- ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">📋 Daftar Evidence ({{ $evidencesRaw->count() }})</h2>
        </div>

        @if($evidencesRaw->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <div class="text-4xl mb-3">📂</div>
            <p class="text-sm">Belum ada evidence. Tambahkan evidence pertama Anda!</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($evidencesRaw as $ev)
            @php
                $statusColor = ['draft'=>'gray','submitted'=>'blue','under_review'=>'amber','approved'=>'green','rejected'=>'red'][$ev->status] ?? 'gray';
                $statusLabel = ['draft'=>'Draft','submitted'=>'Submitted','under_review'=>'Under Review','approved'=>'Disetujui','rejected'=>'Ditolak'][$ev->status] ?? $ev->status;
                $typeLabel   = ['assignment'=>'Tugas','project'=>'Proyek','certification'=>'Sertifikat','reflection'=>'Refleksi','peer_feedback'=>'Peer Feedback'][$ev->evidence_type] ?? $ev->evidence_type;
            @endphp
            <div class="px-6 py-4 flex items-start gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-gray-800 text-sm">{{ $ev->title }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                            {{ $statusLabel }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            {{ $typeLabel }}
                        </span>
                    </div>
                    @if($ev->description)
                    <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $ev->description }}</p>
                    @endif
                    {{-- CPMK linked --}}
                    @if($ev->cpmkMappings->isNotEmpty())
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        @foreach($ev->cpmkMappings as $cpmk)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-brand-50 text-brand-700 font-medium">{{ $cpmk->kode }}</span>
                        @endforeach
                    </div>
                    @endif
                    {{-- Feedback from dosen --}}
                    @if($ev->dosen_feedback)
                    <div class="mt-2 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs text-amber-800">
                        <span class="font-semibold">Feedback:</span> {{ $ev->dosen_feedback }}
                        @if($ev->reviewedBy) <span class="text-amber-600">— {{ $ev->reviewedBy->name }}</span>@endif
                    </div>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $ev->created_at->format('d M Y, H:i') }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    {{-- Download file --}}
                    @if($ev->file_path)
                    <a href="{{ route('portfolio.evidence.download', $ev->id) }}"
                       class="inline-flex items-center px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition">
                        📎 Unduh
                    </a>
                    @endif

                    {{-- Submit button (only if draft or rejected) --}}
                    @if(in_array($ev->status, ['draft', 'rejected']))
                    <form action="{{ route('portfolio.evidence.submit', $ev->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition"
                                onclick="return confirm('Submit evidence ini untuk direview dosen?')">
                            📤 Submit
                        </button>
                    </form>
                    @endif

                    {{-- Delete button (only if draft) --}}
                    @if($ev->status === 'draft')
                    <form action="{{ route('portfolio.evidence.destroy', $ev->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition"
                                onclick="return confirm('Hapus evidence ini?')">
                            🗑️
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
