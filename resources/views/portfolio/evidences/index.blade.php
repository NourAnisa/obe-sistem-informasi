@extends('layouts.dashboard')
@section('title', 'Evidence Saya')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 Evidence Saya</h1>
            <p class="text-sm text-gray-500 mt-0.5">Portfolio: {{ $portfolio->title ?? 'Portfolio Mahasiswa' }}</p>
        </div>
        <a href="{{ route('portfolio.evidences.create') }}"
            class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
            + Tambah Evidence
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

    {{-- Evidence List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @forelse($evidences as $evidence)
        <div class="border-b border-gray-50 last:border-b-0 hover:bg-gray-50/60 transition p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('portfolio.evidences.show', $evidence->id) }}"
                        class="font-semibold text-gray-800 hover:text-brand-600 transition text-sm">
                        {{ $evidence->title }}
                    </a>
                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                        @php
                        $statusColors = [
                            'draft'        => 'bg-gray-100 text-gray-600',
                            'submitted'    => 'bg-blue-100 text-blue-700',
                            'under_review' => 'bg-purple-100 text-purple-700',
                            'approved'     => 'bg-green-100 text-green-700',
                            'rejected'     => 'bg-red-100 text-red-700',
                        ];
                        $statusColor = $statusColors[$evidence->status] ?? 'bg-gray-100 text-gray-600';
                        $statusLabels = [
                            'draft'        => 'Draft',
                            'submitted'    => 'Dikirim',
                            'under_review' => 'Direview',
                            'approved'     => 'Disetujui',
                            'rejected'     => 'Ditolak',
                        ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ $statusLabels[$evidence->status] ?? ucfirst($evidence->status) }}
                        </span>
                        @if($evidence->evidence_type)
                        <span class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $evidence->evidence_type) }}</span>
                        @endif
                        @if($evidence->subCpmk)
                        <span class="text-xs font-mono bg-blue-50 text-blue-700 px-2 py-0.5 rounded">{{ $evidence->subCpmk->kode }}</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $evidence->created_at->format('d M Y') }}</span>
                    </div>
                    @if($evidence->description)
                    <p class="text-xs text-gray-500 mt-1.5 line-clamp-2">{{ $evidence->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('portfolio.evidences.show', $evidence->id) }}"
                        class="px-3 py-1.5 text-xs font-medium bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-lg transition">
                        Lihat
                    </a>
                    @if(in_array($evidence->status, ['draft', 'rejected']))
                    <a href="{{ route('portfolio.evidences.edit', $evidence->id) }}"
                        class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg transition">
                        Edit
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <div class="text-5xl mb-4">📭</div>
            <p class="text-gray-600 font-medium mb-4">Belum ada evidence</p>
            <a href="{{ route('portfolio.evidences.create') }}"
                class="inline-flex px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg text-sm transition">
                Upload Evidence Pertama
            </a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($evidences->hasPages())
    <div class="flex justify-center">{{ $evidences->links() }}</div>
    @endif

</div>
@endsection