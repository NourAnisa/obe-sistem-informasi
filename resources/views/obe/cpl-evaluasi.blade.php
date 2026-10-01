@extends('layouts.dashboard')
@section('title', 'Capaian CPL')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📊 Evaluasi Capaian CPL</h1>
            <p class="text-sm text-gray-500">Capaian CPL per mahasiswa berdasarkan CPMK yang telah dihitung</p>
        </div>
        <a href="{{ route('obe.dashboard') }}" class="text-sm text-blue-600 hover:underline">← Dashboard OBE</a>
    </div>

    <form method="GET" class="bg-white border rounded-xl p-4 flex gap-3 items-end">
        <div>
            <label class="text-xs text-gray-500 block mb-1">Semester Aktif</label>
            <select name="ta" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                @foreach($taList as $t)<option value="{{ $t }}" @selected($t===$ta)>{{ $t }}</option>@endforeach
                @if(!$taList->contains($ta))<option value="{{ $ta }}" selected>{{ $ta }}</option>@endif
            </select>
        </div>
    </form>

    @if($summary->isNotEmpty())
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @foreach($summary as $s)
        @php $p=$s->pct_achieved; $c=$p>=80?'green':($p>=60?'yellow':'red');
        $bg=['green'=>'bg-green-50 border-green-200','yellow'=>'bg-yellow-50 border-yellow-200','red'=>'bg-red-50 border-red-200'][$c];
        $tx=['green'=>'text-green-700','yellow'=>'text-yellow-700','red'=>'text-red-700'][$c]; @endphp
        <div class="border {{ $bg }} rounded-xl p-4">
            <div class="font-bold {{ $tx }} text-sm mb-1">{{ $s->kode }}</div>
            <div class="text-2xl font-bold {{ $tx }}">{{ $p }}%</div>
            <div class="text-xs text-gray-500">{{ $s->tercapai }}/{{ $s->total }} mhs</div>
            <div class="text-xs text-gray-400">Rata: {{ $s->rata_nilai }}</div>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $c==='green'?'bg-green-500':($c==='yellow'?'bg-yellow-500':'bg-red-500') }}" style="width:{{ min($p,100) }}%"></div>
            </div>
            <div class="text-xs mt-1 {{ $tx }}">{{ $s->kategori }}</div>
        </div>
        @endforeach
    </div>
    @endif

    @if($rows->isNotEmpty())
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="px-5 py-3 border-b bg-gray-50"><h2 class="font-semibold text-gray-700">Detail per Mahasiswa — TA {{ $ta }}</h2></div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2 text-left">NIM</th>
                    <th class="px-4 py-2 text-left">Nama</th>
                    <th class="px-4 py-2 text-left">Angkatan</th>
                    <th class="px-4 py-2 text-left">CPL</th>
                    <th class="px-4 py-2 text-left">Kategori</th>
                    <th class="px-4 py-2 text-right">Nilai CPL</th>
                    <th class="px-4 py-2 text-right">CPMK: Total</th>
                    <th class="px-4 py-2 text-right">CPMK: Achieved</th>
                    <th class="px-4 py-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($rows as $r)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-1.5 text-xs font-mono text-gray-600">{{ $r->nim }}</td>
                    <td class="px-4 py-1.5 text-xs">{{ $r->nama }}</td>
                    <td class="px-4 py-1.5 text-xs">{{ $r->angkatan }}</td>
                    <td class="px-4 py-1.5 font-medium text-blue-700">{{ $r->cpl_kode }}</td>
                    <td class="px-4 py-1.5 text-xs">{{ $r->kategori }}</td>
                    <td class="px-4 py-1.5 text-right font-mono font-bold">{{ $r->nilai_cpl }}</td>
                    <td class="px-4 py-1.5 text-right">{{ $r->jumlah_cpmk }}</td>
                    <td class="px-4 py-1.5 text-right text-green-600">{{ $r->jumlah_achieved }}</td>
                    <td class="px-4 py-1.5 text-center">
                        @if($r->achieved)<span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">✅ Tercapai</span>
                        @else<span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">❌ Belum</span>@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @else
    <div class="text-center py-10 bg-white border rounded-xl text-gray-400">
        <div class="text-4xl mb-2">📊</div>
        Belum ada data capaian CPL untuk TA <strong>{{ $ta }}</strong>.
    </div>
    @endif
</div>
@endsection