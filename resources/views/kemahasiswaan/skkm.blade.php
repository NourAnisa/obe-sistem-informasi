@extends('layouts.dashboard')
@section('title', 'Approval SKKM Mahasiswa')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">🏆 Approval SKKM Mahasiswa</h1>
            <p class="text-sm text-brand-300 mt-1">Tinjau dan setujui pengajuan SKKM dari mahasiswa</p>
        </div>
        <a href="{{ route('kemahasiswaan.dashboard') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-brand-700 hover:bg-brand-600 text-white text-sm rounded-lg transition">
            ← Kembali ke Dashboard
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/40 text-green-300 px-4 py-3 rounded-lg text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/20 border border-red-500/40 text-red-300 px-4 py-3 rounded-lg text-sm">
        ❌ {{ session('error') }}
    </div>
    @endif

    {{-- Status Filter Tabs --}}
    <div class="bg-brand-800/60 rounded-xl border border-brand-700 p-4">
        @php
        $tabs = [
        'pending' => ['label' => 'Menunggu', 'color' => 'yellow'],
        'disetujui' => ['label' => 'Disetujui', 'color' => 'green'],
        'ditolak' => ['label' => 'Ditolak', 'color' => 'red'],
        'all' => ['label' => 'Semua', 'color' => 'blue'],
        ];
        $colorMap = [
        'yellow' => ['active' => 'bg-yellow-500 text-white', 'inactive' => 'bg-yellow-500/20 text-yellow-300 hover:bg-yellow-500/30'],
        'green' => ['active' => 'bg-green-500 text-white', 'inactive' => 'bg-green-500/20 text-green-300 hover:bg-green-500/30'],
        'red' => ['active' => 'bg-red-500 text-white', 'inactive' => 'bg-red-500/20 text-red-300 hover:bg-red-500/30'],
        'blue' => ['active' => 'bg-blue-500 text-white', 'inactive' => 'bg-blue-500/20 text-blue-300 hover:bg-blue-500/30'],
        ];
        @endphp
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($tabs as $key => $tab)
            @php
            $isActive = $status === $key;
            $cnt = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0);
            $cls = $colorMap[$tab['color']][$isActive ? 'active' : 'inactive'];
            @endphp
            <a href="{{ route('kemahasiswaan.skkm.index', ['status' => $key, 'search' => $search]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition {{ $cls }}">
                {{ $tab['label'] }}
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-white/20">
                    {{ $cnt }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('kemahasiswaan.skkm.index') }}" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Cari NIM, nama mahasiswa, atau nama kegiatan..."
                class="flex-1 bg-brand-900/60 border border-brand-600 text-white placeholder-brand-400 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-blue-500">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg transition">
                🔍 Cari
            </button>
            @if($search)
            <a href="{{ route('kemahasiswaan.skkm.index', ['status' => $status]) }}"
                class="px-4 py-2 bg-brand-700 hover:bg-brand-600 text-brand-300 text-sm rounded-lg transition">
                ✕ Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-brand-800/60 rounded-xl border border-brand-700 overflow-hidden">
        @if($skkmList->isEmpty())
        <div class="text-center py-16 text-brand-400">
            <div class="text-5xl mb-3">🏆</div>
            <p class="text-lg font-medium text-brand-300">
                Tidak ada data SKKM
                @if($status !== 'all') dengan status <strong>{{ $tabs[$status]['label'] ?? $status }}</strong> @endif
            </p>
            @if($search)
            <p class="text-sm mt-1">untuk pencarian "{{ $search }}"</p>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-brand-200">
                <thead class="bg-brand-700/60 text-brand-300 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left w-8">No</th>
                        <th class="px-4 py-3 text-left">Mahasiswa</th>
                        <th class="px-4 py-3 text-left">Nama Kegiatan</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-center">Tingkat</th>
                        <th class="px-4 py-3 text-center">Poin</th>
                        <th class="px-4 py-3 text-center">Tgl Diajukan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-700/50">
                    @foreach($skkmList as $skkm)
                    <tr class="hover:bg-brand-700/30 transition">
                        <td class="px-4 py-3 text-brand-400">
                            {{ ($skkmList->currentPage() - 1) * $skkmList->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $skkm->nama_mhs ?? '-' }}</div>
                            <div class="text-xs text-brand-400">{{ $skkm->nim ?? '-' }}</div>
                            @if(!empty($skkm->email))
                            <div class="text-xs text-brand-500">{{ $skkm->email }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-white max-w-xs">{{ $skkm->nama_kegiatan }}</div>
                            @if(!empty($skkm->prestasi))
                            <div class="text-xs text-blue-400">Peran: {{ $skkm->prestasi }}</div>
                            @endif
                            @if(!empty($skkm->keterangan))
                            <div class="text-xs text-brand-400 mt-1 max-w-xs" title="{{ $skkm->keterangan }}">
                                {{ \Illuminate\Support\Str::limit($skkm->keterangan, 60) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $kat = $skkm->kategori ?? '-'; @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-xs
                                    {{ str_starts_with($kat, 'B') ? 'bg-purple-500/20 text-purple-300' : 'bg-teal-500/20 text-teal-300' }}">
                                {{ $kat }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs text-brand-300">{{ $skkm->tingkat ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $poin = $skkm->points_awarded ?? $skkm->sks_ekuivalen ?? 0; @endphp
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold
                                    {{ $skkm->status === 'disetujui' ? 'bg-green-500/20 text-green-300' : 'bg-brand-700 text-brand-300' }}">
                                {{ $poin }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-brand-400">
                            {{ \Carbon\Carbon::parse($skkm->created_at)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($skkm->status === 'disetujui')
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-500/20 text-green-300 rounded-full text-xs font-medium">
                                ✅ Disetujui
                            </span>
                            @elseif($skkm->status === 'ditolak')
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-500/20 text-red-300 rounded-full text-xs font-medium">
                                ❌ Ditolak
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-500/20 text-yellow-300 rounded-full text-xs font-medium">
                                ⏳ Menunggu
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                {{-- View detail --}}
                                <button onclick="showDetail({{ json_encode($skkm) }})"
                                    class="p-1.5 bg-blue-500/20 hover:bg-blue-500/40 text-blue-300 rounded transition"
                                    title="Lihat Detail">👁</button>

                                @if($skkm->status === 'pending')
                                {{-- Approve --}}
                                <form method="POST" action="{{ route('kemahasiswaan.skkm.approve', $skkm->id) }}"
                                    onsubmit="return confirm('Setujui SKKM ini?')">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 bg-green-500/20 hover:bg-green-500/40 text-green-300 rounded transition"
                                        title="Setujui">✅</button>
                                </form>
                                {{-- Reject --}}
                                <button onclick="showRejectModal({{ $skkm->id }}, '{{ addslashes($skkm->nama_kegiatan) }}')"
                                    class="p-1.5 bg-red-500/20 hover:bg-red-500/40 text-red-300 rounded transition"
                                    title="Tolak">❌</button>
                                @endif

                                @if($skkm->status !== 'pending')
                                {{-- Reset to pending --}}
                                <form method="POST" action="{{ route('kemahasiswaan.skkm.reset', $skkm->id) }}"
                                    onsubmit="return confirm('Reset status ke pending?')">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 bg-brand-600 hover:bg-brand-500 text-brand-300 rounded transition text-xs"
                                        title="Reset ke Pending">🔄</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($skkmList->hasPages())
        <div class="px-4 py-3 border-t border-brand-700">
            {{ $skkmList->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-300">{{ $counts['pending'] ?? 0 }}</div>
            <div class="text-xs text-yellow-400 mt-1">⏳ Menunggu Review</div>
        </div>
        <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-300">{{ $counts['disetujui'] ?? 0 }}</div>
            <div class="text-xs text-green-400 mt-1">✅ Disetujui</div>
        </div>
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-red-300">{{ $counts['ditolak'] ?? 0 }}</div>
            <div class="text-xs text-red-400 mt-1">❌ Ditolak</div>
        </div>
    </div>

</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 hidden">
    <div class="bg-brand-800 border border-brand-600 rounded-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-white mb-2">❌ Tolak Pengajuan SKKM</h3>
        <p class="text-sm text-brand-300 mb-4">Kegiatan: <span id="rejectKegiatanName" class="text-white font-medium"></span></p>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-brand-300 mb-1">
                    Catatan Penolakan <span class="text-brand-500">(opsional)</span>
                </label>
                <textarea name="catatan_penolakan" rows="3"
                    class="w-full bg-brand-900 border border-brand-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-red-500"
                    placeholder="Berikan alasan penolakan jika perlu..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-medium transition">
                    Tolak Pengajuan
                </button>
                <button type="button" onclick="closeRejectModal()"
                    class="flex-1 py-2 bg-brand-700 hover:bg-brand-600 text-brand-300 rounded-lg text-sm transition">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Detail Modal --}}
<div id="detailModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 hidden">
    <div class="bg-brand-800 border border-brand-600 rounded-xl p-6 w-full max-w-lg mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">📋 Detail Pengajuan SKKM</h3>
            <button onclick="closeDetailModal()" class="text-brand-400 hover:text-white text-2xl leading-none">×</button>
        </div>
        <div id="detailContent" class="space-y-2 text-sm max-h-96 overflow-y-auto"></div>
    </div>
</div>

<script>
    function showRejectModal(id, kegiatan) {
        document.getElementById('rejectKegiatanName').textContent = kegiatan;
        document.getElementById('rejectForm').action = '/kemahasiswaan/skkm/' + id + '/reject';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.querySelector('#rejectForm textarea').value = '';
    }

    function showDetail(data) {
        const fields = [
            ['Mahasiswa', (data.nama_mhs || '-') + ' (' + (data.nim || '-') + ')'],
            ['Email', data.email],
            ['Nama Kegiatan', data.nama_kegiatan],
            ['Kategori', data.kategori],
            ['Tingkat', data.tingkat],
            ['Peran / Prestasi', data.prestasi],
            ['Jenis Anggota', data.jenis_anggota],
            ['Lokasi', data.lokasi],
            ['No. SK / Sertifikat', data.nomor_sk],
            ['Tanggal SK', data.tanggal_sk],
            ['Poin / SKS Ekuivalen', data.points_awarded || data.sks_ekuivalen || 0],
            ['Tahun Akademik', data.tahun_akademik],
            ['Semester', data.semester],
            ['Keterangan', data.keterangan],
            ['Google Drive', data.google_drive_link ?
                '<a href="' + data.google_drive_link + '" target="_blank" class="text-blue-400 underline">Lihat File</a>' :
                null
            ],
        ];
        let html = '';
        fields.forEach(([label, value]) => {
            if (value && value !== '-') {
                html += `<div class="flex gap-3 py-1.5 border-b border-brand-700/50">
                <span class="text-brand-400 w-44 flex-shrink-0 text-xs mt-0.5">${label}</span>
                <span class="text-white font-medium text-sm">${value}</span>
            </div>`;
            }
        });
        document.getElementById('detailContent').innerHTML = html || '<p class="text-brand-400">Tidak ada detail.</p>';
        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) closeRejectModal();
    });
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) closeDetailModal();
    });
</script>
@endsection