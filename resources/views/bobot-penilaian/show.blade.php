@extends('layouts.dashboard')
@section('title','Bobot Penilaian SubCPMK – ' . $mk->kode)
@section('breadcrumb','Bobot Penilaian SubCPMK')
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('bobot-penilaian.index') }}"
                class="text-sm text-unism-primary hover:underline flex items-center gap-1">
                ← Kembali ke Bobot Penilaian
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2">Bobot Penilaian SubCPMK</h1>
            <p class="text-sm text-gray-500">{{ $mk->kode }} – {{ $mk->nama }}</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
        ❌ {{ $errors->first('bobot') }}
    </div>
    @endif

    @if($subCpmks->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
        <p class="text-5xl mb-3">📊</p>
        <p>Belum ada SubCPMK untuk mata kuliah ini.</p>
        <p class="text-xs mt-1">Tambahkan SubCPMK terlebih dahulu melalui menu CPMK.</p>
    </div>
    @else
    <form method="POST" action="{{ route('bobot-penilaian.save') }}">
        @csrf
        <input type="hidden" name="mk_id" value="{{ $mk->id }}">

        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full text-sm border-collapse" id="bobot-table">
                <thead class="bg-unism-primary text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold border border-unism-secondary">CPMK</th>
                        <th class="px-4 py-3 text-left font-semibold border border-unism-secondary">SubCPMK</th>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-20">Tugas</th>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-20">UTS</th>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-20">UAS</th>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-28">Partisipatif</th>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-20">Proyek</th>
                        <th class="px-4 py-3 text-center font-semibold border border-unism-secondary w-20">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subCpmks as $sub)
                    @php $b = $sub->bobot; @endphp
                    <tr class="bobot-row border-b hover:bg-gray-50 transition" data-id="{{ $sub->id }}">
                        <td class="px-4 py-2 border border-gray-200 font-medium text-unism-primary whitespace-nowrap">
                            {{ $sub->cpmk?->kode }}
                        </td>
                        <td class="px-4 py-2 border border-gray-200">
                            <span class="font-medium text-gray-800">{{ $sub->kode }}</span>
                            <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($sub->deskripsi, 60) }}</p>
                        </td>
                        <td class="px-2 py-2 border border-gray-200 text-center">
                            <input type="number" name="bobot[{{ $sub->id }}][tugas]"
                                value="{{ old('bobot.'.$sub->id.'.tugas', $b?->bobot_tugas ?? 20) }}"
                                min="0" max="100"
                                class="bobot-input w-16 border border-gray-300 rounded px-2 py-1 text-center text-sm focus:ring-2 focus:ring-unism-primary">
                        </td>
                        <td class="px-2 py-2 border border-gray-200 text-center">
                            <input type="number" name="bobot[{{ $sub->id }}][uts]"
                                value="{{ old('bobot.'.$sub->id.'.uts', $b?->bobot_uts ?? 20) }}"
                                min="0" max="100"
                                class="bobot-input w-16 border border-gray-300 rounded px-2 py-1 text-center text-sm focus:ring-2 focus:ring-unism-primary">
                        </td>
                        <td class="px-2 py-2 border border-gray-200 text-center">
                            <input type="number" name="bobot[{{ $sub->id }}][uas]"
                                value="{{ old('bobot.'.$sub->id.'.uas', $b?->bobot_uas ?? 20) }}"
                                min="0" max="100"
                                class="bobot-input w-16 border border-gray-300 rounded px-2 py-1 text-center text-sm focus:ring-2 focus:ring-unism-primary">
                        </td>
                        <td class="px-2 py-2 border border-gray-200 text-center">
                            <input type="number" name="bobot[{{ $sub->id }}][partisipatif]"
                                value="{{ old('bobot.'.$sub->id.'.partisipatif', $b?->bobot_partisipatif ?? 20) }}"
                                min="0" max="100"
                                class="bobot-input w-16 border border-gray-300 rounded px-2 py-1 text-center text-sm focus:ring-2 focus:ring-unism-primary">
                        </td>
                        <td class="px-2 py-2 border border-gray-200 text-center">
                            <input type="number" name="bobot[{{ $sub->id }}][proyek]"
                                value="{{ old('bobot.'.$sub->id.'.proyek', $b?->bobot_proyek ?? 20) }}"
                                min="0" max="100"
                                class="bobot-input w-16 border border-gray-300 rounded px-2 py-1 text-center text-sm focus:ring-2 focus:ring-unism-primary">
                        </td>
                        <td class="px-4 py-2 border border-gray-200 text-center">
                            <span class="row-total font-bold text-base">100</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex justify-between items-center">
            <p class="text-xs text-gray-500">💡 Setiap baris harus berjumlah tepat 100%. Baris merah = total belum 100.</p>
            <button type="submit"
                class="px-8 py-2.5 bg-unism-primary text-white rounded-lg font-semibold hover:bg-unism-secondary transition text-sm">
                💾 Simpan Bobot
            </button>
        </div>
    </form>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateRowTotal(row) {
            const inputs = row.querySelectorAll('.bobot-input');
            let total = 0;
            inputs.forEach(i => total += parseInt(i.value || 0, 10));
            const totalSpan = row.querySelector('.row-total');
            totalSpan.textContent = total;
            if (total === 100) {
                row.classList.remove('bg-red-50');
                row.classList.add('bg-white');
                totalSpan.classList.remove('text-red-600');
                totalSpan.classList.add('text-green-600');
            } else {
                row.classList.add('bg-red-50');
                row.classList.remove('bg-white');
                totalSpan.classList.add('text-red-600');
                totalSpan.classList.remove('text-green-600');
            }
        }

        document.querySelectorAll('.bobot-row').forEach(row => {
            updateRowTotal(row);
            row.querySelectorAll('.bobot-input').forEach(input => {
                input.addEventListener('input', () => updateRowTotal(row));
            });
        });
    });
</script>
@endsection