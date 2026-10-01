@extends('layouts.dashboard')

@section('title', 'Konfigurasi Profil Universitas')
@section('breadcrumb', 'Profil Universitas')

@push('styles')
<style>
    .settings-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .section-header {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        padding: 14px 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-header span.icon {
        width: 32px; height: 32px;
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    .section-header h3 {
        color: white; font-weight: 700; font-size: 15px; margin: 0;
    }
    .section-header p {
        color: rgba(255,255,255,0.75); font-size: 12px; margin: 0;
    }
    .form-field label {
        display: block;
        font-size: 13px; font-weight: 600; color: #374151;
        margin-bottom: 6px;
    }
    .form-field input[type=text],
    .form-field input[type=number],
    .form-field textarea,
    .form-field select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.15s;
        box-sizing: border-box;
    }
    .form-field input:focus,
    .form-field textarea:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }

    /* Logo upload zone */
    .logo-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8fafc;
        position: relative;
    }
    .logo-zone:hover, .logo-zone.drag { border-color: #3b82f6; background: #eff6ff; }
    .logo-zone input[type=file] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .logo-preview {
        max-height: 80px;
        max-width: 180px;
        object-fit: contain;
        margin: 0 auto 8px;
        display: block;
        border-radius: 8px;
    }
    .badge-role {
        display: inline-flex; align-items: center;
        padding: 3px 10px; border-radius: 99px;
        font-size: 11px; font-weight: 600;
        background: #dbeafe; color: #1d4ed8;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="mb-8 flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow">
                    <span class="text-white text-lg">⚙️</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 font-display">Konfigurasi Profil Universitas</h1>
                    <p class="text-slate-500 text-sm">Kelola identitas institusi dan logo tingkat universitas</p>
                </div>
            </div>
        </div>
        <span class="badge-role">Admin Universitas</span>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-4 text-sm font-medium">
        <span class="text-lg">✅</span> {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-4 text-sm">
        <p class="font-semibold mb-1">⚠️ Terdapat kesalahan input:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" x-data="settingsForm()">
        @csrf
        @method('PUT')

        {{-- ── IDENTITAS SISTEM ── --}}
        <div class="settings-card mb-6">
            <div class="section-header">
                <span class="icon">🏛</span>
                <div>
                    <h3>Identitas Sistem & Universitas</h3>
                    <p>Nama sistem dan nama universitas</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="form-field sm:col-span-2">
                    <label>Nama Sistem <span class="text-red-400">*</span></label>
                    <input type="text" name="nama_sistem"
                        value="{{ old('nama_sistem', $settings['nama_sistem']->value ?? 'OBE SI UNISM') }}"
                        placeholder="Contoh: OBE SI UNISM">
                    <p class="text-xs text-slate-400 mt-1">Ditampilkan di navbar dan title halaman</p>
                </div>
                <div class="form-field sm:col-span-2">
                    <label>Nama Universitas <span class="text-red-400">*</span></label>
                    <input type="text" name="universitas"
                        value="{{ old('universitas', $settings['universitas']->value ?? '') }}"
                        placeholder="Nama Universitas">
                </div>
            </div>
        </div>

        {{-- ── LOGO KAMPUS ── --}}
        <div class="settings-card mb-6">
            <div class="section-header" style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);">
                <span class="icon">🖼</span>
                <div>
                    <h3>Logo Universitas</h3>
                    <p>Format PNG/JPG/SVG, maks 2MB. Rekomendasi: transparan, rasio 1:1</p>
                </div>
            </div>
            <div class="p-6">
                <div class="form-field max-w-md mx-auto">
                    <label>Logo Kampus / Universitas</label>
                    @php $logoKampus = $settings['logo_path']->value ?? null; @endphp
                    <div class="logo-zone" id="zone-logo"
                        @dragover.prevent="$el.classList.add('drag')"
                        @dragleave="$el.classList.remove('drag')"
                        @drop.prevent="handleDrop($event, 'logo_path', 'prev-logo')">
                        <input type="file" name="logo_path" accept="image/*"
                            @change="previewImage($event, 'prev-logo')">
                        @if($logoKampus)
                        <img id="prev-logo" src="{{ Storage::disk('public')->url($logoKampus) }}"
                            class="logo-preview" alt="Logo Kampus">
                        <p class="text-xs text-slate-500 mt-1">Klik atau drag untuk ganti</p>
                        @else
                        <img id="prev-logo" class="logo-preview hidden" alt="">
                        <div class="text-slate-400 py-2">
                            <div class="text-3xl mb-2">📁</div>
                            <p class="text-sm font-medium">Klik atau drag logo ke sini</p>
                            <p class="text-xs mt-1">PNG, JPG, SVG · maks 2MB</p>
                        </div>
                        @endif
                    </div>
                    @if($logoKampus)
                    <label class="flex items-center gap-2 mt-2 text-xs text-red-500 cursor-pointer">
                        <input type="checkbox" name="delete_logo_path" class="rounded"> Hapus logo ini
                    </label>
                    @endif
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400">
                ⓘ Perubahan ini berlaku secara global di seluruh sistem.
            </p>
            <button type="submit"
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600
                       text-white font-semibold rounded-xl shadow hover:from-blue-700 hover:to-indigo-700
                       transition-all active:scale-95">
                <span>💾</span> Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function settingsForm() {
    return {
        previewImage(event, previewId) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.getElementById(previewId);
                img.src = e.target.result;
                img.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        },
        handleDrop(event, inputName, previewId) {
            event.currentTarget.classList.remove('drag');
            const file = event.dataTransfer.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const input = event.currentTarget.querySelector('input[type=file]');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.getElementById(previewId);
                img.src = e.target.result;
                img.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>
@endpush
