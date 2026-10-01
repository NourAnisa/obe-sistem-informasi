{{--
    Reusable Traffic Light Grid partial.
    Required variables: $cohortByAngkatan (Collection), $cplList (Collection)
    Optional: $widgetTitle (string), $ta (string)
--}}
@php
$widgetTitle = $widgetTitle ?? 'Traffic Light — Capaian CPL × Angkatan';
@endphp

<div class="bg-white rounded-xl border shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b bg-gray-50 flex flex-wrap items-center justify-between gap-2">
        <h2 class="font-semibold text-gray-700">🚦 {{ $widgetTitle }}</h2>
        <div class="flex flex-wrap gap-3 text-xs text-gray-600">
            <span class="flex items-center gap-1.5">
                <span class="w-4 h-4 rounded bg-green-500 inline-block"></span>
                <span>≥ 80% — Tercapai</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-4 h-4 rounded bg-yellow-400 inline-block"></span>
                <span>60–79% — Perlu Monitoring</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-4 h-4 rounded bg-red-500 inline-block"></span>
                <span>&lt; 60% — Belum Tercapai</span>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-4 h-4 rounded bg-gray-200 inline-block"></span>
                <span>Tidak ada data</span>
            </span>
        </div>
    </div>

    @if($cohortByAngkatan->isEmpty())
    <div class="px-5 py-8 text-center text-gray-400 text-sm">
        Belum ada data evaluasi angkatan. Jalankan kalkulasi OBE terlebih dahulu.
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-4 py-3 text-left text-gray-600 font-semibold text-xs uppercase tracking-wide whitespace-nowrap">
                        Angkatan
                    </th>
                    @foreach($cplList as $cpl)
                    <th class="px-2 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wide"
                        title="{{ $cpl->deskripsi }}">
                        {{ $cpl->kode }}
                    </th>
                    @endforeach
                    <th class="px-3 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wide">
                        Rata<br>CPL
                    </th>
                    <th class="px-3 py-3 text-center text-gray-600 font-semibold text-xs uppercase tracking-wide">
                        Mhs
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($cohortByAngkatan as $angk => $rows)
                @php
                $avgPct = $rows->avg('pct_lulus');
                $totalMhs = $rows->max('total_mahasiswa') ?? 0;
                @endphp
                <tr class="border-b hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-2 font-bold text-gray-800 whitespace-nowrap">{{ $angk }}</td>

                    @foreach($cplList as $cpl)
                    @php
                    $cell = $rows->firstWhere('cpl_id', $cpl->id);
                    if ($cell) {
                    $pct = (float) $cell->pct_lulus;
                    [$bg, $text, $ring] = match(true) {
                    $pct >= 80 => ['bg-green-500', 'text-white', 'ring-green-300'],
                    $pct >= 60 => ['bg-yellow-400', 'text-gray-900', 'ring-yellow-200'],
                    default => ['bg-red-500', 'text-white', 'ring-red-300'],
                    };
                    $icon = match(true) {
                    $pct >= 80 => '🟢',
                    $pct >= 60 => '🟡',
                    default => '🔴',
                    };
                    $tooltip = "{$cell->jumlah_tercapai}/{$cell->total_mahasiswa} mhs tercapai | target: {$cell->target_capaian}%";
                    }
                    @endphp
                    <td class="px-1.5 py-2 text-center">
                        @if($cell)
                        <div class="{{ $bg }} {{ $text }} rounded-lg px-1 py-1.5 min-w-[52px] inline-flex flex-col items-center
                                    ring-2 {{ $ring }} cursor-default select-none"
                            title="{{ $tooltip }}">
                            <span class="font-bold text-sm leading-tight">{{ number_format($pct, 0) }}%</span>
                            <span class="text-xs opacity-75 leading-tight">{{ $cell->jumlah_tercapai }}/{{ $cell->total_mahasiswa }}</span>
                        </div>
                        @else
                        <div class="bg-gray-100 text-gray-400 rounded-lg px-2 py-2 min-w-[52px] inline-block text-center text-xs">
                            —
                        </div>
                        @endif
                    </td>
                    @endforeach

                    {{-- Row summary --}}
                    <td class="px-3 py-2 text-center">
                        @php
                        $avgColor = $avgPct >= 80 ? 'text-green-700 bg-green-50' : ($avgPct >= 60 ? 'text-yellow-700 bg-yellow-50' : 'text-red-700 bg-red-50');
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $avgColor }}">
                            {{ number_format($avgPct, 1) }}%
                        </span>
                    </td>
                    <td class="px-3 py-2 text-center text-gray-500 text-xs">{{ $totalMhs }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Column summary row --}}
    <div class="px-5 py-3 bg-gray-50 border-t">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <tr>
                    <td class="pr-4 text-gray-500 font-semibold whitespace-nowrap">Avg semua angkatan:</td>
                    @foreach($cplList as $cpl)
                    @php
                    $allRows = $cohortByAngkatan->flatten()->where('cpl_id', $cpl->id);
                    $colAvg = $allRows->isNotEmpty() ? $allRows->avg('pct_lulus') : null;
                    $colColor = is_null($colAvg) ? 'text-gray-400'
                    : ($colAvg >= 80 ? 'text-green-700 font-bold' : ($colAvg >= 60 ? 'text-yellow-700 font-bold' : 'text-red-700 font-bold'));
                    @endphp
                    <td class="px-2 text-center {{ $colColor }}">
                        {{ is_null($colAvg) ? '—' : number_format($colAvg, 0).'%' }}
                    </td>
                    @endforeach
                    <td colspan="2"></td>
                </tr>
            </table>
        </div>
    </div>
    @endif
</div>