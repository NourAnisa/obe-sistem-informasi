<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'OBE ' . config('obe.prodi') . ' ' . config('obe.universitas_singkat')) — Kurikulum {{ config('obe.prodi') }} {{ config('obe.universitas_singkat') }}</title>

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

<body class="bg-slate-50 antialiased" x-data="{ mobileOpen: false }">

    {{-- ═══════ NAVBAR ═══════ --}}
    <header class="sticky top-0 z-50" x-data="{ scrolled: false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 10)">

        {{-- Top info bar --}}
        <div class="bg-brand-950 text-brand-300 text-xs py-1.5 hidden sm:block">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <span>🎓 {{ config('obe.universitas') }} · {{ config('obe.fakultas') }}</span>
                <span>TA {{ config('obe.tahun_akademik') }} · Kurikulum {{ config('obe.kurikulum') }}</span>
            </div>
        </div>

        {{-- Main Navbar --}}
        <nav :class="scrolled ? 'bg-white/95 backdrop-blur shadow-md' : 'bg-gradient-to-r from-brand-800 to-brand-900'"
            class="transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    {{-- Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center group-hover:bg-white/30 transition shadow-inner ring-1 ring-white/30">
                            <span class="text-white font-black text-base font-display">{{ strtoupper(substr(config('obe.prodi'), 0, 2)) }}</span>
                        </div>
                        <div class="hidden sm:block">
                            <p :class="scrolled ? 'text-brand-900' : 'text-white'"
                                class="font-bold text-sm leading-tight font-display tracking-tight">OBE {{ config('obe.prodi') }}</p>
                            <p :class="scrolled ? 'text-brand-400' : 'text-brand-200'"
                                class="text-xs leading-tight">{{ config('obe.universitas_singkat') }}</p>
                        </div>
                    </a>

                    {{-- Desktop Nav Links --}}
                    <div class="hidden md:flex items-center space-x-1">
                        @php $path = request()->path(); @endphp
                        @foreach([
                        ['Beranda', route('home'), ''],
                        ['CPL', route('cpl.index'), 'cpl'],
                        ['Mata Kuliah', route('mata-kuliah.index'), 'mata-kuliah'],
                        ['Kurikulum', route('kurikulum.index'), 'kurikulum'],
                        ['Bahan Kajian', route('bahan-kajian.index'), 'bahan-kajian'],
                        ['MBKM', route('mbkm.index'), 'mbkm'],
                        ] as [$label, $url, $match])
                        <a href="{{ $url }}"
                            :class="scrolled ? '{{ ($path === $match || ($match && Str::startsWith($path, $match))) ? 'text-brand-700 font-semibold bg-brand-50 rounded-lg' : 'text-slate-600 hover:text-brand-700 hover:bg-slate-100 rounded-lg' }}' : '{{ ($path === $match || ($match && Str::startsWith($path, $match))) ? 'bg-white/20 text-white font-semibold rounded-lg' : 'text-brand-100 hover:bg-white/10 hover:text-white rounded-lg' }}'"
                            class="px-3 py-2 text-sm transition-all">{{ $label }}</a>
                        @endforeach
                    </div>

                    {{-- Right: Auth buttons --}}
                    <div class="hidden md:flex items-center space-x-2">
                        @auth
                        <span :class="scrolled ? 'text-slate-600' : 'text-brand-200'" class="text-sm">
                            Hai, {{ auth()->user()->name }}
                        </span>
                        <a href="{{ route('dashboard') }}"
                            :class="scrolled ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-white/20 text-white hover:bg-white/30 ring-1 ring-white/30'"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition">
                            Dashboard
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                :class="scrolled ? 'text-slate-500 hover:text-red-600' : 'text-brand-200 hover:text-white'"
                                class="px-3 py-2 rounded-lg text-sm transition">
                                Keluar
                            </button>
                        </form>
                        @else
                        <a href="{{ route('login') }}"
                            :class="scrolled ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-white text-brand-800 hover:bg-brand-50'"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition shadow-sm">
                            Login
                        </a>
                        @endauth
                    </div>

                    {{-- Mobile hamburger --}}
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg"
                        :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-white hover:bg-white/10'">
                        <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile menu --}}
            <div x-show="mobileOpen" x-cloak
                :class="scrolled ? 'bg-white border-t border-slate-100' : 'bg-brand-900'"
                class="md:hidden px-4 pb-4 space-y-1">
                @foreach([
                ['Beranda', route('home')],
                ['CPL', route('cpl.index')],
                ['Mata Kuliah', route('mata-kuliah.index')],
                ['Kurikulum', route('kurikulum.index')],
                ['Bahan Kajian', route('bahan-kajian.index')],
                ['MBKM', route('mbkm.index')],
                ] as [$label, $url])
                <a href="{{ $url }}"
                    :class="scrolled ? 'text-slate-700 hover:bg-slate-100' : 'text-brand-100 hover:bg-white/10'"
                    class="block px-3 py-2 rounded-lg text-sm">{{ $label }}</a>
                @endforeach
                @auth
                <a href="{{ route('dashboard') }}"
                    :class="scrolled ? 'text-brand-700 hover:bg-brand-50' : 'text-white hover:bg-white/10'"
                    class="block px-3 py-2 rounded-lg text-sm font-medium">Dashboard</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        :class="scrolled ? 'text-red-600' : 'text-red-300'"
                        class="block w-full text-left px-3 py-2 rounded-lg text-sm">Keluar</button>
                </form>
                @else
                <a href="{{ route('login') }}"
                    class="block px-3 py-2 rounded-lg text-sm font-semibold bg-white text-brand-800">Login</a>
                @endauth
            </div>
        </nav>
    </header>

    {{-- ═══════ MAIN CONTENT ═══════ --}}
    <main>
        @yield('content')
    </main>

    {{-- ═══════ FOOTER ═══════ --}}
    <footer class="bg-brand-950 text-brand-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                {{-- Brand --}}
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center ring-1 ring-white/20">
                            <span class="text-white font-black text-base font-display">{{ strtoupper(substr(config('obe.prodi'), 0, 2)) }}</span>
                        </div>
                        <div>
                            <p class="text-white font-bold font-display">OBE {{ config('obe.prodi') }} {{ config('obe.universitas_singkat') }}</p>
                            <p class="text-brand-400 text-xs">Sistem Kurikulum OBE</p>
                        </div>
                    </div>
                    <p class="text-brand-400 text-sm leading-relaxed">
                        Platform informasi kurikulum berbasis Outcome-Based Education (OBE) Program Studi {{ config('obe.prodi') }}
                        {{ config('obe.universitas') }}.
                    </p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="text-white font-semibold mb-4 font-display">Tautan Cepat</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach([
                        ['Beranda', route('home')],
                        ['Capaian Pembelajaran Lulusan', route('cpl.index')],
                        ['Mata Kuliah', route('mata-kuliah.index')],
                        ['Kurikulum', route('kurikulum.index')],
                        ['Bahan Kajian', route('bahan-kajian.index')],
                        ['Program MBKM', route('mbkm.index')],
                        ] as [$label, $url])
                        <li><a href="{{ $url }}" class="text-brand-300 hover:text-white transition">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="text-white font-semibold mb-4 font-display">Kontak</h3>
                    <ul class="space-y-2 text-sm text-brand-300">
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5">🏛️</span>
                            <span>Program Studi {{ config('obe.prodi') }}<br>{{ config('obe.universitas') }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span>📍</span>
                            <span>{{ config('obe.alamat') }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span>🌐</span>
                            <a href="https://{{ config('obe.website') }}" class="hover:text-white transition">{{ config('obe.website') }}</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-brand-800 mt-8 pt-6 text-center text-brand-400 text-sm">
                © {{ date('Y') }} Program Studi {{ config('obe.prodi') }} — {{ config('obe.universitas') }}
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>