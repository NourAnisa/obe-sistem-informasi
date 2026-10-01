<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use App\Models\PortfolioEvidence;
use App\Models\StudentPortfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * PortfolioController
 *
 * Portfolio mahasiswa yang terhubung langsung ke evidence CPL/CPMK.
 * Setiap evidence di-link ke satu atau lebih CPMK (bukan upload bebas).
 *
 * Routes:
 *   mahasiswa → index, show, store, update, destroy, submitEvidence
 *   dosen/kaprodi → review, approve, reject
 */
class PortfolioController extends Controller
{
    // ── Mahasiswa ────────────────────────────────────────────────────────

    /**
     * GET /portfolio
     * Dashboard portfolio mahasiswa login.
     */
    public function index()
    {
        $mahasiswaId = Auth::user()->mahasiswa?->id
            ?? abort(403, 'Akun ini tidak terhubung ke data mahasiswa.');

        $ta        = config('obe.tahun_akademik', '2025/2026');
        $portfolio = StudentPortfolio::firstOrCreate(
            ['mahasiswa_id' => $mahasiswaId],
            ['semester_aktif' => $ta, 'status' => 'active']
        );

        // Semua CPMK yang diambil mahasiswa semester ini
        $cpmkList = DB::table('cpmk as c')
            ->join('mata_kuliah_cpmk as mkc', 'mkc.cpmk_id', '=', 'c.id')
            ->join('mahasiswa_mk as mmk', 'mmk.mata_kuliah_id', '=', 'mkc.mata_kuliah_id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'mkc.mata_kuliah_id')
            ->where('mmk.mahasiswa_id', $mahasiswaId)
            ->where('mmk.semester_aktif', $ta)
            ->where('mmk.status', 'disetujui')
            ->select('c.id', 'c.kode', 'c.deskripsi', 'mk.nama as mk_nama')
            ->orderBy('c.kode')
            ->get();

        // Evidence per CPMK — link via pivot evidence_cpmk_mapping
        $evidencesRaw = $portfolio->evidences()
            ->with(['cpmkMappings', 'subCpmk', 'reviewedBy'])
            ->latest()
            ->get();

        // Coverage CPL dari evidence yang approved/under_review
        $coveredCpmkIds = $evidencesRaw
            ->whereIn('status', ['approved', 'under_review'])
            ->pluck('cpmkMappings')
            ->flatten()
            ->pluck('id')
            ->unique();

        $summary = $portfolio->getPortfolioSummary();
        $summary['cpmk_total']    = $cpmkList->count();
        $summary['cpmk_covered']  = $coveredCpmkIds->count();
        $summary['cpmk_coverage_pct'] = $cpmkList->count() > 0
            ? round($coveredCpmkIds->count() / $cpmkList->count() * 100)
            : 0;

        $evidencesByStatus = $evidencesRaw->groupBy('status');

        return view('portfolio.dashboard', compact(
            'portfolio', 'cpmkList', 'evidencesRaw',
            'evidencesByStatus', 'summary', 'ta'
        ));
    }

    /**
     * POST /portfolio/evidence
     * Upload evidence baru + map ke CPMK.
     */
    public function storeEvidence(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'evidence_type'    => 'required|in:assignment,project,certification,reflection,peer_feedback',
            'description'      => 'nullable|string|max:2000',
            'reflection_notes' => 'nullable|string|max:3000',
            'external_link'    => 'nullable|url|max:500',
            'file'             => 'nullable|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,zip',
            'cpmk_ids'         => 'required|array|min:1',
            'cpmk_ids.*'       => 'integer|exists:cpmk,id',
            'demonstration_level.*' => 'nullable|integer|min:1|max:5',
        ]);

        $mahasiswaId = Auth::user()->mahasiswa?->id ?? abort(403);
        $ta          = config('obe.tahun_akademik', '2025/2026');

        $portfolio = StudentPortfolio::firstOrCreate(
            ['mahasiswa_id' => $mahasiswaId],
            ['semester_aktif' => $ta, 'status' => 'active']
        );

