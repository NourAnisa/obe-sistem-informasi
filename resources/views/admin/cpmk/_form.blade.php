<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Kode CPMK <span class="text-red-500">*</span></label>
    <input type="text" name="kode" value="{{ old('kode', $cpmk?->kode) }}" required maxlength="20"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="CPMK011">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">CPL Induk <span class="text-red-500">*</span></label>
    <select name="cpl_id" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">— Pilih CPL —</option>
        @foreach($cpls as $c)
        <option value="{{ $c->id }}" {{ old('cpl_id', $cpmk?->cpl_id) == $c->id ? 'selected' : '' }}>
            {{ $c->kode }} — {{ Str::limit($c->deskripsi, 60) }}
        </option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi CPMK <span class="text-red-500">*</span>
        <span class="text-gray-400 font-normal text-xs">(Bahasa Indonesia)</span>
    </label>
    <textarea id="cpmk_deskripsi_id" name="deskripsi" required rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-y"
        placeholder="Mampu ...">{{ old('deskripsi', $cpmk?->deskripsi) }}</textarea>
</div>

<div>
    <div class="flex items-center justify-between mb-1">
        <label class="block text-sm font-medium text-gray-700">Deskripsi CPMK (English)
            <span class="text-gray-400 font-normal text-xs">— untuk RPS bilingual</span>
        </label>
        <button type="button" onclick="translateMyMemory('cpmk_deskripsi_id','cpmk_deskripsi_en')"
            class="flex items-center gap-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-3 py-1.5 transition">
            🌐 Terjemahkan Otomatis (MyMemory)
        </button>
    </div>
    <textarea id="cpmk_deskripsi_en" name="deskripsi_en" rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none resize-y bg-green-50"
        placeholder="Able to ...">{{ old('deskripsi_en', $cpmk?->deskripsi_en) }}</textarea>
    <p class="text-xs text-gray-400 mt-1">Klik tombol di atas untuk menerjemahkan otomatis. Bisa diedit manual.</p>
</div>

@if(isset($cpmk) && $cpmk?->cpl && $cpmk->cpl->bahanKajians->count())
<div class="pt-2">
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Bahan Kajian terkait (via CPL)
        <span class="text-gray-400 font-normal text-xs">— read-only, berdasarkan CPL induk</span>
    </label>
    <div class="flex flex-wrap gap-1.5 p-3 bg-gray-50 border border-gray-200 rounded-lg">
        @foreach($cpmk->cpl->bahanKajians as $bk)
        <span class="inline-flex items-center gap-1 text-xs bg-white border border-gray-300 text-gray-700 rounded px-2 py-1 font-mono">
            <span class="font-semibold">{{ $bk->kode }}</span>
            @if($bk->nama)<span class="text-gray-400 font-sans font-normal">{{ Str::limit($bk->nama, 40) }}</span>@endif
        </span>
        @endforeach
    </div>
</div>
@endif

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