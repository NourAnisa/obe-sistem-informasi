@if(auth()->user()->role === 'admin')
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi <span class="text-red-500">*</span></label>
    <select name="program_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
        <option value="">-- Pilih Program Studi --</option>
        @foreach($programs as $prog)
            <option value="{{ $prog->id }}" {{ old('program_id', $bk?->program_id ?? '') == $prog->id ? 'selected' : '' }}>
                {{ $prog->jenjang }} {{ $prog->nama }}
            </option>
        @endforeach
    </select>
</div>
@endif

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode <span class="text-red-500">*</span>
            <span class="text-gray-400 font-normal text-xs">(contoh: BK01)</span>
        </label>
        <input type="text" name="kode" value="{{ old('kode', $bk?->kode) }}" required maxlength="10"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 outline-none uppercase"
            placeholder="BK01" oninput="this.value=this.value.toUpperCase()">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Referensi <span class="text-red-500">*</span></label>
        <select name="referensi" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="IS2020" {{ old('referensi', $bk?->referensi) === 'IS2020' ? 'selected' : '' }}>IS2020 (Information Systems)</option>
            <option value="CC2020" {{ old('referensi', $bk?->referensi) === 'CC2020' ? 'selected' : '' }}>CC2020 (Computing Curricula)</option>
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan Kajian <span class="text-red-500">*</span></label>
    <input type="text" name="nama" value="{{ old('nama', $bk?->nama) }}" required maxlength="150"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        placeholder="Contoh: Data / Information Management">
</div>