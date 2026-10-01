<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('obe.nama_sistem', 'OBE Kurikulum')) — {{ auth()->user()->program->nama ?? config('obe.prodi', 'Program Studi') }} {{ config('obe.universitas_singkat', '') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Plus Jakarta Sans', 'sans-serif'],
                        display: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js + Collapse plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .font-display {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        a,
        button {
            transition: all 0.15s ease;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .gradient-text {
            background: linear-gradient(135deg, #1d4ed8 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50 antialiased" x-data="{ sidebarOpen: true, userDropdown: false }">

    {{-- ═══════ SIDEBAR ═══════ --}}
    <aside x-show="sidebarOpen" x-cloak
        class="fixed inset-y-0 left-0 z-50 w-64 bg-brand-900 text-white flex flex-col shadow-xl transition-all duration-300">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-brand-800">
            <div class="flex items-center space-x-3">
                @if(config('obe.logo_path'))
                <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center ring-1 ring-white/30 overflow-hidden">
                    <img src="{{ Storage::disk('public')->url(config('obe.logo_path')) }}" alt="Logo Kampus" class="max-w-full max-h-full object-contain">
                </div>
                @elseif(config('obe.logo_prodi_path'))
                <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center ring-1 ring-white/30 overflow-hidden">
                    <img src="{{ Storage::disk('public')->url(config('obe.logo_prodi_path')) }}" alt="Logo Prodi" class="max-w-full max-h-full object-contain">
                </div>
                @else
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center ring-1 ring-white/30">
                    <span class="text-white font-black text-base font-display">
                        {{ strtoupper(substr(auth()->user()->program->nama ?? config('obe.prodi', 'OBE'), 0, 2)) }}
                    </span>
                </div>
                @endif
                <div>
                    <p class="text-white font-bold font-display leading-tight">{{ config('obe.nama_sistem', 'OBE Kurikulum') }}</p>
                    <p class="text-brand-300 text-xs leading-tight">{{ auth()->user()->program->nama ?? config('obe.prodi', 'Program Studi') }}</p>
                </div>
            </div>
        </div>

        {{-- User Info --}}
        <div class="px-4 py-4 border-b border-brand-800">
            <div class="flex items-center space-x-3 p-3 rounded-xl bg-white/10">
                <div class="w-9 h-9 rounded-full bg-brand-500 flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name ?? 'User' }}</p>
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-brand-500/40 text-brand-200 capitalize">
                        {{ auth()->user()->role ?? 'user' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @php
            $current = request()->path();
            $role = auth()->user()->role ?? '';
            $navClass = fn($path) => Str::startsWith($current, trim($path, '/'))
            ? 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold bg-white/20 text-white'
            : 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-brand-200 hover:bg-white/10 hover:text-white transition';
            @endphp

            {{-- ── KAPRODI / ADMIN ── --}}
            @if(in_array($role, ['kaprodi', 'admin', 'dekan', 'wakildekan']))
            @if(in_array($role, ['dekan', 'wakildekan']))
            <a href="{{ route('dekan.dashboard') }}" class="{{ $navClass('dekan/dashboard') }}">
                <span>🏠</span> <span>Dashboard</span>
            </a>
            @else
            <a href="{{ route('kaprodi.dashboard') }}" class="{{ $navClass('kaprodi/dashboard') }}">
                <span>🏠</span> <span>Dashboard</span>
            </a>
            @endif
            <a href="{{ route('kurikulum.index') }}" class="{{ $navClass('kurikulum') }}">
                <span>📚</span> <span>Kurikulum</span>
            </a>
            <a href="{{ route('pemetaan.index') }}" class="{{ $navClass('pemetaan') }}">
                <span>🗺️</span> <span>Pemetaan CPL–CPMK</span>
            </a>

            {{-- OBE Analytics — Collapsible (kaprodi/admin: dengan Rekap Angkatan) --}}
            @include('layouts._nav_obe_analytics', ['showRekapAngkatan' => true])

            {{-- Akademik — Collapsible --}}
            <div x-data="{ open: {{ Str::startsWith($current, 'distribusi') || Str::startsWith($current, 'krs') || Str::startsWith($current, 'nilai') || Str::startsWith($current, 'rps') || Str::startsWith($current, 'bap') || Str::startsWith($current, 'evaluasi-bap') || Str::startsWith($current, 'rooms') || Str::startsWith($current, 'course-schedules') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 pt-3 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider hover:text-brand-200 transition">
                    <span>📂 Akademik</span>
                    <svg :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                </button>
                <div x-show="open" x-collapse class="space-y-0.5">
                    <a href="{{ route('distribusi-dosen.index') }}" class="{{ $navClass('distribusi-dosen') }}">
                        <span>👥</span> <span>Distribusi Dosen</span>
                    </a>
                    <a href="{{ route('krs-mahasiswa.index') }}" class="{{ $navClass('krs-mahasiswa') }}">
                        <span>📋</span> <span>KRS Mahasiswa</span>
                    </a>
                    <a href="{{ route('nilai-mahasiswa.index') }}" class="{{ $navClass('nilai-mahasiswa') }}">
                        <span>📝</span> <span>Input Nilai</span>
                    </a>
                    <a href="{{ route('rps.index') }}" class="{{ $navClass('rps') }}">
                        <span>📄</span> <span>RPS</span>
                    </a>
                    <a href="{{ route('bap.index') }}" class="{{ $navClass('bap') }}">
                        <span>📋</span> <span>BAP (Berita Acara)</span>
                    </a>
                    <a href="{{ route('evaluasi-bap.index') }}" class="{{ $navClass('evaluasi-bap') }}">
                        <span>⭐</span> <span>Evaluasi Dosen</span>
                    </a>
                    <a href="{{ route('rooms.analytics.dashboard') }}" class="{{ $navClass('rooms/dashboard') }}">
                        <span>📊</span> <span>Room Utilization</span>
                    </a>
                    <a href="{{ route('rooms.index') }}" class="{{ $navClass('rooms') }}">
                        <span>🏢</span> <span>Kelola Ruangan</span>
                    </a>
                    <a href="{{ route('course-schedules.index') }}" class="{{ $navClass('course-schedules') }}">
                        <span>📅</span> <span>Jadwal Kuliah</span>
                    </a>
                    <a href="{{ route('laporan-evaluasi.index') }}" class="{{ $navClass('laporan-evaluasi') }}">
                        <span>📊</span> <span>Laporan Evaluasi</span>
                    </a>
                </div>
            </div>

            {{-- Manajemen — Collapsible --}}
            <div x-data="{ open: {{ Str::startsWith($current, 'admin') || Str::startsWith($current, 'mahasiswa') || Str::startsWith($current, 'users') || Str::startsWith($current, 'laporan') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 pt-3 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider hover:text-brand-200 transition">
                    <span>⚙️ Manajemen</span>
                    <svg :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                </button>
                <div x-show="open" x-collapse class="space-y-0.5">
                    <a href="{{ route('admin.mk.index') }}" class="{{ $navClass('admin/mk') }}">
                        <span>📄</span> <span>Mata Kuliah</span>
                    </a>
                    <a href="{{ route('admin.cpl.index') }}" class="{{ $navClass('admin/cpl') }}">
                        <span>📑</span> <span>CPL</span>
                    </a>
                    <a href="{{ route('admin.cpmk.index') }}" class="{{ $navClass('admin/cpmk') }}">
                        <span>🎯</span> <span>CPMK</span>
                    </a>
                    <a href="{{ route('admin.sub-cpmk.index') }}" class="{{ $navClass('admin/sub-cpmk') }}">
                        <span>🔖</span> <span>Sub-CPMK</span>
                    </a>
                    <a href="{{ route('admin.bahan-kajian.index') }}" class="{{ $navClass('admin/bahan-kajian') }}">
                        <span>📚</span> <span>Bahan Kajian</span>
                    </a>
                    <a href="{{ route('mahasiswa.index') }}" class="{{ $navClass('mahasiswa') }}">
                        <span>👨‍🎓</span> <span>Mahasiswa</span>
                    </a>
                    <a href="{{ route('users.index') }}" class="{{ $navClass('users') }}">
                        <span>👥</span> <span>Pengguna</span>
                    </a>
                    <a href="{{ route('laporan.publikasi') }}" class="{{ $navClass('laporan/publikasi') }}">
                        <span>📚</span> <span>Laporan Publikasi</span>
                    </a>
                    <a href="{{ route('admin.faculties.index') }}" class="{{ $navClass('admin/faculties') }}">
                        <span>🏛</span> <span>Fakultas</span>
                    </a>
                    <a href="{{ route('admin.programs.index') }}" class="{{ $navClass('admin/programs') }}">
                        <span>🎓</span> <span>Program Studi</span>
                    </a>
                    @if(in_array($role, ['admin', 'dekan', 'wakildekan']))
                    <a href="{{ route('admin.settings.index') }}" class="{{ $navClass('admin/settings') }}">
                        <span>⚙️</span> <span>Profil Universitas</span>
                    </a>
                    @endif
                    @if($role === 'kaprodi')
                    <a href="{{ route('kaprodi.settings.index') }}" class="{{ $navClass('kaprodi/settings') }}">
                        <span>⚙️</span> <span>Profil Prodi</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── DOSEN ── --}}
            @if($role === 'dosen')
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider">Dosen</p>
            <a href="{{ route('dosen.dashboard') }}" class="{{ $navClass('dosen/dashboard') }}">
                <span>🏠</span> <span>Dashboard</span>
            </a>

            {{-- Akademik Dosen — Collapsible --}}
            <div x-data="{ open: {{ Str::startsWith($current, 'distribusi') || Str::startsWith($current, 'mata-kuliah') || Str::startsWith($current, 'rps') || Str::startsWith($current, 'bap') || Str::startsWith($current, 'evaluasi-bap') || Str::startsWith($current, 'nilai') || Str::startsWith($current, 'bobot') || Str::startsWith($current, 'cpl') || Str::startsWith($current, 'dosen') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-3 pt-3 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider hover:text-brand-200 transition">
                    <span>📂 Akademik</span>
                    <svg :class="open ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                </button>
                <div x-show="open" x-collapse class="space-y-0.5">
                    <a href="{{ route('distribusi-dosen.index') }}" class="{{ $navClass('distribusi-dosen') }}">
                        <span>👥</span> <span>Distribusi MK Saya</span>
                    </a>
                    <a href="{{ route('mata-kuliah.index') }}" class="{{ $navClass('mata-kuliah') }}">
                        <span>📚</span> <span>Mata Kuliah Saya</span>
                    </a>
                    <a href="{{ route('rps.index') }}" class="{{ $navClass('rps') }}">
                        <span>📄</span> <span>RPS</span>
                    </a>
                    <a href="{{ route('bap.index') }}" class="{{ $navClass('bap') }}">
                        <span>📋</span> <span>BAP (Berita Acara)</span>
                    </a>
                    <a href="{{ route('evaluasi-bap.index') }}" class="{{ $navClass('evaluasi-bap') }}">
                        <span>⭐</span> <span>Evaluasi Dosen</span>
                    </a>
                    <a href="{{ route('dosen.pa.index') }}" class="{{ $navClass('dosen/pa') }}">
                        <span>👨‍🏫</span>
                        <span>KRS Bimbingan PA
                            @if(isset($paKrsPending) && $paKrsPending > 0)
                            <span class="ml-1 px-1.5 py-0.5 text-xs bg-amber-500 text-white rounded-full">{{ $paKrsPending }}</span>
                            @endif
                        </span>
                    </a>
                    <a href="{{ route('obe.cpmk-evaluasi') }}" class="{{ $navClass('cpmk-evaluasi') }}">
                        <span>🎯</span> <span>CPMK Saya</span>
                    </a>
                    <a href="{{ route('bobot-penilaian.index') }}" class="{{ $navClass('bobot-penilaian') }}">
                        <span>⚖️</span> <span>Bobot Penilaian</span>
                    </a>
                    <a href="{{ route('nilai-mahasiswa.index') }}" class="{{ $navClass('nilai-mahasiswa') }}">
                        <span>📝</span> <span>Input Nilai</span>
                    </a>
                    <a href="{{ route('dosen.publikasi.index', auth()->id()) }}" class="{{ $navClass('dosen/'.auth()->id().'/publikasi') }}">
                        <span>📚</span> <span>Publikasi Saya</span>
                    </a>
                </div>
            </div>

            {{-- OBE Analytics Dosen — Collapsible (tanpa Rekap Angkatan) --}}
            @include('layouts._nav_obe_analytics', ['showRekapAngkatan' => false])
            @endif

            @if($role === 'akademik')
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider">Akademik</p>
            <a href="{{ route('akademik.dashboard') }}" class="{{ $navClass('akademik/dashboard') }}">
                <span>🏠</span> <span>Dashboard</span>
            </a>
            <a href="{{ route('kurikulum.index') }}" class="{{ $navClass('kurikulum') }}">
                <span>📚</span> <span>Kurikulum</span>
            </a>
            <a href="{{ route('rps.index') }}" class="{{ $navClass('rps') }}">
                <span>📄</span> <span>RPS</span>
            </a>
            <a href="{{ route('bap.index') }}" class="{{ $navClass('bap') }}">
                <span>📋</span> <span>BAP (Berita Acara)</span>
            </a>
            <a href="{{ route('evaluasi-bap.index') }}" class="{{ $navClass('evaluasi-bap') }}">
                <span>⭐</span> <span>Evaluasi Dosen</span>
            </a>
            <a href="{{ route('laporan-evaluasi.index') }}" class="{{ $navClass('laporan-evaluasi') }}">
                <span>📊</span> <span>Laporan Evaluasi</span>
            </a>
            @endif

            @if($role === 'kemahasiswaan')
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider">Kemahasiswaan</p>
            <a href="{{ route('kemahasiswaan.dashboard') }}" class="{{ $navClass('kemahasiswaan/dashboard') }}">
                <span>🏠</span> <span>Dashboard</span>
            </a>
            <a href="{{ route('kemahasiswaan.skkm.index') }}" class="{{ $navClass('kemahasiswaan/skkm') }}">
                <span>🏆</span>
                <span class="flex-1">Approval SKKM</span>
                {{-- Pending badge (pass $skkmPending from controller) --}}
                @php $pending = $skkmPending ?? 0; @endphp
                @if($pending > 0)
                <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold bg-yellow-500 text-black rounded-full">
                    {{ $pending > 99 ? '99+' : $pending }}
                </span>
                @endif
            </a>
            <a href="{{ route('evaluasi.index') }}" class="{{ $navClass('evaluasi') }}">
                <span>🎓</span> <span>CPL Mahasiswa</span>
            </a>
            <a href="{{ route('mbkm.index') }}" class="{{ $navClass('mbkm') }}">
                <span>🌍</span> <span>MBKM</span>
            </a>

            @endif

            @if($role === 'mahasiswa')
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-brand-400 uppercase tracking-wider">Mahasiswa</p>
            <a href="{{ route('mahasiswa.dashboard') }}" class="{{ $navClass('mahasiswa-dashboard') }}">
                <span>🏠</span> <span>Dashboard</span>
            </a>
            <a href="{{ route('mahasiswa.krs') }}" class="{{ $navClass('mahasiswa-dashboard/krs') }}">
                <span>📚</span> <span>Ambil Mata Kuliah</span>
            </a>
            <a href="{{ route('mahasiswa.nilai') }}" class="{{ $navClass('mahasiswa-dashboard/nilai') }}">
                <span>📊</span> <span>Nilai Saya</span>
            </a>
            <a href="{{ route('mahasiswa.cpl') }}" class="{{ $navClass('mahasiswa-dashboard/cpl') }}">
                <span>🎯</span> <span>Evaluasi CPL Saya</span>
            </a>
            <a href="{{ route('mahasiswa.skkm') }}" class="{{ $navClass('mahasiswa-dashboard/skkm') }}">
                <span>🏆</span> <span>Input SKKM</span>
            </a>
            <a href="{{ route('bap-evaluasi.index') }}" class="{{ $navClass('bap-evaluasi') }}">
                <span>📝</span> <span>Evaluasi BAP</span>
            </a>
            <a href="{{ route('bap-penilaian.index') }}" class="{{ $navClass('bap-penilaian') }}">
                <span>⭐</span> <span>Penilaian Dosen</span>
            </a>
            <a href="{{ route('portfolio.index') }}" class="{{ $navClass('portfolio') }}">
                <span>📚</span> <span>Portfolio Saya</span>
            </a>
            @endif
        </nav>

        {{-- Logout --}}
        <div class="px-4 py-4 border-t border-brand-800">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-brand-200 hover:bg-red-500/20 hover:text-red-300 transition">
                    <span>🚪</span> <span>Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══════ TOPBAR ═══════ --}}
    <header class="fixed top-0 right-0 z-40 bg-white border-b border-slate-200 shadow-sm transition-all duration-300"
        :class="sidebarOpen ? 'left-64' : 'left-0'">
        <div class="flex items-center justify-between h-16 px-4 sm:px-6">

            {{-- Left: toggle + breadcrumb --}}
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" aria-label="Toggle sidebar"
                    class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('home') }}" class="hover:text-brand-600 transition">{{ config('obe.nama_sistem', 'OBE Kurikulum') }}</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">@yield('breadcrumb', 'Dashboard')</span>
                </div>
            </div>

            {{-- Right: notifications + user dropdown --}}
            <div class="flex items-center gap-3">
                @if(in_array(auth()->user()->role, ['admin', 'dekan', 'wakildekan']))
                <form action="{{ route('admin.switch-program') }}" method="POST" class="inline-flex items-center mr-2">
                    @csrf
                    <label class="text-xs font-semibold text-slate-500 mr-2 hidden md:block">Prodi Aktif:</label>
                    <select name="program_id" onchange="this.form.submit()" class="border border-slate-300 rounded-lg px-2 py-1 text-xs bg-slate-50 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        @foreach(\App\Models\Program::orderBy('nama')->get() as $prog)
                            <option value="{{ $prog->id }}" {{ auth()->user()->program_id == $prog->id ? 'selected' : '' }}>
                                {{ $prog->jenjang }} {{ $prog->nama }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                {{-- Notification bell --}}
                <button aria-label="Notifikasi" class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition">
                        <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center">
                            <span class="text-white font-bold text-xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                            </span>
                        </div>
                        <span class="text-sm font-medium text-slate-700 hidden sm:block">{{ auth()->user()->name ?? 'User' }}</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                        class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <span>👤</span> Profil Saya
                        </a>
                        @if(in_array(auth()->user()->role, ['admin', 'dekan', 'wakildekan']))
                        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <span>🏛</span> Profil Kampus
                        </a>
                        @elseif(auth()->user()->role === 'kaprodi')
                        <a href="{{ route('kaprodi.settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <span>⚙️</span> Profil Prodi
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Mobile sidebar overlay --}}
    <div x-show="!sidebarOpen" x-cloak class="hidden"></div>

    {{-- ═══════ MAIN CONTENT ═══════ --}}
    <main class="min-h-screen bg-gray-50 transition-all duration-300 pt-16"
        :class="sidebarOpen ? 'ml-64' : 'ml-0'">

        {{-- Page header --}}
        @hasSection('page-header')
        <div class="bg-white border-b border-slate-200 px-6 py-4">
            @yield('page-header')
        </div>
        @endif

        {{-- Content --}}
        <div class="p-6">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>

</html>
