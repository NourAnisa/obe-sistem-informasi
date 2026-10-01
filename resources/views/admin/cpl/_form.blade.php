@php
$kategoris = ['Sikap' => 'Sikap', 'KU' => 'Keterampilan Umum (KU)', 'KK' => 'Keterampilan Khusus (KK)', 'PP' => 'Penguasaan Pengetahuan (PP)', 'Sikap_KU' => 'Sikap & KU'];
@endphp

{{-- Hanya admin yang bisa pilih prodi --}}
@if(auth()->user()->role === 'admin')
<div class="border border-amber-100 bg-amber-50 rounded-lg p-4 mb-2">
    <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">🏛 Program Studi (Admin Override)</p>
    <select name="program_id"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
        <option value="">— Prodi saya sendiri (default) —</option>
        @foreach(\App\Models\Program::with('faculty')->orderBy('nama')->get() as $prog)
        <option value="{{ $prog->id }}"
            {{ old('program_id', $cpl?->program_id) == $prog->id ? 'selected' : '' }}>
            {{ $prog->nama }} ({{ $prog->jenjang }}) — {{ $prog->faculty->nama ?? '' }}
        </option>
        @endforeach
    </select>
    <p class="text-xs text-amber-500 mt-1">Kosongkan untuk menggunakan prodi Anda sendiri.</p>
</div>
@endif

<div>
    <input type="text" name="kode" value="{{ old('kode', $cpl?->kode) }}" required maxlength="20"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none uppercase"
        placeholder="CPL01" oninput="this.value=this.value.toUpperCase()">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
    <select name="kategori" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">— Pilih Kategori —</option>
        @foreach($kategoris as $val => $label)
        <option value="{{ $val }}" {{ old('kategori', $cpl?->kategori) === $val ? 'selected' : '' }}>
            {{ $label }}
        </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi CPL <span class="text-red-500">*</span>
        <span class="text-gray-400 font-normal text-xs">(Bahasa Indonesia)</span>
    </label>
    <textarea id="deskripsi_id" name="deskripsi" required rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-y"
        placeholder="Mampu ...">{{ old('deskripsi', $cpl?->deskripsi) }}</textarea>
</div>

<div>
    <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-gray-700">Deskripsi CPL (English)
            <span class="text-gray-400 font-normal text-xs">— untuk RPS bilingual</span>
        </label>
        <button type="button" onclick="translateMyMemory('deskripsi_id','deskripsi_en_field')"
            class="flex items-center gap-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-3 py-1.5 transition">
            🌐 Terjemahkan Otomatis (MyMemory)
        </button>
    </div>
    <textarea id="deskripsi_en_field" name="deskripsi_en" rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none resize-y bg-green-50"
        placeholder="Able to ...">{{ old('deskripsi_en', $cpl?->deskripsi_en) }}</textarea>
    <p class="text-xs text-gray-400 mt-1">Klik tombol di atas untuk menerjemahkan otomatis dari kolom Indonesia. Bisa diedit manual.</p>
</div>

<div class="w-40">
    <label class="block text-sm font-medium text-gray-700 mb-1">Skor Maksimum <span class="text-red-500">*</span></label>
    <input type="number" name="total_skor_maks" value="{{ old('total_skor_maks', $cpl?->total_skor_maks ?? 100) }}"
        required min="0" max="100"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none">
</div>

@once
@push('scripts')
<script>
async function translateMyMemory(sourceId, targetId) {
    const src = document.getElementById(sourceId);
    const tgt = document.getElementById(targetId);
    if (!src || !tgt || !src.value.trim()) {
        alert('Isi dulu kolom Bahasa Indonesia sebelum menerjemahkan.');
        return;
    }
    const btn = event.currentTarget;
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Menerjemahkan...';
    try {
        const res = await fetch(
            'https://api.mymemory.translated.net/get?q=' +
            encodeURIComponent(src.value.trim()) +
            '&langpair=id|en'
        );
        const data = await res.json();
        if (data.responseStatus === 200) {
            tgt.value = data.responseData.translatedText;
            tgt.style.borderColor = '#22c55e';
            setTimeout(() => tgt.style.borderColor = '', 2000);
        } else {
            alert('Terjemahan gagal: ' + (data.responseDetails || 'Unknown error'));
        }
    } catch (e) {
        alert('Gagal menghubungi layanan terjemahan. Periksa koneksi internet.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origText;
    }
}
</script>
@endpush
@endonce