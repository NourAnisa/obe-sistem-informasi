@extends('layouts.dashboard')
@section('title', 'Penilaian Dosen')

@push('styles')
<style>
    .card-penilaian {
        background: #fff;
        border-radius: 14px;
        border: 1.5px solid #e5e7eb;
        padding: 24px;
        max-width: 720px;
        margin: 0 auto;
    }

    .q-group {
        margin-bottom: 20px;
    }

    .q-group h4 {
        font-size: 13px;
        font-weight: 700;
        margin: 0 0 10px;
    }

    .q-item {
        margin-bottom: 12px;
    }

    .q-item p {
        font-size: 13px;
        color: #374151;
        margin: 0 0 6px;
    }

    .scale-btns {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .scale-btns label {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        color: #374151;
        cursor: pointer;
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        padding: 4px 12px;
        transition: all .15s;
    }

    .scale-btns input[type=radio] {
        display: none;
    }

    .scale-btns label.selected {
        border-color: #7c3aed;
        background: #f5f3ff;
        color: #7c3aed;
        font-weight: 700;
    }

    /* Attendance radio buttons */
    .status-opts {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .status-opt label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        border: 2px solid #d1d5db;
        border-radius: 10px;
        padding: 10px 18px;
        cursor: pointer;
        transition: all .15s;
    }

    .status-opt input[type=radio] {
        display: none;
    }

    .status-opt.hadir label.selected {
        border-color: #16a34a;
        background: #f0fdf4;
        color: #16a34a;
    }

    .status-opt.sakit label.selected {
        border-color: #dc2626;
        background: #fef2f2;
        color: #dc2626;
    }

    .status-opt.izin label.selected {
        border-color: #d97706;
        background: #fffbeb;
        color: #d97706;
    }

    .btn-submit {
        background: #7c3aed;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 12px 32px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        margin-top: 8px;
    }

    .btn-submit:hover {
        background: #6d28d9;
    }

    .token-input {
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 22px;
        letter-spacing: 8px;
        font-weight: 700;
        text-align: center;
        font-family: monospace;
        width: 100%;
        box-sizing: border-box;
        color: #7c3aed;
    }

    .meeting-row {
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .meeting-row:hover {
        background: #f5f3ff;
        border-color: #c4b5fd;
    }

    .meeting-row.done {
        cursor: default;
        opacity: .85;
    }

    .badge-done {
        background: #dcfce7;
        color: #166534;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 600;
    }

    .badge-sakit {
        background: #fee2e2;
        color: #991b1b;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 600;
    }

    .badge-izin {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 600;
    }

    .badge-tk {
        background: #f1f5f9;
        color: #475569;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 600;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 8px;
        border-radius: 99px;
        font-size: 10px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div style="padding:24px 16px;max-width:760px;margin:0 auto">

    <div style="margin-bottom:20px">
        <h1 style="font-size:20px;font-weight:800;color:#1e40af;margin:0">📝 Penilaian Dosen</h1>
        <p style="font-size:12px;color:#6b7280;margin:4px 0 0">Catat kehadiran dan berikan penilaian untuk setiap pertemuan</p>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;font-weight:600">
        {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px">
        @foreach($errors->all() as $e)<div>❌ {{ $e }}</div>@endforeach
    </div>
    @endif

    {{-- STEP 1: Select MK --}}
    <div class="card-penilaian" style="margin-bottom:20px">
        <h3 style="font-size:14px;font-weight:700;color:#374151;margin:0 0 16px">📚 Pilih Mata Kuliah</h3>
        <select id="mkSelect" onchange="loadPertemuan()"
            style="width:100%;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:13px">
            <option value="">-- Pilih Mata Kuliah --</option>
            @foreach($matkulList as $mk)
            <option value="{{ $mk->id }}">{{ $mk->kode }} – {{ $mk->nama }}</option>
            @endforeach
        </select>

        <div id="pertemuanList" style="display:none;margin-top:16px">
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Pilih Pertemuan</label>
            <div id="pertemuanItems"></div>
        </div>
    </div>

    {{-- STEP 2: Attendance + Evaluation Form --}}
    <div id="stepForm" class="card-penilaian" style="display:none">
        <h3 style="font-size:14px;font-weight:700;color:#374151;margin:0 0 4px">📋 Form Kehadiran & Penilaian</h3>
        <p id="formSubtitle" style="font-size:12px;color:#7c3aed;font-weight:600;margin:0 0 20px"></p>

        <form id="penilaianForm" method="POST" action="{{ route('bap-penilaian.store') }}"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="bap_pertemuan_id" id="formPertemuanId">
            <input type="hidden" name="pertemuan_minggu" id="formMinggu">

            {{-- ── Attendance Status ─────────────────────────────── --}}
            <div style="margin-bottom:20px;padding:16px;background:#f8fafc;border-radius:10px;border:1.5px solid #e2e8f0">
                <p style="font-size:13px;font-weight:700;color:#1e293b;margin:0 0 12px">📌 Status Kehadiran</p>
                <div class="status-opts">
                    <div class="status-opt hadir">
                        <label id="lbl-hadir" onclick="selectAttendance('hadir')">
                            <input type="radio" name="status_kehadiran" value="hadir" required>
                            ✅ Hadir
                        </label>
                    </div>
                    <div class="status-opt sakit">
                        <label id="lbl-sakit" onclick="selectAttendance('sakit')">
                            <input type="radio" name="status_kehadiran" value="sakit">
                            🤒 Sakit
                        </label>
                    </div>
                    <div class="status-opt izin">
                        <label id="lbl-izin" onclick="selectAttendance('izin')">
                            <input type="radio" name="status_kehadiran" value="izin">
                            📋 Izin
                        </label>
                    </div>
                </div>
            </div>

            {{-- ── Hadir: Token + Questionnaire ─────────────────── --}}
            <div id="sectionHadir" style="display:none">

                {{-- Questionnaire --}}
                @php
                $questions = \App\Http\Controllers\BapPenilaianController::QUESTIONS;
                $groups = [
                ['name'=>'Kompetensi Pedagogik', 'color'=>'#1e40af','bg'=>'#eff6ff','idx'=>[0,1]],
                ['name'=>'Kompetensi Profesional', 'color'=>'#065f46','bg'=>'#f0fdf4','idx'=>[2,3]],
                ['name'=>'Kompetensi Kepribadian', 'color'=>'#92400e','bg'=>'#fffbeb','idx'=>[4,5]],
                ['name'=>'Kompetensi Sosial', 'color'=>'#9d174d','bg'=>'#fdf2f8','idx'=>[6,7]],
                ];
                @endphp

                @foreach($groups as $gi => $group)
                <div class="q-group" style="background:{{ $group['bg'] }};border-radius:10px;padding:14px;margin-bottom:12px">
                    <h4 style="color:{{ $group['color'] }}">{{ $gi+1 }}. {{ $group['name'] }}</h4>
                    @foreach($group['idx'] as $qi)
                    @php $qNum = $qi+1; @endphp
                    <div class="q-item">
                        <p>{{ $qNum }}. {{ $questions[$qi] }}</p>
                        <div class="scale-btns">
                            @foreach([1=>'Kurang',2=>'Cukup',3=>'Baik'] as $val => $label)
                            <label onclick="selectScale(this)">
                                <input type="radio" name="q{{ $qNum }}" value="{{ $val }}">
                                <span>{{ $val }} – {{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach

                {{-- Token --}}
                <div style="margin-top:20px;border-top:1.5px solid #e5e7eb;padding-top:20px">
                    <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:8px">
                        🔑 Token Kehadiran
                        <span style="font-size:11px;font-weight:400;color:#6b7280"> (dari dosen · opsional)</span>
                    </label>
                    <input type="text" name="token" class="token-input" maxlength="8"
                        placeholder="000000" autocomplete="off">
                    <p style="font-size:11px;color:#9ca3af;margin:6px 0 0">Token valid → kehadiran otomatis tercatat.</p>
                </div>

                {{-- Kritik & Saran — only meeting 16 --}}
                <div id="feedbackSection" style="display:none;margin-top:16px;border:1.5px solid #c4b5fd;background:#f5f3ff;border-radius:10px;padding:16px">
                    <p style="font-size:13px;font-weight:700;color:#5b21b6;margin:0 0 4px">💬 Masukan (Pertemuan Terakhir)</p>
                    <p style="font-size:11px;color:#7c3aed;margin:0 0 14px">Sampaikan kritik dan saran untuk perbaikan pembelajaran.</p>
                    <div style="margin-bottom:12px">
                        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">📌 Kritik</label>
                        <textarea name="kritik" rows="3"
                            style="width:100%;box-sizing:border-box;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical"
                            placeholder="Hal-hal yang perlu diperbaiki..."></textarea>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:5px">💡 Saran</label>
                        <textarea name="saran" rows="3"
                            style="width:100%;box-sizing:border-box;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:13px;resize:vertical"
                            placeholder="Saran untuk meningkatkan kualitas perkuliahan..."></textarea>
                    </div>
                </div>
            </div>

            {{-- ── Sakit / Izin: File Upload ─────────────────────── --}}
            <div id="sectionBukti" style="display:none">
                <div style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:16px">
                    <p style="font-size:13px;font-weight:700;color:#92400e;margin:0 0 4px" id="buktiTitle">📎 Upload Bukti</p>
                    <p style="font-size:11px;color:#b45309;margin:0 0 12px">Upload surat dokter (sakit) atau surat izin. Format: PDF, JPG, PNG. Maks 5MB.</p>
                    <input type="file" name="bukti_file" id="buktiFile"
                        accept=".pdf,.jpg,.jpeg,.png"
                        style="width:100%;padding:8px;border:1.5px dashed #fbbf24;border-radius:8px;font-size:13px;background:#fff">
                    <p style="font-size:10px;color:#9ca3af;margin:6px 0 0">Upload bersifat opsional. Tanpa bukti tetap bisa submit.</p>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit" style="margin-top:20px;display:none">
                ✅ Kirim
            </button>
        </form>
    </div>
</div>

<script>
    const dosenPertemuan = @json($pertemuanByMk ?? []);
    const sudahEvaluasi = @json($sudahEvaluasi ?? []);
    const statusKehadiran = @json($statusKehadiran ?? []);

    const BADGE = {
        hadir: '<span class="badge-done">✅ Hadir</span>',
        sakit: '<span class="badge-sakit">🤒 Sakit</span>',
        izin: '<span class="badge-izin">📋 Izin</span>',
        tk: '<span class="badge-tk">❌ TK</span>',
    };

    function loadPertemuan() {
        const mkId = document.getElementById('mkSelect').value;
        const items = dosenPertemuan[mkId] || [];
        const container = document.getElementById('pertemuanItems');
        container.innerHTML = '';
        if (!items.length) {
            document.getElementById('pertemuanList').style.display = 'none';
            return;
        }
        items.forEach(pt => {
            const done = sudahEvaluasi.includes(parseInt(pt.id));
            const status = statusKehadiran[pt.id] ?? null;
            const badge = done ?
                (BADGE[status] ?? '<span class="badge-done">✅ Tercatat</span>') :
                '<span class="badge-pending">⏳ Belum</span>';

            const div = document.createElement('div');
            div.className = 'meeting-row' + (done ? ' done' : '');
            if (!done) div.onclick = () => openForm(pt.id, pt.minggu, pt.materi);

            let tgl = pt.tanggal ? `<span style="font-size:10px;color:#9ca3af;margin-left:6px">${pt.tanggal}</span>` : '';
            div.innerHTML = `
                <div>
                    <span style="font-size:13px;font-weight:600;color:#374151">Pertemuan ${pt.minggu}</span>
                    ${tgl}
                    ${pt.materi ? `<span style="font-size:11px;color:#6b7280;margin-left:8px">${pt.materi.substring(0,55)}</span>` : ''}
                </div>
                ${badge}
            `;
            container.appendChild(div);
        });
        document.getElementById('pertemuanList').style.display = 'block';
        document.getElementById('stepForm').style.display = 'none';
    }

    function openForm(id, minggu, materi) {
        document.getElementById('formPertemuanId').value = id;
        document.getElementById('formMinggu').value = minggu;
        document.getElementById('formSubtitle').textContent =
            `Pertemuan ke-${minggu}${materi ? ': '+materi.substring(0,60) : ''}`;

        // Reset form state
        document.getElementById('stepForm').style.display = 'block';
        document.getElementById('sectionHadir').style.display = 'none';
        document.getElementById('sectionBukti').style.display = 'none';
        document.getElementById('feedbackSection').style.display = 'none';
        document.getElementById('btnSubmit').style.display = 'none';

        document.querySelectorAll('#penilaianForm input[type=radio]').forEach(r => r.checked = false);
        document.querySelectorAll('#penilaianForm label').forEach(l => l.classList.remove('selected'));
        const tokenEl = document.querySelector('input[name=token]');
        if (tokenEl) tokenEl.value = '';
        document.getElementById('buktiFile').value = '';

        // Remove required from q inputs (will be re-added if hadir)
        document.querySelectorAll('input[name^="q"]').forEach(el => el.required = false);

        document.getElementById('stepForm').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function selectAttendance(status) {
        // Visual selection
        ['hadir', 'sakit', 'izin'].forEach(s => {
            const lbl = document.getElementById('lbl-' + s);
            if (s === status) lbl.classList.add('selected');
            else lbl.classList.remove('selected');
        });

        // Tick the hidden radio
        document.querySelector(`input[name=status_kehadiran][value="${status}"]`).checked = true;

        const minggu = parseInt(document.getElementById('formMinggu').value);

        if (status === 'hadir') {
            document.getElementById('sectionHadir').style.display = 'block';
            document.getElementById('sectionBukti').style.display = 'none';
            // Make questionnaire required
            document.querySelectorAll('input[name^="q"]').forEach(el => el.required = true);
            // Kritik/saran only for final meeting
            document.getElementById('feedbackSection').style.display = minggu === 16 ? 'block' : 'none';
        } else {
            document.getElementById('sectionHadir').style.display = 'none';
            document.getElementById('sectionBukti').style.display = 'block';
            document.querySelectorAll('input[name^="q"]').forEach(el => el.required = false);
            document.getElementById('buktiTitle').textContent = status === 'sakit' ? '📎 Upload Surat Dokter' : '📎 Upload Surat Izin';
        }

        document.getElementById('btnSubmit').style.display = 'block';
    }

    function selectScale(label) {
        const name = label.querySelector('input').name;
        document.querySelectorAll(`label:has(input[name="${name}"])`).forEach(l => l.classList.remove('selected'));
        label.classList.add('selected');
    }
</script>
@endsection