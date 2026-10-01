<?php

namespace App\Http\Controllers;

use App\Models\Bap;
use App\Models\BapPertemuan;
use App\Models\KrsMahasiswa;
use App\Models\MataKuliah;
use App\Models\DosenMataKuliah;
use App\Models\User;
use App\Services\RpsGeneratorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BapController extends Controller
{
    const METODE_OPTIONS = [
        'Ceramah',
        'Ceramah & Tanya Jawab',
        'Diskusi Interaktif',
        'Diskusi Kelompok',
        'Praktikum',
        'Demonstrasi',
        'Small Group Discussion (Diskusi Kelompok Kecil)',
        'Case Study (Studi Kasus)',
        'Problem-Based Learning (PBL)',
        'Project-Based Learning (PjBL)',
        'Discovery Learning',
        'Collaborative Learning (Pembelajaran Kolaboratif)',
        'Cooperative Learning (Pembelajaran Kooperatif)',
        'Contextual Instruction (Pembelajaran Kontekstual)',
        'Self-Directed Learning (Pembelajaran Mandiri)',
        'Role Playing & Simulation (Bermain Peran & Simulasi)',
        'Ujian Tengah Semester (UTS)',
        'Ujian Akhir Semester (UAS)',
    ];

    public function __construct(private RpsGeneratorService $generator) {}

    private function findMk(string $kode): MataKuliah
    {
        return MataKuliah::where('kode', $kode)->firstOrFail();
    }

    /** Abort 403 if authenticated dosen is not assigned to this MK. */
    private function authorizeDosenMk(MataKuliah $mk): void
    {
        if ((Auth::user()->role ?? '') === 'dosen') {
            $ta = config('obe.tahun_akademik', date('Y') . '/' . (date('Y') + 1));
            $assigned = DosenMataKuliah::mkIdsForDosen(Auth::id(), $ta);
            if (!$assigned->contains($mk->id)) {
                abort(403, 'Anda tidak mengampu mata kuliah ini.');
            }
        }
    }

    private function getKaprodi(): ?User
    {
        return User::where('role', 'kaprodi')->first();
    }


    public function index()
    {
        $query = MataKuliah::withCount(['cpls', 'cpmks'])
            ->orderBy('semester')
            ->orderBy('kode');

        // Dosen only sees courses they are assigned to
        $role = Auth::user()->role ?? '';
        if ($role === 'dosen') {
            $ta = config('obe.tahun_akademik', date('Y') . '/' . (date('Y') + 1));
            $assignedIds = DosenMataKuliah::mkIdsForDosen(Auth::id(), $ta);
            $query->whereIn('id', $assignedIds);
        }

        $mataKuliahs = $query->get()->groupBy('semester');
        $bapIds = Bap::pluck('mata_kuliah_id')->toArray();

        return view('bap.index', compact('mataKuliahs', 'bapIds'));
    }

    /** GET /bap/enrollment-info?mk_id=&ta=&kelas=
     * JSON: jumlah_mahasiswa from krs_mahasiswa + PJMK from dosen_mata_kuliah
     */
    public function enrollmentInfo(Request $request)
    {
        $mkId  = $request->input('mk_id');
        $ta    = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $kelas = $request->input('kelas');

        if (!$mkId) {
            return response()->json(['jumlah_mahasiswa' => 0, 'pjmk' => null]);
        }

        $jumlah = KrsMahasiswa::countAktif((int)$mkId, $ta, $kelas ?: null);

        // PJMK: prefer dosen_mata_kuliah record
        $pjmkUser = DB::table('dosen_mata_kuliah as dmk')
            ->join('users', 'users.id', '=', 'dmk.dosen_id')
            ->where('dmk.mata_kuliah_id', $mkId)
            ->where('dmk.tahun_akademik', $ta)
            ->where('dmk.peran', 'pengampu')
            ->select('users.name', 'users.nik')
            ->first();

        // Fallback: MataKuliah.pjmk string
        $pjmkName = $pjmkUser?->name
            ?? MataKuliah::find($mkId)?->pjmk
            ?? null;

        return response()->json([
            'jumlah_mahasiswa' => $jumlah,
            'pjmk'             => $pjmkName,
            'pjmk_nik'         => $pjmkUser?->nik,
        ]);
    }

    /** GET /bap/{kode} — show & edit BAP for a MK */
    public function show(string $kode)
    {
        $mk = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $mk->load([
            'cpls',
            'cpmks.cpl',
            'cpmks.subCpmks',
            'bahanKajians',
            'bobotPenilaians',
            'teknikPenilaians',
            'rpsReferensis',
            'rpsDetail',
            'rpsPertemuans',
        ]);

        $bap = Bap::with('bapPertemuans')
            ->where('mata_kuliah_id', $mk->id)
            ->latest()
            ->first();

        if (!$bap) {
            $bap = $this->autoCreateBap($mk);
        } else {
            // Re-populate empty materi rows from RPS
            $this->fillEmptyMateri($mk, $bap);
        }

        $metodePembelajaran = self::METODE_OPTIONS;
        $kaprodi            = $this->getKaprodi();

        return view('bap.show', compact('mk', 'bap', 'metodePembelajaran', 'kaprodi'));
    }

    /** PUT /bap/{kode} — save BAP */
    public function update(Request $request, string $kode)
    {
        $mk  = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $bap = Bap::where('mata_kuliah_id', $mk->id)->findOrFail($request->bap_id);

        $bap->update([
            'semester_aktif'   => $request->semester_aktif ?? 'Ganjil 2025/2026',
            'kelas'            => $request->kelas ?? 'A',
            'ruangan'          => $request->ruangan ?? '',
            'jumlah_mahasiswa' => (int) ($request->jumlah_mahasiswa ?? 0),
            'catatan'          => $request->catatan ?? '',
        ]);

        if ($pertemuanData = $request->pertemuan) {
            $upsertRows = [];
            foreach ($pertemuanData as $minggu => $data) {
                $existing = BapPertemuan::where('bap_id', $bap->id)->where('minggu', $minggu)->first();
                if ($existing) {
                    $upsertRows[] = [
                        'id'                  => $existing->id,
                        'bap_id'              => $bap->id,
                        'minggu'              => $minggu,
                        'tanggal'             => !empty($data['tanggal']) ? $data['tanggal'] : null,
                        'materi'              => $data['materi'] ?? '',
                        'metode_pembelajaran' => $data['metode_pembelajaran'] ?? 'Ceramah',
                        'jumlah_hadir'        => (int) ($data['jumlah_hadir'] ?? 0),
                        'jumlah_ijin'         => (int) ($data['jumlah_ijin'] ?? 0),
                        'jumlah_sakit'        => (int) ($data['jumlah_sakit'] ?? 0),
                        'jumlah_tk'           => (int) ($data['jumlah_tk'] ?? 0),
                        'keterangan'          => $data['keterangan'] ?? '',
                    ];
                }
            }
            if (!empty($upsertRows)) {
                BapPertemuan::upsert(
                    $upsertRows,
                    ['id'],
                    ['tanggal', 'materi', 'metode_pembelajaran', 'jumlah_hadir', 'jumlah_ijin', 'jumlah_sakit', 'jumlah_tk', 'keterangan']
                );
            }
        }

        return redirect()->route('bap.show', $kode)->with('success', 'BAP berhasil disimpan!');
    }

    /** GET /bap/{kode}/print — printable view */
    public function printView(string $kode)
    {
        $mk  = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $mk->load(['rpsDetail']);
        $bap = Bap::with('bapPertemuans')
            ->where('mata_kuliah_id', $mk->id)
            ->latest()
            ->firstOrFail();

        $kaprodi = $this->getKaprodi();
        return view('bap.print', compact('mk', 'bap', 'kaprodi'));
    }

    /** GET /bap/{kode}/word — export Word */
    public function exportWord(string $kode)
    {
        $mk  = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $mk->load(['rpsDetail']);
        $bap = Bap::with('bapPertemuans')
            ->where('mata_kuliah_id', $mk->id)
            ->latest()
            ->firstOrFail();

        $kaprodi  = $this->getKaprodi();
        $html     = view('bap.word', compact('mk', 'bap', 'kaprodi'))->render();
        $filename = 'BAP_' . preg_replace('/[^A-Za-z0-9_]/', '_', $mk->kode) . '_' . preg_replace('/[^A-Za-z0-9]/', '', $bap->semester_aktif) . '.doc';

        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    // ── Private helpers ────────────────────────────────

    /**
     * Build materi text from RPS row: indikator (primary) + materi (secondary).
     */
    private function buildMateriFromRps(?\App\Models\RpsPertemuan $rpsRow): string
    {
        if (!$rpsRow) return '';
        $parts = [];
        if (!empty(trim($rpsRow->indikator ?? '')))  $parts[] = trim($rpsRow->indikator);
        if (
            !empty(trim($rpsRow->materi ?? ''))
            && trim($rpsRow->materi) !== trim($rpsRow->indikator ?? '')
        ) {
            $parts[] = trim($rpsRow->materi);
        }
        return implode("\n", $parts);
    }

    /**
     * Build materi text from a jadwal mingguan row (auto-generated).
     */
    private function buildMateriFromJadwal(array $j): string
    {
        $parts = [];
        if (!empty($j['indikator']) && $j['indikator'] !== '-') $parts[] = $j['indikator'];
        if (
            !empty($j['materi'])    && $j['materi']    !== '-'
            && $j['materi'] !== ($j['indikator'] ?? '')
        )          $parts[] = $j['materi'];
        return implode("\n", $parts);
    }

    private function autoCreateBap(MataKuliah $mk): Bap
    {
        $ta    = config('obe.tahun_akademik', '2025/2026');
        $kelas = 'A';

        // Auto-fill jumlah_mahasiswa from KRS enrollment
        $jumlahMhs = KrsMahasiswa::countAktif($mk->id, $ta, $kelas);
        if ($jumlahMhs === 0) {
            // Try without kelas filter (any class)
            $jumlahMhs = KrsMahasiswa::countAktif($mk->id, $ta, null);
        }

        $bap = Bap::create([
            'mata_kuliah_id'   => $mk->id,
            'semester_aktif'   => 'Ganjil ' . $ta,
            'kelas'            => $kelas,
            'ruangan'          => '',
            'jumlah_mahasiswa' => $jumlahMhs,
            'catatan'          => '',
        ]);

        $pertemuanMap = $mk->rpsPertemuans->keyBy('minggu');

        // Fall back to auto-generated jadwal when no manual rps_pertemuan
        $jadwalMap = [];
        if ($mk->rpsPertemuans->isEmpty()) {
            $rpsData = $this->generator->generate($mk);
            foreach ($rpsData['jadwalMingguan'] as $j) {
                $jadwalMap[$j['minggu']] = $j;
            }
        }

        for ($minggu = 1; $minggu <= 16; $minggu++) {
            $rpsRow = $pertemuanMap->get($minggu);
            $jadwal = $jadwalMap[$minggu] ?? null;
            $isUts  = $minggu === 8;
            $isUas  = $minggu === 16;

            if ($isUts) {
                $metode = 'Ujian Tengah Semester (UTS)';
                $materi = 'Ujian Tengah Semester – Materi Pertemuan 1–7';
            } elseif ($isUas) {
                $metode = 'Ujian Akhir Semester (UAS)';
                $materi = 'Ujian Akhir Semester – Materi Pertemuan 9–15';
            } elseif ($rpsRow) {
                $metode = $this->mapRpsMetode($rpsRow->metode_sinkron ?? '');
                $materi = $this->buildMateriFromRps($rpsRow);
            } elseif ($jadwal) {
                $metode = $this->mapRpsMetode($jadwal['metode'] ?? '');
                $materi = $this->buildMateriFromJadwal($jadwal);
            } else {
                $metode = 'Ceramah';
                $materi = '';
            }

            BapPertemuan::create([
                'bap_id'              => $bap->id,
                'minggu'              => $minggu,
                'tanggal'             => null,
                'materi'              => $materi,
                'metode_pembelajaran' => $metode,
                'jumlah_hadir'        => 0,
                'jumlah_ijin'         => 0,
                'jumlah_sakit'        => 0,
                'jumlah_tk'           => 0,
                'keterangan'          => '',
            ]);
        }

        return $bap->load('bapPertemuans');
    }

    /**
     * Fill empty materi cells in an existing BAP from RPS / generator.
     */
    private function fillEmptyMateri(MataKuliah $mk, Bap $bap): void
    {
        $emptyRows = $bap->bapPertemuans->filter(
            fn($p) => in_array((int)$p->minggu, [8, 16])
                ? false
                : empty(trim($p->materi ?? ''))
        );

        if ($emptyRows->isEmpty()) return;

        $pertemuanMap = $mk->rpsPertemuans->keyBy('minggu');

        // Get jadwal mingguan as fallback
        $jadwalMap = [];
        $rpsData   = $this->generator->generate($mk);
        foreach ($rpsData['jadwalMingguan'] as $j) {
            $jadwalMap[$j['minggu']] = $j;
        }

        foreach ($emptyRows as $bapRow) {
            $minggu = (int)$bapRow->minggu;
            $rpsRow = $pertemuanMap->get($minggu);
            $jadwal = $jadwalMap[$minggu] ?? null;

            if ($rpsRow) {
                $materi = $this->buildMateriFromRps($rpsRow);
                $metode = $this->mapRpsMetode($rpsRow->metode_sinkron ?? '');
            } elseif ($jadwal && $jadwal['type'] === 'content') {
                $materi = $this->buildMateriFromJadwal($jadwal);
                $metode = $this->mapRpsMetode($jadwal['metode'] ?? '');
            } else {
                continue;
            }

            if (!empty($materi)) {
                $bapRow->update([
                    'materi'              => $materi,
                    'metode_pembelajaran' => $bapRow->metode_pembelajaran === 'Ceramah'
                        ? $metode : $bapRow->metode_pembelajaran,
                ]);
            }
        }

        $bap->load('bapPertemuans');
    }

    private function mapRpsMetode(string $raw): string
    {
        $lower = strtolower($raw);
        if (str_contains($lower, 'pjbl') || str_contains($lower, 'project'))    return 'Project-Based Learning (PjBL)';
        if (str_contains($lower, 'pbl') || str_contains($lower, 'problem'))     return 'Problem-Based Learning (PBL)';
        if (str_contains($lower, 'case') || str_contains($lower, 'studi kasus')) return 'Case Study (Studi Kasus)';
        if (str_contains($lower, 'discovery'))   return 'Discovery Learning';
        if (str_contains($lower, 'collaborative')) return 'Collaborative Learning (Pembelajaran Kolaboratif)';
        if (str_contains($lower, 'cooperative'))  return 'Cooperative Learning (Pembelajaran Kooperatif)';
        if (str_contains($lower, 'contextual'))   return 'Contextual Instruction (Pembelajaran Kontekstual)';
        if (str_contains($lower, 'role'))         return 'Role Playing & Simulation (Bermain Peran & Simulasi)';
        if (str_contains($lower, 'small group'))  return 'Small Group Discussion (Diskusi Kelompok Kecil)';
        if (str_contains($lower, 'diskusi'))      return 'Diskusi Kelompok';
        if (str_contains($lower, 'praktikum'))    return 'Praktikum';
        if (str_contains($lower, 'demonstrasi') || str_contains($lower, 'demo')) return 'Demonstrasi';
        return 'Ceramah & Tanya Jawab';
    }
}
