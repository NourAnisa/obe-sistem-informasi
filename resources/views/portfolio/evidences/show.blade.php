@extends('layouts.dashboard')
@section('title', $evidence->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $evidence->title }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Dibuat {{ $evidence->created_at->format('d M Y H:i') }}
                @if($evidence->portfolio->mahasiswa ?? false)
                oleh {{ $evidence->portfolio->mahasiswa->nama }}
                @endif
            </p>
        </div>
        <a href="{{ route('portfolio.evidences.index') }}"
            class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Status Banner --}}
            @php
            $statusConfig = [
                'draft'        => ['bg-gray-100',   'text-gray-700',   'Draft'],
                'submitted'    => ['bg-blue-100',    'text-blue-700',   'Dikirim — Menunggu Review'],
                'under_review' => ['bg-purple-100',  'text-purple-700', 'Sedang Direview'],
                'approved'     => ['bg-green-100',   'text-green-700',  '✅ Disetujui'],
                'rejected'     => ['bg-red-100',     'text-red-700',    '❌ Ditolak'],
            ];
            [$bgCls, $txtCls, $statusLabel] = $statusConfig[$evidence->status] ?? ['bg-gray-100', 'text-gray-600', ucfirst($evidence->status)];
            @endphp
            <div class="rounded-xl px-5 py-3 {{ $bgCls }}">
                <p class="font-semibold text-sm {{ $txtCls }}">Status: {{ $statusLabel }}</p>
                @if($evidence->review_notes)
                <p class="text-xs mt-1 {{ $txtCls }} opacity-80">Catatan reviewer: {{ $evidence->review_notes }}</p>
                @endif
                @if($evidence->reviewedBy)
                <p class="text-xs mt-1 {{ $txtCls }} opacity-70">
                    Direview oleh: {{ $evidence->reviewedBy->name }}
                    @if($evidence->reviewed_at) pada {{ \Carbon\Carbon::parse($evidence->reviewed_at)->format('d M Y') }} @endif
                </p>
                @endif
            </div>

            {{-- Description --}}
            @if($evidence->description)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-50 bg-gray-50/50">
                    <h2 class="font-semibold text-sm text-gray-700">📄 Deskripsi</h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $evidence->description }}</p>
                </div>
            </div>
            @endif

            {{-- Reflection --}}
            @if($evidence->reflection_notes)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-50 bg-gray-50/50">
                    <h2 class="font-semibold text-sm text-gray-700">💭 Catatan Refleksi</h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $evidence->reflection_notes }}</p>
                </div>
            </div>
            @endif

            {{-- CPMK Mappings --}}
            @if($evidence->cpmkMappings->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-50 bg-gray-50/50">
                    <h2 class="font-semibold text-sm text-gray-700">🎯 Pemetaan CPMK</h2>
                </div>
                <div class="px-6 py-4 flex flex-wrap gap-2">
                    @foreach($evidence->cpmkMappings as $mapping)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-mono font-medium bg-blue-50 text-blue-700 border border-blue-100">
                        {{ $mapping->cpmk->kode ?? $mapping->cpmk_id }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Meta Info --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h3 class="font-semibold text-sm text-gray-700">ℹ️ Informasi</h3>
                <div class="space-y-2 text-sm">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tipe</p>
                        <p class="font-medium text-gray-800 capitalize mt-0.5">
                            {{ str_replace('_', ' ', $evidence->evidence_type ?? '-') }}
                        </p>
                    </div>
                    @if($evidence->subCpmk)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sub-CPMK</p>
                        <p class="font-mono text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded mt-0.5 inline-block">
                            {{ $evidence->subCpmk->kode }}
                        </p>
                    </div>
                    @endif
                    @if($evidence->external_link)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Link Eksternal</p>
                        <a href="{{ $evidence->external_link }}" target="_blank"
                            class="text-xs text-brand-600 hover:underline break-all mt-0.5 block">
                            🔗 Buka Link
                        </a>
                    </div>
                    @endif
                    @if($evidence->file_path)
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">File</p>
                        <a href="{{ Storage::url($evidence->file_path) }}" target="_blank"
                            class="text-xs text-brand-600 hover:underline mt-0.5 block">
                            📎 {{ $evidence->file_name ?? 'Download File' }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-2">
                <h3 class="font-semibold text-sm text-gray-700 mb-3">⚡ Aksi</h3>

                @if(in_array($evidence->status, ['draft', 'rejected']))
                <a href="{{ route('portfolio.evidences.edit', $evidence->id) }}"
                    class="flex items-center justify-center w-full px-4 py-2 bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-lg text-sm font-medium transition">
                    ✏️ Edit Evidence
                </a>
                @endif

                @if($evidence->status === 'draft')
                <form action="{{ route('portfolio.evidences.submit', $evidence->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Kirim evidence untuk direview?')"
                        class="flex items-center justify-center w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition">
                        📤 Submit untuk Review
                    </button>
                </form>
                @endif

                @if(in_array($evidence->status, ['draft', 'rejected']))
                <form action="{{ route('portfolio.evidences.destroy', $evidence->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Hapus evidence ini?')"
                        class="flex items-center justify-center w-full px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-sm font-medium transition">
                        🗑 Hapus
                    </button>
                </form>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection