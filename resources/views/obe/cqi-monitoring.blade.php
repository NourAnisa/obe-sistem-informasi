@extends('layouts.dashboard')
@section('title', 'CQI Monitoring — Closed-loop Improvement')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🔄 CQI Loop Monitoring</h1>
            <p class="text-sm text-gray-500 mt-0.5">Rencana tindak lanjut perbaikan CPL yang belum tercapai (Closed-loop Quality Improvement)</p>
        </div>
        <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline self-start">← Dashboard OBE</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-300 rounded-xl px-5 py-3 text-green-800 text-sm font-medium">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if(!$cqiTableExists)
    <div class="bg-amber-50 border border-amber-300 rounded-xl px-5 py-4 text-amber-800 text-sm">
        <p class="font-semibold mb-1">⚙️ Tabel <code>cqi_actions</code> belum dibuat</p>
        <p class="mb-2">Jalankan migration runner untuk mengaktifkan fitur CQI Monitoring:</p>
        <a href="/run_cqi_migration.php" target="_blank"
           class="inline-block bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded text-sm font-medium">
            🚀 Jalankan Migration Runner
        </a>
        <p class="mt-2 text-xs text-amber-600">Setelah berhasil, refresh halaman ini.</p>
    </div>
    @endif

    {{-- Filter + Auto-generate --}}
    <div class="bg-white border rounded-xl p-4 flex flex-wrap gap-3 items-end justify-between">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500 block mb-1">Tahun Akademik</label>
                <select name="ta" class="border rounded px-3 py-2 text-sm w-40">
                    @foreach($taList as $t)
                    <option value="{{ $t }}" @selected($ta === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Angkatan</label>
                <select name="angkatan" class="border rounded px-3 py-2 text-sm w-32">
                    <option value="">Semua</option>
                    @foreach($angkatanList as $a)
                    <option value="{{ $a }}" @selected($angkatan == $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">🔍 Filter</button>
        </form>
        <form method="GET">
            <input type="hidden" name="ta" value="{{ $ta }}">
            <input type="hidden" name="angkatan" value="{{ $angkatan }}">
            <input type="hidden" name="auto_generate" value="1">
            <button class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded text-sm font-medium"
                    onclick="return confirm('Auto-generate CQI action untuk {{ $belowThreshold->count() }} CPL di bawah threshold?')">
                ⚡ Auto-Generate CQI ({{ $belowThreshold->count() }} CPL)
            </button>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-red-600">{{ $openCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Open</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-blue-600">{{ $inProgCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Dalam Proses</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-3xl font-extrabold text-green-600">{{ $doneCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Selesai</p>
        </div>
    </div>

    {{-- Below Threshold Alert --}}
    @if($belowThreshold->isNotEmpty())
    <div class="bg-amber-50 border border-amber-300 rounded-xl p-4">
        <p class="font-semibold text-amber-800 mb-2">⚠️ CPL di bawah threshold untuk TA {{ $ta }} ({{ $belowThreshold->count() }} item)</p>
        <div class="flex flex-wrap gap-2">
            @foreach($belowThreshold as $b)
            <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-medium border border-amber-300">
                {{ $b->cpl_kode }}: {{ $b->avg_nilai }} &lt; {{ $b->threshold }}
                @if($b->angkatan) (Angkatan {{ $b->angkatan }})@endif
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Actions Table --}}
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Daftar CQI Action Plans</h2>
            <span class="text-xs text-gray-400">{{ $actions->count() }} actions · TA {{ $ta }}</span>
        </div>

        @if($actions->isEmpty())
        <div class="text-center py-12 text-gray-400">
            <div class="text-4xl mb-3">📋</div>
            <p>Belum ada CQI action untuk TA <strong>{{ $ta }}</strong>.</p>
            <p class="text-sm mt-1">Klik <strong>⚡ Auto-Generate CQI</strong> untuk membuat action plan otomatis.</p>
        </div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">CPL</th>
                    <th class="px-4 py-2 text-center">Angkatan</th>
                    <th class="px-4 py-2 text-center">Nilai</th>
                    <th class="px-4 py-2 text-left">Masalah</th>
                    <th class="px-4 py-2 text-left">Rencana Perbaikan</th>
                    <th class="px-4 py-2 text-center">PIC</th>
                    <th class="px-4 py-2 text-center">Target</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($actions as $a)
                @php
                    $statusCls = match($a->status) {
                        'done'        => 'bg-green-100 text-green-700 border border-green-300',
                        'in_progress' => 'bg-blue-100 text-blue-700 border border-blue-300',
                        default       => 'bg-red-100 text-red-700 border border-red-300',
                    };
                    $statusLabel = match($a->status) {
                        'done'        => 'Selesai',
                        'in_progress' => 'Dalam Proses',
                        default       => 'Open',
                    };
                @endphp
                <tr class="hover:bg-gray-50" id="row-{{ $a->id }}">
                    <td class="px-4 py-2">
                        <span class="font-bold text-purple-700">{{ $a->cpl_kode }}</span>
                        <div class="text-xs text-gray-400 max-w-xs truncate">{{ $a->cpl_deskripsi }}</div>
                    </td>
                    <td class="px-4 py-2 text-center text-gray-600">{{ $a->angkatan ?? '—' }}</td>
                    <td class="px-4 py-2 text-center">
                        <span class="font-mono font-bold {{ $a->nilai_cpl < $a->threshold ? 'text-red-600' : 'text-green-600' }}">
                            {{ $a->nilai_cpl }}
                        </span>
                        <div class="text-xs text-gray-400">threshold: {{ $a->threshold }}</div>
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-700 max-w-xs">{{ $a->masalah }}</td>
                    <td class="px-4 py-2 text-xs text-gray-700 max-w-xs">{{ $a->rencana_perbaikan }}</td>
                    <td class="px-4 py-2 text-center text-xs">{{ $a->pic }}</td>
                    <td class="px-4 py-2 text-center text-xs">{{ $a->target_semester ?? '—' }}</td>
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusCls }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-center">
                        <button onclick="openEdit({{ $a->id }}, @js($a))"
                                class="text-xs text-blue-600 hover:underline px-2 py-1 border border-blue-200 rounded">
                            ✏️ Edit
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>

{{-- Edit Modal --}}
<div id="cqiModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-4">✏️ Edit CQI Action</h3>
        <form id="cqiForm" method="POST">
            @csrf
            @method('POST')
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Status</label>
                    <select name="status" id="editStatus" class="border rounded px-3 py-2 text-sm w-full">
                        <option value="open">Open</option>
                        <option value="in_progress">Dalam Proses</option>
                        <option value="done">Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Masalah</label>
                    <textarea name="masalah" id="editMasalah" rows="2" class="border rounded px-3 py-2 text-sm w-full"></textarea>
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-1">Rencana Perbaikan</label>
                    <textarea name="rencana_perbaikan" id="editRencana" rows="2" class="border rounded px-3 py-2 text-sm w-full"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">PIC</label>
                        <input type="text" name="pic" id="editPic" class="border rounded px-3 py-2 text-sm w-full">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Target Semester</label>
                        <input type="text" name="target_semester" id="editTarget" class="border rounded px-3 py-2 text-sm w-full">
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-5 justify-end">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded text-sm text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>
<script>
function openEdit(id, data) {
    document.getElementById('cqiForm').action = '/obe/cqi-actions/' + id;
    document.getElementById('editStatus').value  = data.status;
    document.getElementById('editMasalah').value = data.masalah;
    document.getElementById('editRencana').value = data.rencana_perbaikan;
    document.getElementById('editPic').value     = data.pic;
    document.getElementById('editTarget').value  = data.target_semester || '';
    document.getElementById('cqiModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('cqiModal').classList.add('hidden');
}
document.getElementById('cqiModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endsection
