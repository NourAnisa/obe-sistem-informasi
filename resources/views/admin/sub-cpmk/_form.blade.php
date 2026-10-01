<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Sub-CPMK <span class="text-red-500">*</span></label>
    <input type="text" name="kode" value="{{ old('kode', $subCpmk?->kode) }}" required maxlength="30"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="Sub-CPMK011">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">CPMK Induk <span class="text-red-500">*</span></label>
    <select name="cpmk_id" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">— Pilih CPMK —</option>
        @foreach($cpmks as $c)
        <option value="{{ $c->id }}" {{ old('cpmk_id', $subCpmk?->cpmk_id) == $c->id ? 'selected' : '' }}>
            {{ $c->kode }} ({{ $c->cpl?->kode }}) — {{ Str::limit($c->deskripsi, 55) }}
        </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Sub-CPMK <span class="text-red-500">*</span>
        <span class="text-gray-400 font-normal text-xs">(Bahasa Indonesia)</span>
    </label>
    <textarea id="subcpmk_deskripsi_id" name="deskripsi" required rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-y"
        placeholder="Mampu ...">{{ old('deskripsi', $subCpmk?->deskripsi) }}</textarea>
</div>

<div>
    <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-gray-700">Deskripsi Sub-CPMK (English)
            <span class="text-gray-400 font-normal text-xs">— untuk RPS bilingual</span>
        </label>
        <button type="button" onclick="translateMyMemory('subcpmk_deskripsi_id','subcpmk_deskripsi_en')"
            class="flex items-center gap-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-3 py-1.5 transition">
            🌐 Terjemahkan Otomatis (MyMemory)
        </button>
    </div>
    <textarea id="subcpmk_deskripsi_en" name="deskripsi_en" rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none resize-y bg-green-50"
        placeholder="Able to ...">{{ old('deskripsi_en', $subCpmk?->deskripsi_en) }}</textarea>
    <p class="text-xs text-gray-400 mt-1">Klik tombol di atas untuk menerjemahkan otomatis. Bisa diedit manual.</p>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
    <input type="text" name="catatan" value="{{ old('catatan', $subCpmk?->catatan) }}" maxlength="255"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="Catatan tambahan...">
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