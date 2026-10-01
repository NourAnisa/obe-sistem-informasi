<?php

namespace App\Providers;

use App\Models\Mahasiswa;
use App\Models\MahasiswaMk;
use App\Models\SystemSetting;
use App\Models\MataKuliah;
use App\Models\Cpl;
use App\Observers\ProgramObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── Register ProgramObserver (auto-fill program_id on create) ──────
        MataKuliah::observe(ProgramObserver::class);
        Cpl::observe(ProgramObserver::class);
        Mahasiswa::observe(ProgramObserver::class);
        // ── Sync system_settings DB → config('obe.*') ──────────────────────
        // Dilakukan sekali per request, aman jika tabel belum ada (migrate:fresh)
        try {
            if (Schema::hasTable('system_settings')) {
                $dbSettings = Cache::remember('settings.all', 600, fn() =>
                    SystemSetting::all()->pluck('value', 'key')->toArray()
                );
                // Key yang di-map ke config obe.*
                $obeKeys = [
                    'universitas', 'fakultas', 'prodi', 'jenjang',
                    'tahun_akademik', 'kaprodi', 'nik_kaprodi',
                    'akreditasi', 'sks_total', 'total_semester', 'visi',
                    'nama_sistem', 'logo_path', 'logo_prodi_path',
                ];
                foreach ($obeKeys as $key) {
                    if (isset($dbSettings[$key]) && $dbSettings[$key] !== null) {
                        config(["obe.{$key}" => $dbSettings[$key]]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Tabel belum ada (migrate:fresh) — gunakan default dari config/obe.php
            Log::debug('AppServiceProvider: system_settings not ready — ' . $e->getMessage());
        }

        // Auto-create portfolio and import-nilai view directories/files if missing.
        // Guard with cache flag so filesystem I/O is skipped after first successful setup.
        if (!Cache::get('obe_views_setup_done')) {
            $allExist = is_dir(resource_path('views/portfolio/evidences'))
                && is_dir(resource_path('views/import-nilai'))
                && file_exists(resource_path('views/portfolio/dashboard.blade.php'));

            if (!$allExist) {
                if (!is_dir(resource_path('views/portfolio'))) {
                    $script = base_path('create_portfolio_views.php');
                    if (file_exists($script)) {
                        try {
                            chdir(base_path());
                            include $script;
                        } catch (\Throwable $e) {
                            Log::warning('AppServiceProvider: gagal include create_portfolio_views.php — ' . $e->getMessage());
                        }
                    }
                }

                foreach ([resource_path('views/portfolio/evidences'), resource_path('views/import-nilai')] as $dir) {
                    if (!is_dir($dir)) {
                        try {
                            mkdir($dir, 0755, true);
                        } catch (\Throwable $e) {
                            Log::warning("AppServiceProvider: gagal membuat direktori {$dir} — " . $e->getMessage());
                        }
                    }
                }

                $files = [
                    resource_path('views/portfolio/dashboard.blade.php') => $this->dashboardBlade(),
                    resource_path('views/portfolio/evidences/index.blade.php') => $this->evidencesIndexBlade(),
                    resource_path('views/portfolio/evidences/create.blade.php') => $this->evidencesCreateBlade(),
                    resource_path('views/portfolio/evidences/edit.blade.php') => $this->evidencesEditBlade(),
                    resource_path('views/portfolio/evidences/show.blade.php') => $this->evidencesShowBlade(),
                    resource_path('views/import-nilai/index.blade.php') => $this->importNilaiBlade(),
                ];
                foreach ($files as $path => $content) {
                    if (!file_exists($path)) {
                        try {
                            file_put_contents($path, $content);
                        } catch (\Throwable $e) {
                            Log::warning("AppServiceProvider: gagal membuat file {$path} — " . $e->getMessage());
                        }
                    }
                }
            }

            // Cache flag for 24 hours — avoids filesystem checks on every request
            Cache::put('obe_views_setup_done', true, 86400);
        }

        // Share PA pending count to all dosen views (cached per user per 60s)
        View::composer('layouts.dashboard', function ($view) {
            try {
                if (Auth::check() && Auth::user()->role === 'dosen') {
                    $userId = Auth::id();
                    $schemaOk = Cache::remember('schema_pa_check', 3600, function () {
                        return Schema::hasColumn('mahasiswas', 'dosen_pa_id')
                            && Schema::hasColumn('mahasiswa_mk', 'status');
                    });
                    if ($schemaOk) {
                        $paKrsPending = Cache::remember("pa_pending_{$userId}", 60, function () use ($userId) {
                            return MahasiswaMk::whereHas(
                                'mahasiswa',
                                fn($q) => $q->where('dosen_pa_id', $userId)
                            )->where('status', 'diajukan')->count();
                        });
                        $view->with('paKrsPending', $paKrsPending);
                    }
                }
            } catch (\Exception $e) {
                // Silently ignore if tables don't exist yet
            }
        });
    }

    private function dashboardBlade(): string
    {
        return <<<'BLADE'
@extends('layouts.dashboard')
@section('title', 'Portfolio Mahasiswa')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🗂️ Portfolio Saya</h1>
            @if($mahasiswa)
            <p class="text-sm text-gray-500 mt-0.5">{{ $mahasiswa->nama }} — NIM: {{ $mahasiswa->nim }}</p>
            @endif
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('portfolio.evidences.create') }}"
                class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                + Tambah Evidence
            </a>
            <a href="{{ route('portfolio.export-pdf') }}"
                class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
                📄 Export PDF
            </a>
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

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @php
        $statCards = [
            ['label' => 'Total Evidence',  'value' => $stats['total_evidences'],        'color' => '#3b82f6'],
            ['label' => 'Disetujui',        'value' => $stats['approved_evidences'],     'color' => '#10b981'],
            ['label' => 'Menunggu',         'value' => $stats['pending_evidences'],      'color' => '#f59e0b'],
            ['label' => 'Direview',         'value' => $stats['under_review_evidences'], 'color' => '#8b5cf6'],
            ['label' => 'Ditolak',          'value' => $stats['rejected_evidences'],     'color' => '#ef4444'],
            ['label' => 'Kelengkapan',      'value' => ($stats['completion_percentage'] ?? 0) . '%', 'color' => '#0ea5e9'],
        ];
        @endphp
        @foreach($statCards as $card)
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100" style="border-left:4px solid {{ $card['color'] }}">
            <div class="text-2xl font-bold" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $card['label'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- CPMK Coverage --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-brand-600 text-white px-6 py-3 font-semibold text-sm">🎯 Capaian CPMK</div>
            <div class="px-6 py-5">
                @php $pct = $totalCpmk > 0 ? round($coveredCpmk / $totalCpmk * 100) : 0; @endphp
                <div class="flex items-end gap-2 mb-3">
                    <span class="text-4xl font-bold text-brand-600">{{ $coveredCpmk }}</span>
                    <span class="text-gray-400 text-sm mb-1">/ {{ $totalCpmk }} CPMK tercakup</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden mb-2">
                    <div class="h-3 rounded-full" style="width:{{ $pct }}%;background:#3b82f6"></div>
                </div>
                <p class="text-xs text-gray-500">{{ $pct }}% CPMK telah memiliki evidence</p>
            </div>
        </div>

        {{-- Evidence by Type --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-purple-600 text-white px-6 py-3 font-semibold text-sm">📊 Evidence per Tipe</div>
            <div class="px-6 py-5 space-y-2">
                @forelse($evidenceByType as $type => $count)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">{{ $count }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">Belum ada evidence.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick link to evidences --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-b border-gray-100">
            <span class="font-semibold text-sm text-gray-700">📋 Evidence</span>
            <a href="{{ route('portfolio.evidences.index') }}" class="text-xs text-brand-600 hover:underline">Kelola semua evidence →</a>
        </div>
        <div class="px-6 py-4 text-sm text-gray-500 text-center">
            <a href="{{ route('portfolio.evidences.index') }}" class="text-brand-600 hover:underline">
                Lihat dan kelola semua evidence Anda di sini
            </a>
        </div>
    </div>

</div>
@endsection
BLADE;
    }

    private function evidencesIndexBlade(): string
    {
        return <<<'BLADE'
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
BLADE;
    }

    private function evidencesCreateBlade(): string
    {
        return <<<'BLADE'
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
BLADE;
    }

    private function evidencesEditBlade(): string
    {
        return <<<'BLADE'
@extends('layouts.dashboard')
@section('title', 'Edit Evidence')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">✏️ Edit Evidence</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $evidence->title }}</p>
        </div>
        <a href="{{ route('portfolio.evidences.show', $evidence->id) }}"
            class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-brand-600 text-white px-6 py-3 font-semibold text-sm">📋 Edit Data Evidence</div>

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

        <form action="{{ route('portfolio.evidences.update', $evidence->id) }}" method="POST"
            enctype="multipart/form-data" class="px-6 py-5 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Judul Evidence <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $evidence->title) }}" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none">{{ old('description', $evidence->description) }}</textarea>
            </div>

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
                    <option value="{{ $type }}" @selected(old('evidence_type', $evidence->evidence_type) === $type)>
                        {{ $labels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Sub-CPMK</label>
                <select name="sub_cpmk_id"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    <option value="">-- Pilih Sub-CPMK (opsional) --</option>
                    @foreach($subCpmks as $subCpmk)
                    <option value="{{ $subCpmk->id }}"
                        @selected(old('sub_cpmk_id', $evidence->sub_cpmk_id) == $subCpmk->id)>
                        {{ $subCpmk->kode }} — {{ Str::limit($subCpmk->deskripsi ?? '', 60) }}
                    </option>
                    @endforeach
                </select>
            </div>

            @if($cpmks->isNotEmpty())
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">CPMK yang Dipetakan</label>
                <div class="border border-gray-200 rounded-lg p-3 space-y-1 max-h-40 overflow-y-auto">
                    @php $mappedIds = old('cpmk_ids', $evidence->cpmkMappings->pluck('cpmk_id')->toArray()); @endphp
                    @foreach($cpmks as $cpmk)
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="cpmk_ids[]" value="{{ $cpmk->id }}"
                            @checked(in_array($cpmk->id, $mappedIds))
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Ganti File (opsional)</label>
                    @if($evidence->file_path)
                    <p class="text-xs text-gray-500 mb-1">File saat ini: {{ $evidence->file_name ?? basename($evidence->file_path) }}</p>
                    @endif
                    <input type="file" name="file"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.zip">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Link Eksternal</label>
                    <input type="url" name="external_link" value="{{ old('external_link', $evidence->external_link) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                        placeholder="https://...">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan Refleksi</label>
                <textarea name="reflection_notes" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent resize-none">{{ old('reflection_notes', $evidence->reflection_notes) }}</textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('portfolio.evidences.show', $evidence->id) }}"
                    class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit"
                    class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                    💾 Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
BLADE;
    }

    private function evidencesShowBlade(): string
    {
        return <<<'BLADE'
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
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
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
BLADE;
    }

    private function importNilaiBlade(): string
    {
        return <<<'BLADE'
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
BLADE;
    }
}
