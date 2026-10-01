@extends('layouts.dashboard')
@section('title', 'Capaian CPMK')
@section('content')
@php
function cpmkRubricBadge($nilai) {
$n = (float)($nilai ?? 0);
if ($n >= 80) return ['label' => 'Proficient', 'cls' => 'bg-green-100 text-green-700 border border-green-300'];
if ($n >= 60) return ['label' => 'Developing', 'cls' => 'bg-yellow-100 text-yellow-700 border border-yellow-300'];
return ['label' => 'Novice', 'cls' => 'bg-red-100 text-red-700 border border-red-300'];
}
@endphp
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📉 Evaluasi Capaian CPMK</h1>
            <p class="text-sm text-gray-500">Lihat detail nilai CPMK per mahasiswa per mata kuliah</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Excel Export/Import --}}
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('obe.export-nilai-cpmk', ['ta' => $ta ?? '']) }}"
                   class="inline-flex items-center gap-1 px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                   ⬇ Export Bobot Excel
                </a>
                <form method="POST" action="{{ route('obe.import-nilai-cpmk') }}" enctype="multipart/form-data" class="inline-flex items-center gap-2">
                    @csrf
                    <label class="cursor-pointer inline-flex items-center gap-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                        ⬆ Import Bobot Excel
                        <input type="file" name="file" accept=".xlsx,.xls" class="hidden" onchange="this.form.submit()">
                    </label>
                </form>
            </div>
            <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard OBE</a>
        </div>
    </div>

    <form method="GET" class="bg-white border rounded-xl p-4 flex gap-3 items-end">
        <div>
            <label class="text-xs text-gray-500 block mb-1">Semester Aktif</label>
            <input name="ta" value="{{ $ta }}" class="border rounded px-3 py-2 text-sm w-36">
        </div>
        <div class="flex-1">
            <label class="text-xs text-gray-500 block mb-1">Mata Kuliah</label>
            <select name="mk_id" class="border rounded px-3 py-2 text-sm w-full">
                <option value="">— Pilih Mata Kuliah —</option>
                @foreach($mataKuliahs as $m)
                <option value="{{ $m->id }}" @selected($mk && $mk->id == $m->id)>{{ $m->kode }} — {{ $m->nama }}</option>
                @endforeach
            </select>
        </div>
        <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm">Tampilkan</button>
        @if($mk)
        <button type="button" onclick="hitungMk({{ $mk->id }})" class="bg-green-600 text-white px-4 py-2 rounded text-sm">⚡ Hitung CPMK</button>
        @endif
    </form>

    @if($mk && $summary->isNotEmpty())
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="font-semibold text-gray-700">Ringkasan CPMK — {{ $mk->nama }}</h2>
            <span class="text-xs text-gray-400">TA: {{ $ta }}</span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">CPMK</th>
                    <th class="px-4 py-2 text-left">Deskripsi</th>
                    <th class="px-4 py-2 text-left">CPL</th>
                    <th class="px-4 py-2 text-right">Total Mhs</th>
                    <th class="px-4 py-2 text-right">Tercapai</th>
                    <th class="px-4 py-2 text-right">Rata Nilai</th>
                    <th class="px-4 py-2 text-center">% Capaian</th>
                    <th class="px-4 py-2 text-center">Rubrik</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($summary as $s)
                @php $rb = cpmkRubricBadge($s->rata_nilai); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium">{{ $s->kode }}</td>
                    <td class="px-4 py-2 text-xs text-gray-600 max-w-xs">{{ Str::limit($s->deskripsi, 60) }}</td>
                    <td class="px-4 py-2 text-xs text-blue-600">{{ $s->cpl_kode }}</td>
                    <td class="px-4 py-2 text-right">{{ $s->total }}</td>
                    <td class="px-4 py-2 text-right text-green-600">{{ $s->tercapai }}</td>
                    <td class="px-4 py-2 text-right font-mono">{{ $s->rata_nilai }}</td>
                    <td class="px-4 py-2 text-center">
                        @php $p=$s->pct_achieved; $c=$p>=80?'green':($p>=60?'yellow':'red'); @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $c==='green'?'bg-green-100 text-green-700':($c==='yellow'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') }}">{{ $p }}%</span>
                    </td>
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rb['cls'] }}">{{ $rb['label'] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($mk && $rows->isNotEmpty())
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-gray-50">
            <h2 class="font-semibold text-gray-700">Detail per Mahasiswa</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-3 py-2 text-left">NIM</th>
                        <th class="px-3 py-2 text-left">Nama</th>
                        <th class="px-3 py-2 text-left">CPMK</th>
                        <th class="px-3 py-2 text-right">Tugas</th>
                        <th class="px-3 py-2 text-right">UTS</th>
                        <th class="px-3 py-2 text-right">UAS</th>
                        <th class="px-3 py-2 text-right">Partisipatif</th>
                        <th class="px-3 py-2 text-right">Proyek</th>
                        <th class="px-3 py-2 text-right">Nilai CPMK</th>
                        <th class="px-3 py-2 text-center">Achieved</th>
                        <th class="px-3 py-2 text-center">Rubrik</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($rows as $r)
                    @php $rbr = cpmkRubricBadge($r->nilai_cpmk); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-1.5 text-xs font-mono text-gray-600">{{ $r->nim }}</td>
                        <td class="px-3 py-1.5 text-xs">{{ $r->nama }}</td>
                        <td class="px-3 py-1.5"><span class="text-xs font-medium text-blue-700">{{ $r->cpmk_kode }}</span>
                            <div class="text-xs text-gray-400">{{ $r->cpl_kode }}</div>
                        </td>
                        <td class="px-3 py-1.5 text-right font-mono text-xs">{{ $r->nilai_tugas }}</td>
                        <td class="px-3 py-1.5 text-right font-mono text-xs">{{ $r->nilai_uts }}</td>
                        <td class="px-3 py-1.5 text-right font-mono text-xs">{{ $r->nilai_uas }}</td>
                        <td class="px-3 py-1.5 text-right font-mono text-xs">{{ $r->nilai_partisipatif }}</td>
                        <td class="px-3 py-1.5 text-right font-mono text-xs">{{ $r->nilai_proyek }}</td>
                        <td class="px-3 py-1.5 text-right font-mono font-bold">{{ $r->nilai_cpmk }}</td>
                        <td class="px-3 py-1.5 text-center">
                            @if($r->achieved)<span class="text-green-600 text-sm">✅</span>@else<span class="text-red-400 text-sm">❌</span>@endif
                        </td>
                        <td class="px-3 py-1.5 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rbr['cls'] }}">{{ $rbr['label'] }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($mk)
    <div class="text-center py-10 bg-white border rounded-xl text-gray-400">
        Belum ada data CPMK achievement untuk MK ini. Klik <strong>⚡ Hitung CPMK</strong>.
    </div>
    @endif
</div>
<script>
    function hitungMk(mkId) {
        const ta = document.querySelector('input[name=ta]').value;
        if (!confirm('Hitung CPMK achievement untuk MK ini?')) return;
        fetch(`/api/obe/hitung/${mkId}?ta=${encodeURIComponent(ta)}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(r => r.json()).then(d => {
                alert('✅ ' + d.cpmk_records + ' records diproses.');
                location.reload();
            })
            .catch(e => alert('Error: ' + e));
    }
</script>
@endsection