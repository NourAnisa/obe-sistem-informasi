@extends('layouts.dashboard')

@section('title', 'Distribusi Dosen Mata Kuliah')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">📋 Distribusi Dosen Mata Kuliah</h1>
            <p class="text-sm text-gray-500 mt-0.5">Penugasan dosen ke mata kuliah per semester &amp; tahun akademik</p>
        </div>
        @if($canModify)
        <a href="{{ route('distribusi-dosen.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-sm">
            ➕ Tambah Distribusi
        </a>
        @endif
        @if(in_array(auth()->user()->role ?? '', ['admin','akademik','kaprodi']))
        <a href="{{ route('distribusi-dosen.export', array_filter(['ta'=>$ta,'semester'=>$sem])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm">
            📥 Export Excel
        </a>
        @endif
    </div>

    {{-- ── Alerts ── --}}
    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">
        ❌ {{ session('error') }}
    </div>
    @endif

    {{-- ── Filters ── --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="ta" onchange="this.form.submit()"
            class="rounded-xl border border-gray-200 bg-white text-sm px-3 py-2 focus:ring-2 focus:ring-brand-400 focus:outline-none">
            @foreach($taList as $t)
            <option value="{{ $t }}" {{ $ta == $t ? 'selected' : '' }}>TA {{ $t }}</option>
            @endforeach
        </select>

        <select name="semester" onchange="this.form.submit()"
            class="rounded-xl border border-gray-200 bg-white text-sm px-3 py-2 focus:ring-2 focus:ring-brand-400 focus:outline-none">
            <option value="">Semua Semester</option>
            @for($s = 1; $s <= 8; $s++)
                <option value="{{ $s }}" {{ $sem == $s ? 'selected' : '' }}>Semester {{ $s }}</option>
            @endfor
        </select>

        <select name="mk_id" onchange="this.form.submit()"
            class="rounded-xl border border-gray-200 bg-white text-sm px-3 py-2 focus:ring-2 focus:ring-brand-400 focus:outline-none min-w-[200px]">
            <option value="">Semua Mata Kuliah</option>
            @foreach($mataKuliahs->groupBy('semester') as $smt => $mks)
            <optgroup label="Semester {{ $smt }}">
                @foreach($mks as $mk)
                <option value="{{ $mk->id }}" {{ $mkId == $mk->id ? 'selected' : '' }}>
                    {{ $mk->kode }} — {{ $mk->nama }}
                </option>
                @endforeach
            </optgroup>
            @endforeach
        </select>

        @if($sem || $mkId)
        <a href="{{ route('distribusi-dosen.index', ['ta' => $ta]) }}"
            class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-xl border border-gray-200 bg-white">
            ✕ Reset Filter
        </a>
        @endif
    </form>

    {{-- ── Context banner when filtering by MK ── --}}
    @if($filterMk)
    <div class="mb-4 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-5 py-3">
        <div class="flex items-center gap-3 text-sm text-blue-800">
            <span class="text-lg">📘</span>
            <div>
                <span class="font-semibold">{{ $filterMk->kode }} — {{ $filterMk->nama }}</span>
                <span class="text-blue-600 ml-2">Semester {{ $filterMk->semester }} · {{ $filterMk->sks }} SKS</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($canModify)
            <a href="{{ route('distribusi-dosen.create', ['mk_id' => $filterMk->id]) }}"
                class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-semibold px-3 py-1.5 rounded-lg">
                ➕ Tambah Dosen ke MK ini
            </a>
            @endif
            <a href="{{ route('admin.mk.edit', $filterMk) }}"
                class="text-xs bg-white hover:bg-gray-50 text-blue-700 border border-blue-300 font-semibold px-3 py-1.5 rounded-lg">
                ✏️ Edit MK
            </a>
        </div>
    </div>
    @endif

    {{-- ── Table ── --}}
    @if($distribusi->isEmpty())
    <div class="text-center py-20 bg-white rounded-2xl border border-gray-100">
        <div class="text-5xl mb-3">👥</div>
        <p class="text-gray-400 font-medium">Belum ada distribusi dosen untuk TA {{ $ta }}</p>
        @if($canModify)
        <a href="{{ route('distribusi-dosen.create') }}"
            class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-brand-600 text-white text-sm rounded-xl hover:bg-brand-700">
            ➕ Tambah sekarang
        </a>
        @endif
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 bg-gray-50 text-sm text-gray-500">
            {{ $distribusi->count() }} penugasan &middot; TA {{ $ta }}
            @if($sem) &middot; Semester {{ $sem }} @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Dosen</th>
                        <th class="px-4 py-3 text-left">Mata Kuliah</th>
                        <th class="px-4 py-3 text-center">Kelas</th>
                        <th class="px-4 py-3 text-center">Smt</th>
                        <th class="px-4 py-3 text-center">Tahun Akademik</th>
                        <th class="px-4 py-3 text-center">Peran</th>
                        <th class="px-4 py-3 text-center">SKS</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($distribusi as $i => $d)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $d->dosen->name ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $d->dosen->nik ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $d->mataKuliah->nama ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $d->mataKuliah->kode ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $d->kelas ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-xs font-semibold">
                                {{ $d->semester }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 text-xs">{{ $d->tahun_akademik }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($d->peran === 'pengampu')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Pengampu</span>
                            @else
                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">Pengembang RPS</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-700 text-sm font-medium">
                            {{ $d->jumlah_sks ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(($d->status ?? 'aktif') === 'aktif')
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Aktif</span>
                            @else
                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-semibold">Nonaktif</span>
                            @endif
                        </td>
                        @if($canModify)
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('nilai-mahasiswa.show', $d->mata_kuliah_id) }}"
                                    class="px-3 py-1 text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 whitespace-nowrap"
                                    title="Input nilai mahasiswa untuk MK ini">
                                    📊 Nilai
                                </a>
                                <a href="{{ route('distribusi-dosen.edit', $d) }}"
                                    class="px-3 py-1 text-xs bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('distribusi-dosen.destroy', $d) }}" method="POST"
                                    onsubmit="return confirm('Hapus distribusi ini?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1 text-xs bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100">
                                        🗑 Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                        @else
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('nilai-mahasiswa.show', $d->mata_kuliah_id) }}"
                                class="px-3 py-1 text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 whitespace-nowrap">
                                📊 Input Nilai
                            </a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection