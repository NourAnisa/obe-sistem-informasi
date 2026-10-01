@extends('layouts.dashboard')
@section('title', 'Review Portfolio Mahasiswa')
@section('breadcrumb', 'Review Portfolio')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🔍 Review Portfolio Mahasiswa</h1>
            <p class="text-sm text-gray-500 mt-0.5">Evidence yang menunggu review dosen/kaprodi</p>
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

    {{-- Filter TA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-5 py-4">
        <form method="GET" action="{{ route('portfolio.review.index') }}" class="flex items-center gap-3 flex-wrap">
            <label class="text-xs font-semibold text-gray-600">Tahun Akademik:</label>
            <select name="ta" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                @foreach($taList as $item)
                <option value="{{ $item }}" {{ $item === $ta ? 'selected' : '' }}>{{ $item }}</option>
                @endforeach
            </select>
            <span class="text-xs text-gray-400">Total: {{ $evidences->total() }} evidence menunggu review</span>
        </form>
    </div>

    {{-- Evidence List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($evidences->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <div class="text-4xl mb-3">✅</div>
            <p class="text-sm font-medium text-gray-500">Tidak ada evidence yang perlu direview.</p>
            <p class="text-xs text-gray-400 mt-1">Semua evidence sudah diproses atau belum ada yang disubmit.</p>
        </div>
        @else
        <div class="divide-y divide-gray-100">
            @foreach($evidences as $ev)
            @php
                $statusColor = ['submitted'=>'blue','under_review'=>'amber'][$ev->status] ?? 'gray';
                $statusLabel = ['submitted'=>'Submitted','under_review'=>'Under Review'][$ev->status] ?? $ev->status;
                $typeLabel   = ['assignment'=>'Tugas','project'=>'Proyek','certification'=>'Sertifikat','reflection'=>'Refleksi','peer_feedback'=>'Peer Feedback'][$ev->evidence_type] ?? $ev->evidence_type;
                $mhs         = $ev->portfolio->mahasiswa;
            @endphp
            <div class="px-6 py-5" x-data="{ showApprove: false, showReject: false }">
                <div class="flex items-start gap-4">

                    {{-- Left: info --}}
                    <div class="flex-1 min-w-0">
                        {{-- Mahasiswa info --}}
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-brand-700 font-bold text-xs">{{ strtoupper(substr($mhs->nama ?? 'M', 0, 2)) }}</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-800 text-sm">{{ $mhs->nama ?? '—' }}</span>
                                <span class="text-xs text-gray-400 ml-2">NIM: {{ $mhs->nim ?? '—' }}</span>
                                <span class="text-xs text-gray-400 ml-2">Angkatan: {{ $mhs->angkatan ?? '—' }}</span>
                            </div>
                        </div>

                        {{-- Evidence title & badges --}}
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-gray-800">{{ $ev->title }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                                {{ $statusLabel }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ $typeLabel }}
                            </span>
                        </div>

                        {{-- Description --}}
                        @if($ev->description)
                        <p class="text-xs text-gray-500 mb-2">{{ $ev->description }}</p>
                        @endif

                        {{-- CPMK linked --}}
                        @if($ev->cpmkMappings->isNotEmpty())
                        <div class="flex flex-wrap gap-1 mb-2">
                            <span class="text-xs text-gray-500 mr-1">CPMK:</span>
                            @foreach($ev->cpmkMappings as $cpmk)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-brand-50 text-brand-700 font-medium">
                                {{ $cpmk->kode }}
                                @if($cpmk->pivot->demonstration_level)
                                <span class="ml-1 text-brand-400">(L{{ $cpmk->pivot->demonstration_level }})</span>
                                @endif
                            </span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Reflection notes (collapsible) --}}
                        @if($ev->reflection_notes)
                        <details class="text-xs text-gray-500 mb-2">
                            <summary class="cursor-pointer text-brand-600 hover:underline">Lihat catatan refleksi</summary>
                            <p class="mt-1 bg-gray-50 rounded-lg p-2">{{ $ev->reflection_notes }}</p>
                        </details>
                        @endif

                        {{-- External link --}}
                        @if($ev->external_link)
                        <a href="{{ $ev->external_link }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline mb-2">
                            🔗 {{ Str::limit($ev->external_link, 60) }}
                        </a>
                        @endif

                        <p class="text-xs text-gray-400">Disubmit: {{ $ev->updated_at->format('d M Y, H:i') }}</p>
                    </div>

                    {{-- Right: actions --}}
                    <div class="flex flex-col gap-2 shrink-0">
                        {{-- Download file --}}
                        @if($ev->file_path)
                        <a href="{{ route('portfolio.evidence.download.dosen', $ev->id) }}"
                           class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition">
                            📎 Unduh File
                        </a>
                        @endif

                        {{-- Approve button --}}
                        <button @click="showApprove = !showApprove; showReject = false"
                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold transition">
                            ✅ Setujui
                        </button>

                        {{-- Reject button --}}
                        <button @click="showReject = !showReject; showApprove = false"
                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition">
                            ❌ Tolak
                        </button>
                    </div>
                </div>

                {{-- Approve form (inline, collapsible) --}}
                <div x-show="showApprove" x-collapse class="mt-4">
                    <form action="{{ route('portfolio.evidence.approve', $ev->id) }}" method="POST"
                          class="bg-green-50 border border-green-200 rounded-xl p-4 space-y-3">
                        @csrf
                        <h4 class="text-sm font-semibold text-green-800">✅ Setujui Evidence</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Rating Kualitas <span class="text-gray-400">(opsional, 1–5)</span>
                                </label>
                                <select name="rating"
                                        class="w-full border border-green-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none">
                                    <option value="">-- Tidak dirating --</option>
                                    @foreach([1=>'1 - Poor',2=>'2 - Below Average',3=>'3 - Average',4=>'4 - Good',5=>'5 - Excellent'] as $v=>$l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Feedback <span class="text-gray-400">(opsional)</span></label>
                                <input type="text" name="feedback" maxlength="1000"
                                       class="w-full border border-green-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                                       placeholder="Umpan balik untuk mahasiswa...">
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-semibold transition">
                                Konfirmasi Setujui
                            </button>
                            <button type="button" @click="showApprove = false"
                                    class="px-4 py-1.5 bg-white border border-gray-300 text-gray-600 rounded-lg text-xs font-medium transition hover:bg-gray-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Reject form (inline, collapsible) --}}
                <div x-show="showReject" x-collapse class="mt-4">
                    <form action="{{ route('portfolio.evidence.reject', $ev->id) }}" method="POST"
                          class="bg-red-50 border border-red-200 rounded-xl p-4 space-y-3">
                        @csrf
                        <h4 class="text-sm font-semibold text-red-800">❌ Tolak Evidence</h4>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                Alasan Penolakan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="feedback" required maxlength="1000" rows="2"
                                      class="w-full border border-red-300 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
                                      placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition">
                                Konfirmasi Tolak
                            </button>
                            <button type="button" @click="showReject = false"
                                    class="px-4 py-1.5 bg-white border border-gray-300 text-gray-600 rounded-lg text-xs font-medium transition hover:bg-gray-50">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($evidences->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $evidences->appends(['ta' => $ta])->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
