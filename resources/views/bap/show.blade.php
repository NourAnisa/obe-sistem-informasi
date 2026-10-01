@extends('layouts.dashboard')
@section('title', 'BAP — ' . $mk->kode)
@section('breadcrumb', 'BAP — ' . $mk->kode)

@push('styles')
<style>
    .bap-form input,
    .bap-form textarea,
    .bap-form select {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 5px 8px;
        font-size: 12px;
        line-height: 1.4;
        color: #1f2937;
        background: #fff;
    }

    .bap-form input:focus,
    .bap-form textarea:focus,
    .bap-form select:focus {
        outline: 2px solid #3b82f6;
        border-color: transparent;
    }

    .bap-week-header {
        background: linear-gradient(135deg, #1e40af, #4338ca);
        color: #fff;
    }

    .bap-uts {
        background: #fef3c7 !important;
    }

    .bap-uas {
        background: #fce7f3 !important;
    }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('bap.update', $mk->kode) }}" class="bap-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="bap_id" value="{{ $bap->id }}">

    {{-- ── TOPBAR ── --}}
    <div style="background:linear-gradient(135deg,#1e40af,#4338ca);border-radius:14px;padding:20px 24px;margin:16px;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,.18)">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <a href="{{ route('bap.index') }}" style="color:#bfdbfe;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:6px">← Kembali ke Daftar BAP</a>
                <h1 style="font-size:20px;font-weight:800;margin:0 0 4px">📋 Edit Berita Acara Perkuliahan</h1>
                <p style="font-size:14px;color:#bfdbfe;margin:0">{{ $mk->nama }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;font-size:12px;color:#bfdbfe">
                    <span>📋 <strong style="color:#fff">{{ $mk->kode }}</strong></span>
                    <span>📚 <strong style="color:#fff">{{ $mk->sks }} SKS</strong></span>
                    <span>🎓 Semester <strong style="color:#fff">{{ $mk->semester }}</strong></span>
                    <span>👨‍🏫 <strong style="color:#fff">{{ $mk->pjmk ?: '—' }}</strong></span>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route('rps.show', $mk->kode) }}"
                    style="background:rgba(255,255,255,.15);color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px;border:1px solid rgba(255,255,255,.3)">
                    📄 Lihat RPS
                </a>
                <a href="{{ route('bap.print', $mk->kode) }}" target="_blank"
                    style="background:rgba(255,255,255,.2);color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                    🖨️ Cetak BAP
                </a>
                <a href="{{ route('bap.word', $mk->kode) }}"
                    style="background:#10b981;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                    📝 Export Word
                </a>
                <a href="{{ route('bap.evaluasi.compare', $mk->kode) }}"
                    style="background:#7c3aed;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                    📊 Evaluasi Mahasiswa
                </a>
                <button type="submit"
                    style="background:#f59e0b;color:#1f2937;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                    💾 Simpan BAP
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div style="margin:0 16px 12px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:10px 16px;color:#065f46;font-weight:600;font-size:13px">
        ✅ {{ session('success') }}
    </div>
    @endif

    <div style="margin:0 16px 24px;display:grid;grid-template-columns:1fr 1fr;gap:12px">

        {{-- ── INFO BAP ── --}}
        <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;padding:18px">
            <p style="font-size:13px;font-weight:700;color:#374151;margin:0 0 12px;border-bottom:2px solid #f0f0f0;padding-bottom:8px">⚙️ Informasi BAP</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#4b5563;margin-bottom:4px">Semester Aktif</label>
                    <select name="semester_aktif" id="bap_semester_aktif" onchange="loadEnrollmentInfo()">
                        @foreach(['Ganjil 2025/2026','Genap 2025/2026','Ganjil 2026/2027','Genap 2026/2027'] as $sem)
                        <option value="{{ $sem }}" {{ $bap->semester_aktif === $sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#4b5563;margin-bottom:4px">Kelas</label>
                    <select name="kelas" id="bap_kelas" onchange="loadEnrollmentInfo()">
                        @foreach(['A','B','C','D','E','F'] as $k)
                        <option value="{{ $k }}" {{ $bap->kelas === $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#4b5563;margin-bottom:4px">Ruangan</label>
                    <input type="text" name="ruangan" value="{{ $bap->ruangan }}" placeholder="Contoh: R.101, Lab Komputer 2">
                </div>
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:#4b5563;margin-bottom:4px">
                        Jumlah Mahasiswa Terdaftar
                        <span id="krs_badge" style="font-size:10px;font-weight:normal;color:#6b7280;margin-left:4px"></span>
                    </label>
                    <input type="number" name="jumlah_mahasiswa" id="jumlah_mahasiswa"
                        value="{{ $bap->jumlah_mahasiswa }}" min="0" max="200">
                    <div id="enrollment_hint" style="font-size:10px;color:#6b7280;margin-top:3px"></div>
                </div>
            </div>
            <div style="margin-top:10px">
                <label style="display:block;font-size:11px;font-weight:600;color:#4b5563;margin-bottom:4px">Catatan</label>
                <textarea name="catatan" rows="2" placeholder="Catatan tambahan BAP...">{{ $bap->catatan }}</textarea>
            </div>
        </div>

        {{-- ── RINGKASAN MK ── --}}
        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:18px">
            <p style="font-size:13px;font-weight:700;color:#1e40af;margin:0 0 12px;border-bottom:2px solid #bfdbfe;padding-bottom:8px">📚 Info Mata Kuliah (dari RPS)</p>
            <div style="font-size:12px;color:#1e3a8a;line-height:2">
                <div><strong>Kode MK:</strong> {{ $mk->kode }}</div>
                <div><strong>Nama MK:</strong> {{ $mk->nama }}</div>
                <div><strong>SKS:</strong> {{ $mk->sks }} SKS ({{ $mk->sks_teori ?? $mk->sks }} T {{ $mk->sks_praktikum ?? 0 }} P)</div>
                <div><strong>Semester:</strong> {{ $mk->semester }}</div>
                <div><strong>Dosen PJMK:</strong> <span id="pjmk_name">{{ $mk->pjmk ?: '—' }}</span></div>
                @if($mk->rpsDetail?->dosen_pengampu)
                <div><strong>Dosen Pengampu:</strong> {{ implode(', ', $mk->rpsDetail->dosen_pengampu) }}</div>
                @endif
            </div>
        </div>

    </div>

    {{-- ── TABLE PERTEMUAN ── --}}
    <div style="margin:0 16px 32px;background:#fff;border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between">
            <p style="font-size:13px;font-weight:700;color:#374151;margin:0">📅 Rincian Pertemuan (16 Minggu)</p>
            <div style="display:flex;align-items:center;gap:8px">
                <a href="{{ route('bap.penilaian.summary', $mk->kode) }}"
                   style="font-size:11px;background:#7c3aed;color:#fff;padding:4px 12px;border-radius:99px;text-decoration:none;font-weight:600">
                    📊 Ringkasan Penilaian
                </a>
                <span style="font-size:11px;color:#6b7280;background:#f3f4f6;padding:3px 10px;border-radius:99px">Data materi dari RPS · Klik 🔑 untuk generate token</span>
            </div>
        </div>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <thead>
                    <tr style="background:#1e40af;color:#fff;font-size:11px">
                        <th style="padding:10px 8px;text-align:center;width:50px;border-right:1px solid #2563eb">No.<br>Prtm</th>
                        <th style="padding:10px 8px;text-align:center;width:110px;border-right:1px solid #2563eb">Tanggal</th>
                        <th style="padding:10px 8px;text-align:left;border-right:1px solid #2563eb;min-width:220px">Pokok Bahasan / Materi</th>
                        <th style="padding:10px 8px;text-align:left;border-right:1px solid #2563eb;width:220px">Metode Pembelajaran</th>
                        <th style="padding:10px 8px;text-align:center;width:80px;border-right:1px solid #2563eb">Jml Hadir</th>
                        <th style="padding:10px 8px;text-align:center;width:40px;border-right:1px solid #2563eb" title="Ijin">I</th>
                        <th style="padding:10px 8px;text-align:center;width:40px;border-right:1px solid #2563eb" title="Sakit">S</th>
                        <th style="padding:10px 8px;text-align:center;width:40px;border-right:1px solid #2563eb" title="Tanpa Keterangan">TK</th>
                        <th style="padding:10px 8px;text-align:left;width:130px;border-right:1px solid #2563eb">Keterangan</th>
                        <th style="padding:10px 8px;text-align:center;width:110px" title="Token Kehadiran &amp; Skor Penilaian">🔑 Token / Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bap->bapPertemuans as $pt)
                    @php
                    $isUts = (int)$pt->minggu === 8;
                    $isUas = (int)$pt->minggu === 16;
                    $rowBg = $isUts ? '#fffbeb' : ($isUas ? '#fdf2f8' : ($loop->even ? '#f9fafb' : '#fff'));
                    $evalSummary = \App\Models\BapEvaluasiMahasiswaNilai::summaryForPertemuan($pt->id);
                    @endphp
                    <tr style="background:{{ $rowBg }};border-bottom:1px solid #e5e7eb">
                        <td style="padding:6px 8px;text-align:center;font-weight:700;color:#1e40af;border-right:1px solid #f0f0f0;vertical-align:middle">
                            {{ $pt->minggu }}
                            @if($isUts)<br><span style="font-size:9px;font-weight:normal;color:#92400e">UTS</span>@endif
                            @if($isUas)<br><span style="font-size:9px;font-weight:normal;color:#9d174d">UAS</span>@endif
                        </td>
                        <td style="padding:6px 8px;border-right:1px solid #f0f0f0;vertical-align:middle">
                            <input type="date" name="pertemuan[{{ $pt->minggu }}][tanggal]"
                                value="{{ $pt->tanggal ? $pt->tanggal->format('Y-m-d') : '' }}"
                                style="font-size:11px">
                        </td>
                        <td style="padding:6px 8px;border-right:1px solid #f0f0f0;vertical-align:middle">
                            <textarea name="pertemuan[{{ $pt->minggu }}][materi]" rows="{{ $isUts || $isUas ? 2 : 3 }}"
                                placeholder="Pokok bahasan pertemuan {{ $pt->minggu }}...">{{ $pt->materi }}</textarea>
                        </td>
                        <td style="padding:6px 8px;border-right:1px solid #f0f0f0;vertical-align:middle">
                            <select name="pertemuan[{{ $pt->minggu }}][metode_pembelajaran]">
                                @foreach($metodePembelajaran as $metode)
                                <option value="{{ $metode }}" {{ $pt->metode_pembelajaran === $metode ? 'selected' : '' }}>{{ $metode }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="padding:6px 8px;border-right:1px solid #f0f0f0;vertical-align:middle">
                            <input type="number" name="pertemuan[{{ $pt->minggu }}][jumlah_hadir]"
                                value="{{ $pt->jumlah_hadir }}" min="0" max="200"
                                style="text-align:center">
                        </td>
                        <td style="padding:6px 8px;border-right:1px solid #f0f0f0;vertical-align:middle">
                            <input type="number" name="pertemuan[{{ $pt->minggu }}][jumlah_ijin]"
                                value="{{ $pt->jumlah_ijin }}" min="0" max="200"
                                style="text-align:center;width:44px" title="Ijin">
                        </td>
                        <td style="padding:6px 8px;border-right:1px solid #f0f0f0;vertical-align:middle">
                            <input type="number" name="pertemuan[{{ $pt->minggu }}][jumlah_sakit]"
                                value="{{ $pt->jumlah_sakit }}" min="0" max="200"
                                style="text-align:center;width:44px" title="Sakit">
                        </td>
                        <td style="padding:6px 8px;border-right:1px solid #f0f0f0;vertical-align:middle">
                            <input type="number" name="pertemuan[{{ $pt->minggu }}][jumlah_tk]"
                                value="{{ $pt->jumlah_tk }}" min="0" max="200"
                                style="text-align:center;width:44px" title="Tanpa Keterangan">
                        </td>
                        <td style="padding:6px 8px;vertical-align:middle;border-right:1px solid #f0f0f0">
                            <input type="text" name="pertemuan[{{ $pt->minggu }}][keterangan]"
                                value="{{ $pt->keterangan }}" placeholder="Catatan...">
                        </td>
                        {{-- Token + Skor Column --}}
                        <td style="padding:6px 8px;vertical-align:middle;text-align:center">
                            <button type="button"
                                onclick="generateToken({{ $pt->id }}, {{ $pt->minggu }})"
                                style="font-size:10px;background:#7c3aed;color:#fff;border:none;border-radius:5px;padding:3px 8px;cursor:pointer;font-weight:600;margin-bottom:3px;width:100%">
                                🔑 Token
                            </button>
                            @if($evalSummary['count'] > 0)
                            <div style="font-size:9px;color:#374151;line-height:1.6;margin-top:2px">
                                <span style="background:#dbeafe;color:#1e40af;padding:1px 4px;border-radius:3px;display:block">P:{{ $evalSummary['pedagogik'] }}</span>
                                <span style="background:#dcfce7;color:#166534;padding:1px 4px;border-radius:3px;display:block">Pr:{{ $evalSummary['profesional'] }}</span>
                                <span style="background:#fef3c7;color:#92400e;padding:1px 4px;border-radius:3px;display:block">K:{{ $evalSummary['kepribadian'] }}</span>
                                <span style="background:#fce7f3;color:#9d174d;padding:1px 4px;border-radius:3px;display:block">S:{{ $evalSummary['sosial'] }}</span>
                                <span style="font-size:8px;color:#6b7280">({{ $evalSummary['count'] }} resp)</span>
                            </div>
                            @else
                            <div style="font-size:9px;color:#9ca3af;margin-top:2px">Belum ada<br>penilaian</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── SAVE BUTTON BOTTOM ── --}}
    <div style="margin:0 16px 32px;text-align:right">
        <button type="submit"
            style="background:#1e40af;color:#fff;padding:12px 32px;border-radius:10px;font-size:14px;font-weight:700;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(30,64,175,.3)">
            💾 Simpan Semua Data BAP
        </button>
    </div>

</form>

@push('scripts')
<script>
(function () {
    const mkId = {{ $mk->id }};
    const enrollmentUrl = '{{ route("bap.enrollment-info") }}';

    function extractTa(semesterAktif) {
        // "Ganjil 2025/2026" → "2025/2026"
        const parts = semesterAktif.split(' ');
        return parts.length >= 2 ? parts.slice(1).join(' ') : semesterAktif;
    }

    window.loadEnrollmentInfo = function () {
        const semesterAktif = document.getElementById('bap_semester_aktif').value;
        const kelas         = document.getElementById('bap_kelas').value;
        const ta            = extractTa(semesterAktif);

        const badge = document.getElementById('krs_badge');
        const hint  = document.getElementById('enrollment_hint');
        const pjmk  = document.getElementById('pjmk_name');
        badge.textContent = '⏳';

        fetch(`${enrollmentUrl}?mk_id=${mkId}&ta=${encodeURIComponent(ta)}&kelas=${kelas}`)
            .then(r => r.json())
            .then(data => {
                const jmlInput = document.getElementById('jumlah_mahasiswa');
                if (data.jumlah_mahasiswa > 0) {
                    jmlInput.value = data.jumlah_mahasiswa;
                    jmlInput.style.background = '#f0fdf4';
                    jmlInput.style.color = '#065f46';
                    badge.textContent = '✅ dari KRS';
                    badge.style.color = '#059669';
                    hint.textContent = `${data.jumlah_mahasiswa} mahasiswa aktif (KRS ${ta} · Kelas ${kelas})`;
                } else {
                    badge.textContent = '⚠️ belum ada data KRS';
                    badge.style.color = '#b45309';
                    hint.textContent = 'Isi manual atau tambahkan data di menu KRS Mahasiswa.';
                    jmlInput.style.background = '';
                    jmlInput.style.color = '';
                }
                if (data.pjmk && pjmk) {
                    pjmk.textContent = data.pjmk;
                }
            })
            .catch(() => {
                badge.textContent = '';
                hint.textContent = '';
            });
    };

    // Auto-load on page open
    document.addEventListener('DOMContentLoaded', function () {
        loadEnrollmentInfo();
    });
})();
</script>

{{-- Token Generation Modal --}}
<div id="tokenModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:360px;width:90%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,0.3)">
        <p style="font-size:13px;color:#6b7280;margin:0 0 4px">Token Kehadiran — Pertemuan</p>
        <p id="tokenMinggu" style="font-size:11px;color:#9ca3af;margin:0 0 16px"></p>
        <div id="tokenCode" style="font-size:52px;font-weight:900;letter-spacing:8px;color:#7c3aed;font-family:monospace;margin:0 0 12px">------</div>
        <p style="font-size:11px;color:#6b7280;margin:0 0 16px">Berlaku 2 jam · Bagikan ke mahasiswa</p>
        <div id="tokenCountdown" style="font-size:12px;color:#059669;font-weight:600;margin-bottom:20px"></div>
        <div style="display:flex;gap:8px;justify-content:center">
            <button onclick="closeTokenModal()" style="background:#e5e7eb;color:#374151;border:none;border-radius:8px;padding:8px 20px;cursor:pointer;font-size:13px">Tutup</button>
            <button onclick="copyToken()" style="background:#7c3aed;color:#fff;border:none;border-radius:8px;padding:8px 20px;cursor:pointer;font-size:13px;font-weight:600">📋 Salin</button>
        </div>
    </div>
</div>

<script>
let countdownInterval;

function generateToken(bapPertemuanId, minggu) {
    fetch('{{ route('bap.token.generate') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ bap_pertemuan_id: bapPertemuanId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.token) {
            document.getElementById('tokenCode').textContent = data.token;
            document.getElementById('tokenMinggu').textContent = 'Pertemuan ke-' + minggu;
            document.getElementById('tokenModal').style.display = 'flex';
            // countdown
            clearInterval(countdownInterval);
            const expiredAt = new Date(data.expired_at);
            function updateCountdown() {
                const diff = expiredAt - new Date();
                if (diff <= 0) {
                    document.getElementById('tokenCountdown').textContent = 'Token telah kedaluwarsa';
                    clearInterval(countdownInterval);
                    return;
                }
                const m = Math.floor(diff / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                document.getElementById('tokenCountdown').textContent = `⏱ Sisa waktu: ${m}:${s.toString().padStart(2,'0')}`;
            }
            updateCountdown();
            countdownInterval = setInterval(updateCountdown, 1000);
        } else {
            alert(data.message || 'Gagal generate token');
        }
    })
    .catch(() => alert('Gagal terhubung ke server'));
}

function closeTokenModal() {
    document.getElementById('tokenModal').style.display = 'none';
    clearInterval(countdownInterval);
}

function copyToken() {
    const code = document.getElementById('tokenCode').textContent;
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.querySelector('#tokenModal button:last-child');
        btn.textContent = '✅ Disalin!';
        setTimeout(() => { btn.textContent = '📋 Salin'; }, 2000);
    });
}
</script>
@endpush

@endsection