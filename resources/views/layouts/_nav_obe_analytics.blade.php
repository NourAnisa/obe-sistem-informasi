{{--
    Partial: OBE Analytics navigation group (sidebar).
    Variables:
      $navClass          — closure dari parent (required)
      $current           — request path saat ini (required)
      $showRekapAngkatan — bool, tampilkan "Rekap Angkatan" (kaprodi/admin only)
--}}
@php $showRekapAngkatan = $showRekapAngkatan ?? false; @endphp

<div x-data="{ open: {{ Str::startsWith($current, 'obe') || Str::startsWith($current, 'cpmk-evaluasi') || Str::startsWith($current, 'cpl-evaluasi') || Str::startsWith($current, 'early-warning') || Str::startsWith($current, 'monitoring') || Str::startsWith($current, 'rekap') || Str::startsWith($current, 'student-cpl') || Str::startsWith($current, 'cqi') || Str::startsWith($current, 'problematic') ? 'true' : 'false' }} }">
    <button @click="open = !open" class="w-full flex items-center justify-between px-3 pt-3 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider hover:text-brand-200 transition">
        <span>📐 OBE Analytics</span>
        <svg :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="space-y-0.5">
        <a href="{{ route('obe.dashboard') }}" class="{{ $navClass('obe-dashboard') }}">
            <span>🎯</span> <span>Dashboard OBE</span>
        </a>
        <a href="{{ route('obe.cpmk-evaluasi') }}" class="{{ $navClass('cpmk-evaluasi') }}">
            <span>📉</span> <span>Capaian CPMK</span>
        </a>
        <a href="{{ route('obe.cpl-evaluasi') }}" class="{{ $navClass('cpl-evaluasi') }}">
            <span>📊</span> <span>Capaian CPL</span>
        </a>
        @if($showRekapAngkatan)
        <a href="{{ route('obe.rekap-angkatan') }}" class="{{ $navClass('rekap-angkatan') }}">
            <span>🏫</span> <span>Rekap Angkatan</span>
        </a>
        @endif
        <a href="{{ route('obe.early-warning') }}" class="{{ $navClass('early-warning') }}">
            <span>⚠️</span> <span>Early Warning</span>
        </a>
        <a href="{{ route('obe.monitoring-kelulusan-cpl') }}" class="{{ $navClass('monitoring-kelulusan-cpl') }}">
            <span>🎓</span> <span>Monitoring Kelulusan</span>
        </a>
        <a href="{{ route('obe.cqi-monitoring') }}" class="{{ $navClass('cqi-monitoring') }}">
            <span>🔄</span> <span>CQI Monitoring</span>
        </a>
        <a href="{{ route('obe.rps-bap-consistency') }}" class="{{ $navClass('rps-bap-consistency') }}">
            <span>📋</span> <span>Konsistensi RPS & BAP</span>
        </a>
        <a href="{{ route('obe.problematic-courses') }}" class="{{ $navClass('problematic-courses') }}">
            <span>🔍</span> <span>MK Bermasalah</span>
        </a>
        <a href="{{ route('obe.student-evaluasi') }}" class="{{ $navClass('student-cpl') }}">
            <span>👨‍🎓</span> <span>Evaluasi Mahasiswa</span>
        </a>
    </div>
</div>
