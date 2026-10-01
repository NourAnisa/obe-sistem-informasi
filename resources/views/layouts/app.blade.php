<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('obe.nama_sistem', 'Sistem Informasi Kurikulum OBE')) — {{ auth()->user()->program->nama ?? config('obe.prodi', 'Program Studi') }} {{ config('obe.universitas_singkat', '') }}</title>

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

    <!-- Alpine.js -->
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

        /* Scrollbar */
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

        /* Smooth transitions */
        a,
        button {
            transition: all 0.15s ease;
        }

        /* Badge base */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        /* Active nav */
        .nav-active {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
        }

        /* Card hover effect */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        }

        /* Glass card */
        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #1d4ed8 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Sidebar indicator */
        .nav-pill {
            position: relative;
        }

        .nav-pill.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-slate-50 antialiased">

    {{-- ═══════ NAVBAR ═══════ --}}
    <header class="sticky top-0 z-50" x-data="{ open: false, scrolled: false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 10)">

        {{-- Top bar: fakultas info --}}
        <div class="bg-brand-950 text-brand-300 text-xs py-1.5 hidden sm:block">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <span>🎓 {{ config('obe.universitas', 'Universitas') }} · {{ config('obe.fakultas', 'Fakultas') }}</span>
                <span>TA {{ config('obe.tahun_akademik', '2025/2026') }} · Kurikulum OBE</span>
            </div>
        </div>

        {{-- Main navbar --}}
        <nav :class="scrolled ? 'bg-white/95 backdrop-blur shadow-md' : 'bg-gradient-to-r from-brand-800 to-brand-900'"
            class="transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    {{-- Logo --}}
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        @if(config('obe.logo_path'))
                        <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center shadow-inner ring-1 ring-white/30 overflow-hidden">
                            <img src="{{ Storage::disk('public')->url(config('obe.logo_path')) }}" alt="Logo Kampus" class="max-w-full max-h-full object-contain">
                        </div>
                        @elseif(config('obe.logo_prodi_path'))
                        <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center shadow-inner ring-1 ring-white/30 overflow-hidden">
                            <img src="{{ Storage::disk('public')->url(config('obe.logo_prodi_path')) }}" alt="Logo Prodi" class="max-w-full max-h-full object-contain">
                        </div>
                        @else
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center
                                group-hover:bg-white/30 transition shadow-inner ring-1 ring-white/30">
                            <span class="text-white font-black text-base font-display">
                                {{ strtoupper(substr(auth()->user()->program->nama ?? config('obe.prodi', 'OBE'), 0, 2)) }}
                            </span>
                        </div>
                        @endif
                        <div class="hidden sm:block">
                            <p :class="scrolled ? 'text-brand-900' : 'text-white'"
                                class="font-bold text-sm leading-tight font-display tracking-tight">{{ config('obe.nama_sistem', 'Sistem Informasi Kurikulum OBE') }}</p>
                            <p :class="scrolled ? 'text-brand-400' : 'text-brand-200'"
                                class="text-[11px] leading-none">{{ auth()->user()->program->nama ?? config('obe.prodi', 'Program Studi') }} · {{ auth()->user()->program->jenjang ?? config('obe.jenjang', 'S1') }}</p>
                        </div>
                    </a>

                    {{-- Desktop nav --}}
                    <div class="hidden lg:flex items-center space-x-0.5">
                        @php
                        $navItems = [
                        ['Beranda', route('dashboard'), 'dashboard'],
                        ['Profil Lulusan', route('profil-lulusan.index'), 'profil-lulusan.index'],
                        ['CPL', route('cpl.index'), 'cpl.index'],
                        ['Bahan Kajian', route('bahan-kajian.index'), 'bahan-kajian.index'],
                        ['Mata Kuliah', route('mata-kuliah.index'), 'mata-kuliah.index'],
                        ['Kurikulum', route('kurikulum.index'), 'kurikulum.index'],
                        ['MBKM', route('mbkm.index'), 'mbkm.index'],
                        ];
                        @endphp

                        @foreach($navItems as [$label, $url, $routeName])
                        <a href="{{ $url }}"
                            class="nav-pill px-3 py-2 rounded-lg text-sm font-medium
                       {{ request()->routeIs($routeName) ? 'nav-active text-white font-semibold' : '' }}"
                            :class="scrolled
                           ? '{{ request()->routeIs($routeName) ? 'bg-brand-100 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' }}'
                           : '{{ request()->routeIs($routeName) ? 'nav-active text-white' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}'">
                            {{ $label }}
                        </a>
                        @endforeach

                        {{-- RPS: dosen, admin, kaprodi (sesuai route middleware) --}}
                        @auth
                        @if(in_array(auth()->user()->role, ['dosen', 'admin', 'kaprodi']))
                        <a href="{{ route('rps.index') }}"
                            class="nav-pill px-3 py-2 rounded-lg text-sm font-medium
                       {{ request()->routeIs('rps.*') ? 'nav-active text-white font-semibold' : '' }}"
                            :class="scrolled
                           ? '{{ request()->routeIs('rps.*') ? 'bg-brand-100 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' }}'
                           : '{{ request()->routeIs('rps.*') ? 'nav-active text-white' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}'">
                            ⚡ RPS
                        </a>
                        @endif

                        {{-- Role-specific Dashboard Links --}}
                        @if(in_array(auth()->user()->role, ['kaprodi', 'admin']))
                        <a href="{{ route('kaprodi.dashboard') }}"
                            class="nav-pill px-3 py-2 rounded-lg text-sm font-medium
                       {{ request()->routeIs('kaprodi.*') ? 'nav-active text-white font-semibold' : '' }}"
                            :class="scrolled
                           ? '{{ request()->routeIs('kaprodi.*') ? 'bg-brand-100 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' }}'
                           : '{{ request()->routeIs('kaprodi.*') ? 'nav-active text-white' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}'">
                            🎓 Dashboard
                        </a>
                        @elseif(auth()->user()->role === 'dosen')
                        <a href="{{ route('dosen.dashboard') }}"
                            class="nav-pill px-3 py-2 rounded-lg text-sm font-medium
                       {{ request()->routeIs('dosen.*') ? 'nav-active text-white font-semibold' : '' }}"
                            :class="scrolled
                           ? '{{ request()->routeIs('dosen.*') ? 'bg-brand-100 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' }}'
                           : '{{ request()->routeIs('dosen.*') ? 'nav-active text-white' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}'">
                            👩‍🏫 Dashboard
                        </a>
                        @elseif(auth()->user()->role === 'akademik')
                        <a href="{{ route('akademik.dashboard') }}"
                            class="nav-pill px-3 py-2 rounded-lg text-sm font-medium
                       {{ request()->routeIs('akademik.*') ? 'nav-active text-white font-semibold' : '' }}"
                            :class="scrolled
                           ? '{{ request()->routeIs('akademik.*') ? 'bg-brand-100 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' }}'
                           : '{{ request()->routeIs('akademik.*') ? 'nav-active text-white' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}'">
                            🏛 Dashboard
                        </a>
                        @elseif(auth()->user()->role === 'kemahasiswaan')
                        <a href="{{ route('kemahasiswaan.dashboard') }}"
                            class="nav-pill px-3 py-2 rounded-lg text-sm font-medium
                       {{ request()->routeIs('kemahasiswaan.*') ? 'nav-active text-white font-semibold' : '' }}"
                            :class="scrolled
                           ? '{{ request()->routeIs('kemahasiswaan.*') ? 'bg-brand-100 text-brand-700' : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' }}'
                           : '{{ request()->routeIs('kemahasiswaan.*') ? 'nav-active text-white' : 'text-brand-100 hover:bg-white/10 hover:text-white' }}'">
                            🧑‍🎓 Dashboard
                        </a>
                        @endif
                        @endauth

                        {{-- Dropdown: Sistem (role-aware, Fix #1) --}}
                        @auth
                        @php $role = auth()->user()->role; @endphp
                        @if(in_array($role, ['kaprodi','admin','dosen','akademik','kemahasiswaan','mahasiswa']))
                        <div class="relative" x-data="{ show: false }" @mouseenter="show=true" @mouseleave="show=false">
                            <button :class="scrolled ? 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' : 'text-brand-100 hover:bg-white/10 hover:text-white'"
                                class="flex items-center space-x-1 px-3 py-2 rounded-lg text-sm font-medium">
                                <span>Sistem</span>
                                <svg class="w-3.5 h-3.5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="show" x-cloak
                                class="absolute left-0 mt-1 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden">

                                {{-- Kaprodi / Admin / Dosen / Akademik --}}
                                @if(in_array($role, ['kaprodi','admin','dosen','akademik']))
                                <p class="px-4 pt-2 pb-1 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Akademik</p>
                                <a href="{{ route('obe.dashboard') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-brand-50 rounded-md flex items-center justify-center text-xs group-hover:bg-brand-100">📊</span>
                                    <span>OBE Dashboard</span>
                                </a>
                                <a href="{{ route('obe.early-warning') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-amber-50 rounded-md flex items-center justify-center text-xs group-hover:bg-amber-100">⚠️</span>
                                    <span>Early Warning</span>
                                </a>
                                <a href="{{ route('bap.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-brand-50 rounded-md flex items-center justify-center text-xs group-hover:bg-brand-100">📋</span>
                                    <span>Berita Acara (BAP)</span>
                                </a>
                                <a href="{{ route('nilai-mahasiswa.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-green-50 rounded-md flex items-center justify-center text-xs group-hover:bg-green-100">📝</span>
                                    <span>Nilai Mahasiswa</span>
                                </a>
                                <a href="{{ route('laporan-evaluasi.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-purple-50 rounded-md flex items-center justify-center text-xs group-hover:bg-purple-100">📄</span>
                                    <span>Laporan Evaluasi</span>
                                </a>
                                @endif

                                {{-- Kaprodi / Admin only --}}
                                @if(in_array($role, ['kaprodi','admin']))
                                <div class="border-t border-slate-100 my-1"></div>
                                <p class="px-4 pt-1 pb-1 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Manajemen</p>
                                <a href="{{ route('mahasiswa.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-brand-50 rounded-md flex items-center justify-center text-xs group-hover:bg-brand-100">👥</span>
                                    <span>Data Mahasiswa</span>
                                </a>
                                <a href="{{ route('distribusi-dosen.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-brand-50 rounded-md flex items-center justify-center text-xs group-hover:bg-brand-100">🗂</span>
                                    <span>Distribusi Dosen MK</span>
                                </a>
                                <a href="{{ route('rooms.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-brand-50 rounded-md flex items-center justify-center text-xs group-hover:bg-brand-100">🏫</span>
                                    <span>Ruang & Jadwal</span>
                                </a>
                                @endif

                                {{-- Kemahasiswaan --}}
                                @if($role === 'kemahasiswaan')
                                <p class="px-4 pt-2 pb-1 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Kemahasiswaan</p>
                                <a href="{{ route('kemahasiswaan.skkm.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-brand-50 rounded-md flex items-center justify-center text-xs group-hover:bg-brand-100">🏅</span>
                                    <span>Approval SKKM</span>
                                </a>
                                @endif

                                {{-- Mahasiswa --}}
                                @if($role === 'mahasiswa')
                                <p class="px-4 pt-2 pb-1 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Mahasiswa</p>
                                <a href="{{ route('mahasiswa.krs') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-brand-50 rounded-md flex items-center justify-center text-xs group-hover:bg-brand-100">📚</span>
                                    <span>KRS Saya</span>
                                </a>
                                <a href="{{ route('mahasiswa.nilai') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-green-50 rounded-md flex items-center justify-center text-xs group-hover:bg-green-100">📝</span>
                                    <span>Nilai Saya</span>
                                </a>
                                <a href="{{ route('mahasiswa.skkm') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-amber-50 rounded-md flex items-center justify-center text-xs group-hover:bg-amber-100">🏅</span>
                                    <span>SKKM</span>
                                </a>
                                <a href="{{ route('portfolio.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-6 h-6 bg-purple-50 rounded-md flex items-center justify-center text-xs group-hover:bg-purple-100">🗂</span>
                                    <span>Portfolio</span>
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                        @endauth

                        {{-- Dropdown: Analisis (publik + role-aware) --}}
                        <div class="relative" x-data="{ show: false }" @mouseenter="show=true" @mouseleave="show=false">
                            <button :class="scrolled ? 'text-slate-600 hover:bg-slate-100 hover:text-brand-700' : 'text-brand-100 hover:bg-white/10 hover:text-white'"
                                class="flex items-center space-x-1 px-3 py-2 rounded-lg text-sm font-medium">
                                <span>Analisis</span>
                                <svg class="w-3.5 h-3.5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="show" x-cloak
                                class="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden">
                                <a href="{{ route('pemetaan.index') }}"
                                    class="flex items-center space-x-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-7 h-7 bg-brand-50 rounded-lg flex items-center justify-center text-sm group-hover:bg-brand-100">🔗</span>
                                    <span>Pemetaan CPL–CPMK</span>
                                </a>
                                @auth
                                @if(in_array(auth()->user()->role, ['kaprodi', 'admin', 'dosen', 'akademik']))
                                <a href="{{ route('bobot-penilaian.index') }}"
                                    class="flex items-center space-x-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-7 h-7 bg-brand-50 rounded-lg flex items-center justify-center text-sm group-hover:bg-brand-100">📊</span>
                                    <span>Bobot Penilaian</span>
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="{{ route('evaluasi.index') }}"
                                    class="flex items-center space-x-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700 group">
                                    <span class="w-7 h-7 bg-brand-50 rounded-lg flex items-center justify-center text-sm group-hover:bg-brand-100">📈</span>
                                    <span>Evaluasi CPL</span>
                                </a>
                                @endif
                                @endauth
                            </div>
                        </div>
                    </div>

                    {{-- Auth button --}}
                    <div class="hidden lg:flex items-center space-x-3">
                        @guest
                        <a href="{{ route('login') }}"
                            :class="scrolled ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-white/15 text-white hover:bg-white/25 ring-1 ring-white/30'"
                            class="px-4 py-2 rounded-lg text-sm font-semibold">
                            Masuk
                        </a>
                        @else
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open=!open"
                                :class="scrolled ? 'ring-brand-200 text-brand-900' : 'ring-white/30 text-white'"
                                class="flex items-center space-x-2 ring-1 rounded-xl px-3 py-1.5 hover:bg-white/10">
                                <div class="w-7 h-7 bg-gradient-to-br from-brand-500 to-purple-500 rounded-lg
                                        flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium">{{ Str::words(auth()->user()->name, 1, '') }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak @click.away="open=false"
                                class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1.5 z-50">
                                <div class="px-4 py-2.5 border-b border-slate-100">
                                    <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                                </div>
                                @if(auth()->user()->role === 'admin')
                                <a href="/admin" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700">
                                    <span>⚙️</span><span>Admin Panel</span>
                                </a>
                                @endif
                                @if(in_array(auth()->user()->role, ['admin','kaprodi']))
                                <a href="{{ route('admin.settings.index') }}" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700">
                                    <span>🏛</span><span>Profil Kampus</span>
                                </a>
                                @endif
                                <div class="border-t border-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center space-x-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                        <span>🚪</span><span>Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endguest
                    </div>

                    {{-- Mobile hamburger --}}
                    <button @click="open=!open" class="lg:hidden p-2 rounded-lg text-white hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div x-show="open" x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="lg:hidden border-t border-white/10 bg-brand-900">
                <div class="px-4 py-3 space-y-1">
                    @foreach($navItems as [$label, $url, $routeName])
                    <a href="{{ $url }}"
                        class="block px-3 py-2.5 rounded-lg text-sm font-medium
                   {{ request()->routeIs($routeName) ? 'bg-white/15 text-white font-semibold' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                    <div class="border-t border-white/10 pt-2 mt-2">
                        <a href="{{ route('pemetaan.index') }}" class="block px-3 py-2.5 rounded-lg text-sm text-brand-200 hover:bg-white/10 hover:text-white">Pemetaan CPL</a>
                        <a href="{{ route('bobot-penilaian.index') }}" class="block px-3 py-2.5 rounded-lg text-sm text-brand-200 hover:bg-white/10 hover:text-white">Bobot Penilaian</a>
                    </div>
                    @guest
                    <a href="{{ route('login') }}" class="block mt-2 px-3 py-2.5 rounded-lg text-sm font-semibold bg-white/15 text-white text-center">Masuk</a>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    {{-- ═══════ MAIN CONTENT ═══════ --}}
    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- ═══════ FOOTER ═══════ --}}
    <footer class="bg-slate-900 text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center">
                            <span class="text-white font-black text-sm font-display">OBE</span>
                        </div>
                        <div>
                            <p class="text-white font-bold font-display">{{ config('obe.nama_sistem', 'Sistem Informasi Kurikulum OBE') }}</p>
                            <p class="text-xs text-slate-500">Sistem Informasi Kurikulum OBE Multi-Prodi</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                        Platform pengelolaan kurikulum berbasis <span class="text-brand-400 font-medium">Outcome-Based Education (OBE)</span>
                        untuk {{ auth()->user()->program->jenjang ?? config('obe.jenjang', 'S1') }} {{ auth()->user()->program->nama ?? config('obe.prodi', 'Program Studi') }} {{ config('obe.universitas') }}.
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">Navigasi</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('cpl.index') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">→ Capaian Pembelajaran</a></li>
                        <li><a href="{{ route('mata-kuliah.index') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">→ Daftar Mata Kuliah</a></li>
                        <li><a href="{{ route('kurikulum.index') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">→ Kurikulum per Semester</a></li>
                        <li><a href="{{ route('pemetaan.index') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">→ Pemetaan CPL–CPMK</a></li>
                        <li><a href="{{ route('mbkm.index') }}" class="hover:text-white hover:translate-x-1 transition-all inline-block">→ Info MBKM</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">Program Studi</h4>
                    <div class="space-y-2 text-sm">
                        <p class="flex items-start space-x-2">
                            <span class="text-brand-400 mt-0.5">🎓</span>
                            <span>{{ auth()->user()->program->jenjang ?? config('obe.jenjang', 'S1') }} {{ auth()->user()->program->nama ?? config('obe.prodi', 'Program Studi') }}</span>
                        </p>
                        <p class="flex items-start space-x-2">
                            <span class="text-brand-400 mt-0.5">🏛</span>
                            <span>{{ auth()->user()->program->faculty->nama ?? config('obe.fakultas', 'Fakultas') }}</span>
                        </p>
                        <p class="flex items-start space-x-2">
                            <span class="text-brand-400 mt-0.5">📍</span>
                            <span>{{ config('obe.universitas', 'Universitas') }}</span>
                        </p>
                        <p class="flex items-start space-x-2">
                            <span class="text-brand-400 mt-0.5">👩‍💼</span>
                            <span>Kaprodi: {{ auth()->user()->program->kaprodi->name ?? config('obe.kaprodi', 'Kaprodi') }}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <p>© {{ date('Y') }} {{ auth()->user()->program->nama ?? config('obe.prodi') }} {{ config('obe.universitas') }} · Kurikulum OBE {{ config('obe.tahun_akademik') }}</p>
                <div class="flex items-center space-x-4">
                    <span class="flex items-center space-x-1"><span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span><span>Sistem Kurikulum Terintegrasi</span></span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
