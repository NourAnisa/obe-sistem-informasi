<?php

namespace App\Http\Controllers;

use App\Models\Bap;
use App\Models\BapEvaluasiMahasiswaNilai;
use App\Models\BapPertemuan;
use App\Models\BapToken;
use App\Models\KrsMahasiswa;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BapPenilaianController extends Controller
{
    // 8 evaluation questions (flat array, grouped by 2 per competency)
    const QUESTIONS = [
        'Dosen mengaitkan materi dengan pengalaman nyata atau kasus aktual yang relevan dengan bidang studi',     // q1 - pedagogik
        'Dosen menyesuaikan kecepatan penyampaian materi dengan kemampuan pemahaman mahasiswa',                   // q2 - pedagogik
        'Dosen menunjukkan penguasaan materi melalui penjelasan mendalam dan mampu menjawab pertanyaan kritis',   // q3 - profesional
        'Dosen memberikan contoh implementasi praktis (alat, software, studi kasus industri, atau penelitian terbaru)', // q4 - profesional
        'Dosen memberikan umpan balik yang membangun terhadap kesalahan mahasiswa tanpa menjatuhkan',             // q5 - kepribadian
        'Dosen konsisten antara rencana pembelajaran (RPS) dengan pelaksanaan di kelas',                          // q6 - kepribadian
        'Dosen menciptakan suasana kelas yang mendorong mahasiswa berani menyampaikan pendapat',                  // q7 - sosial
        'Dosen merespon pertanyaan atau diskusi mahasiswa dengan interaktif dan tidak satu arah',                  // q8 - sosial
    ];

    const SCALE = [1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik'];

    // ══════════════════════════════════════════════════════
    // DOSEN: Generate Token
    // ══════════════════════════════════════════════════════

    /**
     * POST /bap/token/generate
     * Dosen generates a 6-digit token for a bap_pertemuan.
     * Returns JSON: {token, expired_at, expires_in_minutes}
     */
    public function generateToken(Request $request)
    {
        $request->validate(['bap_pertemuan_id' => 'required|exists:bap_pertemuan,id']);

        $pertemuan = BapPertemuan::findOrFail($request->bap_pertemuan_id);

        // Generate unique 6-digit numeric token
        do {
            $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            BapToken::where('bap_pertemuan_id', $pertemuan->id)
            ->where('expired_at', '>', now())
            ->where('token', $token)
            ->exists()
        );

        $expiredAt = now()->addHours(2);

        // Invalidate old tokens for this meeting (optional: keep history)
        BapToken::create([
            'bap_pertemuan_id' => $pertemuan->id,
            'token'            => $token,
            'expired_at'       => $expiredAt,
            'created_by'       => Auth::id(),
        ]);

        return response()->json([
            'token'               => $token,
            'expired_at'          => $expiredAt->toIso8601String(),
            'expires_in_minutes'  => 120,
            'pertemuan_minggu'    => $pertemuan->minggu,
        ]);
    }

    // ══════════════════════════════════════════════════════
    // MAHASISWA: Evaluation Portal
    // ══════════════════════════════════════════════════════

    /**
     * GET /bap-penilaian
     * Mahasiswa selects MK and meeting to evaluate.
     */
    public function index(Request $request)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();
        $ta        = config('obe.tahun_akademik', '2025/2026');

        // Get MKs from krs_mahasiswa; fall back to mahasiswa_mk
        $krsList = KrsMahasiswa::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik', $ta)
            ->where('status', 'aktif')
            ->get();

        if ($krsList->isEmpty()) {
            $mkIds = DB::table('mahasiswa_mk')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->pluck('mata_kuliah_id');
            $matkulList = MataKuliah::whereIn('id', $mkIds)->orderBy('kode')->get();
        } else {
            $matkulList = $krsList->map(fn($k) => $k->mataKuliah)->unique('id')->filter()->values();
        }

        // Build pertemuanByMk: {mk_id: [{id,minggu,materi,tanggal}]}
        $pertemuanByMk = [];
        $allPertemuanIds = [];
        foreach ($matkulList as $mk) {
            $bap = Bap::where('mata_kuliah_id', $mk->id)->latest()->first();
            if ($bap) {
                $pts = $bap->bapPertemuans->map(fn($pt) => [
                    'id'      => $pt->id,
                    'minggu'  => $pt->minggu,
                    'materi'  => $pt->materi,
                    'tanggal' => $pt->tanggal?->toDateString(),
                ])->values()->toArray();
                $pertemuanByMk[$mk->id] = $pts;
                foreach ($pts as $pt) {
                    $allPertemuanIds[] = $pt['id'];
                }
            }
        }

        // Auto-mark TK for overdue meetings with no submission
        if (!empty($allPertemuanIds)) {
            BapEvaluasiMahasiswaNilai::autoMarkTk($mahasiswa->id, $allPertemuanIds);
        }

        // IDs this mahasiswa already submitted for (including TK auto-marks)
        $sudahEvaluasi = BapEvaluasiMahasiswaNilai::where('mahasiswa_id', $mahasiswa->id)
            ->pluck('bap_pertemuan_id')
            ->map(fn($id) => (int)$id)
            ->toArray();

        // Status kehadiran per pertemuan (for badge display)
        $statusKehadiran = BapEvaluasiMahasiswaNilai::where('mahasiswa_id', $mahasiswa->id)
            ->pluck('status_kehadiran', 'bap_pertemuan_id')
            ->toArray();

        return view('bap-penilaian.index', compact(
            'mahasiswa',
            'matkulList',
            'pertemuanByMk',
            'sudahEvaluasi',
            'statusKehadiran',
            'ta'
        ));
    }

    /**
     * POST /bap-penilaian
     * Mahasiswa submits evaluation for one meeting.
     */
    public function store(Request $request)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'bap_pertemuan_id'  => 'required|exists:bap_pertemuan,id',
            'status_kehadiran'  => 'required|in:hadir,sakit,izin',
        ]);

        $statusKehadiran = $request->status_kehadiran;

        // Branch-specific validation
        if ($statusKehadiran === 'hadir') {
            $request->validate([
                'token' => 'nullable|string|max:8',
                'q1' => 'required|integer|between:1,3',
                'q2' => 'required|integer|between:1,3',
                'q3' => 'required|integer|between:1,3',
                'q4' => 'required|integer|between:1,3',
                'q5' => 'required|integer|between:1,3',
                'q6' => 'required|integer|between:1,3',
                'q7' => 'required|integer|between:1,3',
                'q8' => 'required|integer|between:1,3',
            ]);
        } else {
            $request->validate([
                'bukti_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        }

        $pertemuan = BapPertemuan::findOrFail($request->bap_pertemuan_id);

        // Prevent duplicate submission
        if (BapEvaluasiMahasiswaNilai::where('bap_pertemuan_id', $pertemuan->id)
            ->where('mahasiswa_id', $mahasiswa->id)->exists()
        ) {
            return back()->with('error', 'Anda sudah mengisi kehadiran untuk pertemuan ini.');
        }

        $data = [
            'mahasiswa_id'     => $mahasiswa->id,
            'bap_pertemuan_id' => $pertemuan->id,
            'status_kehadiran' => $statusKehadiran,
        ];

        if ($statusKehadiran === 'hadir') {
            // Validate token
            $tokenInput = trim($request->token ?? '');
            $hadirBool  = false;
            if ($tokenInput !== '') {
                $hadirBool = BapToken::where('bap_pertemuan_id', $pertemuan->id)
                    ->where('token', $tokenInput)
                    ->where('expired_at', '>', now())
                    ->exists();
            }

            // Category averages
            $pedagogik   = round(((int)$request->q1 + (int)$request->q2) / 2, 2);
            $profesional = round(((int)$request->q3 + (int)$request->q4) / 2, 2);
            $kepribadian = round(((int)$request->q5 + (int)$request->q6) / 2, 2);
            $sosial      = round(((int)$request->q7 + (int)$request->q8) / 2, 2);

            // Kritik & saran only for final meeting
            $isFinal = (int)$pertemuan->minggu === 16;
            $data += [
                'pedagogik'   => $pedagogik,
                'profesional' => $profesional,
                'kepribadian' => $kepribadian,
                'sosial'      => $sosial,
                'token_input' => $tokenInput ?: null,
                'hadir'       => $hadirBool,
                'kritik'      => $isFinal ? (trim($request->kritik ?? '') ?: null) : null,
                'saran'       => $isFinal ? (trim($request->saran  ?? '') ?: null) : null,
            ];
        } else {
            // sakit / izin
            $data += [
                'pedagogik'   => 0,
                'profesional' => 0,
                'kepribadian' => 0,
                'sosial'      => 0,
                'hadir'       => false,
            ];

            // Handle file upload
            if ($request->hasFile('bukti_file')) {
                $path = $request->file('bukti_file')->store(
                    'bap-bukti/' . $mahasiswa->id,
                    'public'
                );
                $data['bukti_file'] = $path;
            }
        }

        DB::transaction(function () use ($data, $pertemuan, $statusKehadiran) {
            BapEvaluasiMahasiswaNilai::create($data);
            if ($statusKehadiran === 'hadir' && ($data['hadir'] ?? false)) {
                $pertemuan->increment('jumlah_hadir');
            } elseif ($statusKehadiran === 'sakit') {
                $pertemuan->increment('jumlah_sakit');
            } elseif ($statusKehadiran === 'izin') {
                $pertemuan->increment('jumlah_ijin');
            }
        });

        $messages = [
            'hadir' => ($data['hadir'] ?? false)
                ? '✅ Kehadiran & evaluasi berhasil dicatat!'
                : '📝 Evaluasi tersimpan. Token tidak valid — kehadiran tidak tercatat.',
            'sakit' => '🤒 Ketidakhadiran (sakit) berhasil dicatat.',
            'izin'  => '📋 Ketidakhadiran (izin) berhasil dicatat.',
        ];

        return redirect()->route('bap-penilaian.index')->with('success', $messages[$statusKehadiran]);
    }

    // ══════════════════════════════════════════════════════
    // DOSEN / KAPRODI: Summary per meeting
    // ══════════════════════════════════════════════════════

    /**
     * GET /bap/{kode}/penilaian-summary
     * Dosen sees average scores per meeting.
     */
    public function summary(string $kode)
    {
        $mk  = MataKuliah::where('kode', $kode)->firstOrFail();
        $bap = Bap::with('bapPertemuans')->where('mata_kuliah_id', $mk->id)->latest()->firstOrFail();

        $pertemuans = $bap->bapPertemuans->sortBy('minggu')->values();

        $summaries = [];
        foreach ($pertemuans as $pt) {
            $summaries[$pt->id] = BapEvaluasiMahasiswaNilai::summaryForPertemuan($pt->id);
        }

        return view('bap-penilaian.summary', compact('mk', 'bap', 'pertemuans', 'summaries'));
    }

    /**
     * GET /bap/token/status?bap_pertemuan_id=X
     * Returns current token status for dosen (AJAX).
     */
    public function tokenStatus(Request $request)
    {
        $id = $request->input('bap_pertemuan_id');
        $token = BapToken::where('bap_pertemuan_id', $id)
            ->where('expired_at', '>', now())
            ->latest()->first();

        if (!$token) {
            return response()->json(['active' => false]);
        }

        $minutesLeft = (int) now()->diffInMinutes($token->expired_at, false);
        return response()->json([
            'active'      => true,
            'token'       => $token->token,
            'minutes_left' => max(0, $minutesLeft),
            'expired_at'  => $token->expired_at->format('H:i'),
        ]);
    }
}
