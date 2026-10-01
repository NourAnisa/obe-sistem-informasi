<?php

namespace App\Http\Controllers;

use App\Exports\MahasiswaCplExport;
use App\Models\Mahasiswa;
use App\Models\MahasiswaMk;
use App\Models\MataKuliah;
use App\Models\Bap;
use App\Models\BapMahasiswaEvaluasi;
use App\Models\NilaiMahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MahasiswaDashboardController extends Controller
{
    /** GET /mahasiswa-dashboard */
    public function index()
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();

        if (!$mahasiswa) {
            return view('mahasiswa-dashboard.index', [
                'mahasiswa'    => null,
                'enrollments'  => collect(),
                'cplList'      => collect(),
                'skkmList'     => collect(),
                'skkmTotal'    => 0,
                'skkmApproved' => 0,
                'skkmPending'  => 0,
                'skkmTarget'   => 700,
                'evalCount'    => 0,
                'targetSks'    => 144,
                'sksLulus'     => 0,
                'sksSisa'      => 144,
                'sksProgress'  => 0,
                'semesterSisa' => 8,
                'sksPerSem'    => collect(),
            ]);
        }

        // MK yang diikuti
        $enrollments = MahasiswaMk::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('semester_aktif', 'desc')
            ->get();

        // CPL dari MK yang diikuti
        $mkIds = $enrollments->pluck('mata_kuliah_id')->filter()->unique();
        $cplList = collect();
        if ($mkIds->isNotEmpty()) {
            $cplList = DB::table('cpl')
                ->join('mata_kuliah_cpl', 'cpl.id', '=', 'mata_kuliah_cpl.cpl_id')
                ->whereIn('mata_kuliah_cpl.mata_kuliah_id', $mkIds)
                ->select('cpl.id', 'cpl.kode', 'cpl.deskripsi', 'cpl.deskripsi_en')
                ->distinct()
                ->orderBy('cpl.kode')
                ->get();
        }

        // SKKM mahasiswa
        $skkmList  = DB::table('skkm_mahasiswas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $skkmTotal         = $skkmList->sum('sks_ekuivalen');
        $skkmApproved      = $skkmList->where('status', 'disetujui')->sum('points_awarded');
        $skkmPending       = $skkmList->where('status', 'pending')->sum('sks_ekuivalen');
        $skkmTarget        = 700;

        // SKS Kelulusan — combine into one query (fixes redundant joins)
        $targetSksQuery = DB::table('mata_kuliah');
        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $targetSksQuery->where('program_id', Auth::user()->program_id);
        } elseif ($mahasiswa->program_id) {
            $targetSksQuery->where('program_id', $mahasiswa->program_id);
        }
        $targetSks = (int) $targetSksQuery->sum('sks') ?: 144;

        $sksPerSemQuery = DB::table('mahasiswa_mk')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'mahasiswa_mk.mata_kuliah_id')
            ->where('mahasiswa_mk.mahasiswa_id', $mahasiswa->id)
            ->where('mahasiswa_mk.status', 'lulus');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $sksPerSemQuery->where('mata_kuliah.program_id', Auth::user()->program_id);
        } elseif ($mahasiswa->program_id) {
            $sksPerSemQuery->where('mata_kuliah.program_id', $mahasiswa->program_id);
        }

        $sksPerSem = $sksPerSemQuery->select('mata_kuliah.semester', DB::raw('SUM(mata_kuliah.sks) as sks_lulus'))
            ->groupBy('mata_kuliah.semester')
            ->orderBy('mata_kuliah.semester')
            ->get();

        $sksLulus     = (int) $sksPerSem->sum('sks_lulus');
        $sksSisa      = max(0, $targetSks - $sksLulus);
        $sksProgress  = $targetSks > 0 ? round(($sksLulus / $targetSks) * 100, 1) : 0;
        $semesterSisa = $sksSisa > 0 ? (int) ceil($sksSisa / 20) : 0;

        // Evaluasi BAP yang sudah diisi
        $evalCount = BapMahasiswaEvaluasi::where('mahasiswa_id', $mahasiswa->id)->count();

        return view('mahasiswa-dashboard.index', compact(
            'mahasiswa',
            'enrollments',
            'cplList',
            'skkmList',
            'skkmTotal',
            'skkmApproved',
            'skkmPending',
            'skkmTarget',
            'evalCount',
            'targetSks',
            'sksLulus',
            'sksSisa',
            'sksProgress',
            'semesterSisa',
            'sksPerSem',
        ));
    }

    /** GET /mahasiswa-dashboard/skkm */
    public function skkmIndex()
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();
        $skkmList  = DB::table('skkm_mahasiswas')
            ->leftJoin('skkm_point_rules', 'skkm_mahasiswas.point_rule_id', '=', 'skkm_point_rules.id')
            ->leftJoin('skkm_activity_types', 'skkm_point_rules.activity_type_id', '=', 'skkm_activity_types.id')
            ->where('skkm_mahasiswas.mahasiswa_id', $mahasiswa->id)
            ->orderBy('skkm_mahasiswas.created_at', 'desc')
            ->select(
                'skkm_mahasiswas.*',
                'skkm_point_rules.role',
                'skkm_point_rules.level',
                'skkm_point_rules.points',
                'skkm_activity_types.name as activity_name',
                'skkm_activity_types.category as activity_category'
            )
            ->get();
        $skkmTotal = $skkmList->sum('sks_ekuivalen');
        $activityTypes = DB::table('skkm_activity_types')->orderBy('id')->get();
        return view('mahasiswa-dashboard.skkm', compact('mahasiswa', 'skkmList', 'skkmTotal', 'activityTypes'));
    }

    /** POST /mahasiswa-dashboard/skkm */
    public function skkmStore(Request $request)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'nama_kegiatan'    => 'required|string|max:255',
            'point_rule_id'    => 'required|exists:skkm_point_rules,id',
            'jenis_anggota'    => 'required|in:Personal,Kelompok',
            'tahun_akademik'   => 'nullable|string|max:20',
            'semester'         => 'nullable|string|max:20',
            'keterangan'       => 'nullable|string',
            'lokasi'           => 'nullable|string|max:255',
            'nomor_sk'         => 'nullable|string|max:100',
            'tanggal_sk'       => 'nullable|date',
            'google_drive_link' => 'nullable|url|max:500',
            'file_bukti'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('file_bukti')) {
            $filePath = $request->file('file_bukti')->store('skkm/bukti', 'public');
        }

        // Get the selected point rule to derive points, role, level
        $rule = DB::table('skkm_point_rules')
            ->join('skkm_activity_types', 'skkm_point_rules.activity_type_id', '=', 'skkm_activity_types.id')
            ->where('skkm_point_rules.id', $request->point_rule_id)
            ->select(
                'skkm_point_rules.*',
                'skkm_activity_types.name as activity_name',
                'skkm_activity_types.category as activity_category'
            )
            ->first();

        DB::table('skkm_mahasiswas')->insert([
            'mahasiswa_id'      => $mahasiswa->id,
            'point_rule_id'     => $request->point_rule_id,
            'nama_kegiatan'     => $request->nama_kegiatan,
            'kategori'          => $rule->activity_name ?? '-',
            'tingkat'           => $rule->level ?? '-',
            'prestasi'          => $rule->role ?? '-',
            'jenis_anggota'     => $request->jenis_anggota,
            'semester'          => $request->semester,
            'tahun_akademik'    => $request->tahun_akademik,
            'sks_ekuivalen'     => $rule->points ?? 0,
            'points_awarded'    => $rule->points ?? 0,
            'keterangan'        => $request->keterangan,
            'lokasi'            => $request->lokasi,
            'nomor_sk'          => $request->nomor_sk,
            'tanggal_sk'        => $request->tanggal_sk,
            'google_drive_link' => $request->google_drive_link,
            'file_bukti'        => $filePath,
            'status'            => 'pending',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()->route('mahasiswa.skkm')->with('success', 'Kegiatan SKKM berhasil ditambahkan!');
    }

    /** DELETE /mahasiswa-dashboard/skkm/{id} */
    public function skkmDestroy(int $id)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();
        DB::table('skkm_mahasiswas')
            ->where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->delete();
        return redirect()->route('mahasiswa.skkm')->with('success', 'Data SKKM dihapus.');
    }

    /** GET /mahasiswa-dashboard/cpl */
    public function cplIndex()
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        // CPL achievement dari tabel cpl_achievement
        $cplAchievements = DB::table('cpl_achievement')
            ->join('cpl', 'cpl.id', '=', 'cpl_achievement.cpl_id')
            ->where('cpl_achievement.mahasiswa_id', $mahasiswa->id)
            ->select(
                'cpl.kode',
                'cpl.deskripsi',
                'cpl.deskripsi_en',
                'cpl.kategori',
                'cpl_achievement.nilai_cpl',
                'cpl_achievement.threshold',
                'cpl_achievement.achieved',
                'cpl_achievement.jumlah_cpmk',
                'cpl_achievement.jumlah_achieved',
            )
            ->orderBy('cpl.kode')
            ->get();

        // CPMK achievement dari tabel cpmk_achievement
        $cpmkAchievements = DB::table('cpmk_achievement')
            ->join('cpmk', 'cpmk.id', '=', 'cpmk_achievement.cpmk_id')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'cpmk_achievement.mata_kuliah_id')
            ->where('cpmk_achievement.mahasiswa_id', $mahasiswa->id)
            ->select(
                'mata_kuliah.id as mk_id',
                'mata_kuliah.kode as mk_kode',
                'mata_kuliah.nama as mk_nama',
                'cpmk.kode as cpmk_kode',
                'cpmk.deskripsi as cpmk_deskripsi',
                'cpmk_achievement.nilai_cpmk',
                'cpmk_achievement.threshold',
                'cpmk_achievement.achieved',
                'cpmk_achievement.nilai_tugas',
                'cpmk_achievement.nilai_uts',
                'cpmk_achievement.nilai_uas',
                'cpmk_achievement.nilai_partisipatif',
                'cpmk_achievement.nilai_proyek',
            )
            ->orderBy('mata_kuliah.kode')
            ->orderBy('cpmk.kode')
            ->get();

        // Group CPMK by mata kuliah
        $cpmkGrouped = $cpmkAchievements->groupBy('mk_kode');

        // Summary stats
        $totalCpl    = $cplAchievements->count();
        $cplTercapai = $cplAchievements->where('achieved', true)->count();
        $avgCpl      = $totalCpl > 0 ? round($cplAchievements->avg('nilai_cpl'), 1) : 0;

        return view('mahasiswa-dashboard.cpl', compact(
            'mahasiswa',
            'cplAchievements',
            'cpmkGrouped',
            'totalCpl',
            'cplTercapai',
            'avgCpl',
        ));
    }

    /** GET /mahasiswa-dashboard/cpl/export */
    public function cplExport()
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        $cplAchievements = DB::table('cpl_achievement')
            ->join('cpl', 'cpl.id', '=', 'cpl_achievement.cpl_id')
            ->where('cpl_achievement.mahasiswa_id', $mahasiswa->id)
            ->select(
                'cpl.kode',
                'cpl.deskripsi',
                'cpl_achievement.nilai_cpl',
                'cpl_achievement.threshold',
                'cpl_achievement.achieved',
                'cpl_achievement.jumlah_cpmk',
                'cpl_achievement.jumlah_achieved'
            )
            ->orderBy('cpl.kode')->get();

        $cpmkAchievements = DB::table('cpmk_achievement')
            ->join('cpmk', 'cpmk.id', '=', 'cpmk_achievement.cpmk_id')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'cpmk_achievement.mata_kuliah_id')
            ->where('cpmk_achievement.mahasiswa_id', $mahasiswa->id)
            ->select(
                'mata_kuliah.kode as mk_kode',
                'mata_kuliah.nama as mk_nama',
                'cpmk.kode as cpmk_kode',
                'cpmk.deskripsi as cpmk_deskripsi',
                'cpmk_achievement.nilai_cpmk',
                'cpmk_achievement.threshold',
                'cpmk_achievement.achieved',
                'cpmk_achievement.nilai_tugas',
                'cpmk_achievement.nilai_uts',
                'cpmk_achievement.nilai_uas',
                'cpmk_achievement.nilai_partisipatif',
                'cpmk_achievement.nilai_proyek'
            )
            ->orderBy('mata_kuliah.kode')->orderBy('cpmk.kode')->get();

        $nim      = preg_replace('/[^A-Za-z0-9_]/', '_', $mahasiswa->nim ?? 'mahasiswa');
        $filename = "CPL_CPMK_{$nim}.xlsx";

        return Excel::download(
            new MahasiswaCplExport($mahasiswa, $cplAchievements, $cpmkAchievements),
            $filename
        );
    }

    /** GET /mahasiswa-dashboard/cpl/template — download blank import template */
    public function cplImportTemplate()
    {
        $headers = ['Kode CPL', 'Nilai CPL (0-100)', 'Catatan'];
        $rows    = [['CPL01', '', ''], ['CPL02', '', ''], ['CPL03', '', '']];

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) fputcsv($out, $row);
            fclose($out);
        }, 'template_import_cpl.csv', ['Content-Type' => 'text/csv']);
    }

    /** GET /mahasiswa-dashboard/krs */
    public function krsIndex(Request $request)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        $tahunAkademik  = $mahasiswa->tahun_akademik ?? '2025/2026';
        $allowedSems    = $mahasiswa->allowedSemesters();
        $maxSks         = $mahasiswa->maxSks();

        // Semester yang sedang dilihat (default: semester mahasiswa)
        $semesterView = (int) $request->query('semester', $mahasiswa->semester ?? 1);
        $semesterView = max(1, min(8, $semesterView));

        // MK yang tersedia di semester yang dilihat
        $mataKuliahs = MataKuliah::where('semester', $semesterView)
            ->orderBy('kategori')->orderBy('nama')->get();

        // MK diambil: map mk_id => enrollment record
        $enrolledRaw = MahasiswaMk::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_aktif', $tahunAkademik)->get()->keyBy('mata_kuliah_id');

        $enrolled = $enrolledRaw->map(fn($e) => $e->id);

        $totalSks = MataKuliah::whereIn('id', $enrolledRaw->keys())->sum('sks');
        $sisaSks  = max(0, $maxSks - $totalSks);

        // Stats by status
        $statusCounts = $enrolledRaw->groupBy('status')->map->count();

        // PA info
        $dosenPa = $mahasiswa->dosen_pa_id
            ? \App\Models\User::find($mahasiswa->dosen_pa_id)
            : null;

        return view('mahasiswa-dashboard.krs', compact(
            'mahasiswa',
            'mataKuliahs',
            'enrolled',
            'enrolledRaw',
            'semesterView',
            'tahunAkademik',
            'totalSks',
            'sisaSks',
            'maxSks',
            'allowedSems',
            'statusCounts',
            'dosenPa'
        ));
    }

    /** POST /mahasiswa-dashboard/krs/enroll */
    public function krsEnroll(Request $request)
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
        ]);

        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();
        $tahunAkademik = $mahasiswa->tahun_akademik ?? '2025/2026';

        $mk      = MataKuliah::findOrFail($request->mata_kuliah_id);
        $maxSks  = $mahasiswa->maxSks();
        $allowed = $mahasiswa->allowedSemesters();

        // Validasi semester MK boleh diambil
        if (!in_array($mk->semester, $allowed)) {
            return back()->with('error', 'Mata kuliah semester ' . $mk->semester . ' tidak bisa diambil pada semester ' . $mahasiswa->semester . ' Anda.');
        }

        // Hitung total SKS yang sudah diambil
        $totalSks = MataKuliah::whereIn(
            'id',
            MahasiswaMk::where('mahasiswa_id', $mahasiswa->id)
                ->where('semester_aktif', $tahunAkademik)
                ->pluck('mata_kuliah_id')
        )->sum('sks');

        if ($totalSks + $mk->sks > $maxSks) {
            return back()->with('error', "Tidak bisa menambah MK ini. Total SKS akan melebihi batas maksimal {$maxSks} SKS (IPK: {$mahasiswa->ipk}, IPS: {$mahasiswa->ips}).");
        }

        MahasiswaMk::firstOrCreate([
            'mahasiswa_id'   => $mahasiswa->id,
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'semester_aktif' => $tahunAkademik,
        ], ['is_pjmk' => false, 'status' => 'draft']);

        return back()->with('success', 'Mata kuliah berhasil ditambahkan ke KRS.');
    }

    /** DELETE /mahasiswa-dashboard/krs/{id} */
    public function krsUnenroll(int $id)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        MahasiswaMk::where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('status', ['draft', 'ditolak']) // can only cancel draft or rejected
            ->delete();

        return back()->with('success', 'Mata kuliah berhasil dibatalkan.');
    }

    /** POST /mahasiswa-dashboard/krs/ajukan — submit KRS to PA for approval */
    public function krsAjukan(Request $request)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();
        $tahunAkademik = $mahasiswa->tahun_akademik ?? '2025/2026';

        $updated = MahasiswaMk::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_aktif', $tahunAkademik)
            ->where('status', 'draft')
            ->update(['status' => 'diajukan']);

        if ($updated === 0) {
            return back()->with('error', 'Tidak ada KRS berstatus draft untuk diajukan.');
        }

        return back()->with('success', "KRS berhasil diajukan ke Dosen PA. Menunggu persetujuan.");
    }

    /** POST /mahasiswa-dashboard/krs/semester — update semester aktif mahasiswa */
    public function krsUpdateSemester(Request $request)
    {
        $request->validate([
            'semester'       => 'required|integer|min:1|max:8',
            'tahun_akademik' => 'required|string|max:20',
        ]);

        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        DB::table('mahasiswas')->where('id', $mahasiswa->id)->update([
            'semester'       => $request->semester,
            'tahun_akademik' => $request->tahun_akademik,
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'Semester aktif diperbarui.');
    }

    /** GET /mahasiswa-dashboard/nilai — mahasiswa lihat nilai sendiri */
    public function nilaiIndex(Request $request)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        $nilaiRecords = NilaiMahasiswa::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('semester_aktif', 'desc')
            ->get();

        $grouped = $nilaiRecords->groupBy('semester_aktif');

        return view('mahasiswa-dashboard.nilai', compact('mahasiswa', 'nilaiRecords', 'grouped'));
    }
}