        // Upload file jika ada
        $filePath = $fileName = $fileMime = $fileSize = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $filePath = $file->store("portfolio/{$mahasiswaId}", 'private');
            $fileName = $file->getClientOriginalName();
            $fileMime = $file->getMimeType();
            $fileSize = $file->getSize();
        }

        $evidence = PortfolioEvidence::create([
            'student_portfolio_id' => $portfolio->id,
            'evidence_type'        => $request->evidence_type,
            'title'                => $request->title,
            'description'          => $request->description,
            'reflection_notes'     => $request->reflection_notes,
            'external_link'        => $request->external_link,
            'file_path'            => $filePath,
            'file_name'            => $fileName,
            'file_mime_type'       => $fileMime,
            'file_size'            => $fileSize,
            'status'               => 'draft',
        ]);

        // Attach CPMK mapping (pivot: evidence_cpmk_mapping)
        $cpmkSync = [];
        foreach ($request->cpmk_ids as $cpmkId) {
            $cpmkSync[$cpmkId] = [
                'demonstration_level' => $request->input("demonstration_level.{$cpmkId}"),
                'comment'             => null,
            ];
        }
        $evidence->cpmkMappings()->sync($cpmkSync);

        $portfolio->updateCompletionPercentage();

        return back()->with('success', 'Evidence berhasil ditambahkan. Submit ketika siap untuk direview.');
    }

    /**
     * POST /portfolio/evidence/{id}/submit
     * Submit evidence untuk direview dosen.
     */
    public function submitEvidence(int $id)
    {
        $evidence = PortfolioEvidence::findOrFail($id);
        abort_unless($evidence->portfolio->mahasiswa_id === Auth::user()->mahasiswa?->id, 403);
        abort_unless(in_array($evidence->status, ['draft', 'rejected']), 422, 'Status tidak valid untuk di-submit.');

        $evidence->submit();

        return back()->with('success', 'Evidence berhasil di-submit untuk direview.');
    }

    /**
     * DELETE /portfolio/evidence/{id}
     * Hapus evidence (hanya boleh jika masih draft).
     */
    public function destroyEvidence(int $id)
    {
        $evidence = PortfolioEvidence::findOrFail($id);
        abort_unless($evidence->portfolio->mahasiswa_id === Auth::user()->mahasiswa?->id, 403);
        abort_unless($evidence->status === 'draft', 422, 'Hanya evidence berstatus draft yang bisa dihapus.');

        if ($evidence->file_path) {
            Storage::disk('private')->delete($evidence->file_path);
        }
        $evidence->cpmkMappings()->detach();
        $evidence->delete();
        $evidence->portfolio->updateCompletionPercentage();

        return back()->with('success', 'Evidence dihapus.');
    }

    // ── Dosen / Kaprodi ──────────────────────────────────────────────────

    /**
     * GET /portfolio/review
     * Daftar semua evidence yang perlu direview (submitted / under_review).
     */
    public function reviewIndex(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $evidences = PortfolioEvidence::with([
            'portfolio.mahasiswa', 'cpmkMappings', 'subCpmk',
        ])
        ->whereHas('portfolio', fn($q) => $q->where('semester_aktif', $ta))
        ->whereIn('status', ['submitted', 'under_review'])
        ->latest()
        ->paginate(20);

        $taList = StudentPortfolio::distinct()->orderByDesc('semester_aktif')->pluck('semester_aktif');

        return view('portfolio.review-index', compact('evidences', 'ta', 'taList'));
    }

    /**
     * POST /portfolio/evidence/{id}/approve
     */
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'feedback' => 'nullable|string|max:1000',
            'rating'   => 'nullable|integer|min:1|max:5',
        ]);

        $evidence = PortfolioEvidence::findOrFail($id);
        $evidence->approve(Auth::id(), $request->feedback, $request->rating);

        return back()->with('success', "Evidence '{$evidence->title}' disetujui.");
    }

    /**
     * POST /portfolio/evidence/{id}/reject
     */
    public function reject(Request $request, int $id)
    {
        $request->validate(['feedback' => 'required|string|max:1000']);

        $evidence = PortfolioEvidence::findOrFail($id);
        $evidence->reject(Auth::id(), $request->feedback);

        return back()->with('warning', "Evidence '{$evidence->title}' ditolak.");
    }

    /**
     * GET /portfolio/evidence/{id}/download
     * Download file evidence (private storage).
     */
    public function downloadFile(int $id)
    {
        $evidence = PortfolioEvidence::findOrFail($id);

        // Hanya mahasiswa pemilik, dosen reviewer, atau admin
        $user = Auth::user();
        $isMahasiswaPemilik = $user->mahasiswa?->id === $evidence->portfolio->mahasiswa_id;
        $isDosen = in_array($user->role, ['dosen', 'kaprodi', 'admin']);

        abort_unless($isMahasiswaPemilik || $isDosen, 403);
        abort_unless($evidence->file_path && Storage::disk('private')->exists($evidence->file_path), 404);

        return Storage::disk('private')->download($evidence->file_path, $evidence->file_name);
    }
}
