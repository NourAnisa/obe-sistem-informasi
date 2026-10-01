<?php

namespace App\Http\Controllers;

use App\Exports\DistribusiDosenExport;
use App\Models\DosenMataKuliah;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistribusiDosenController extends Controller
{
    private function getTaAktif(): string
    {
        $y = (int) date('Y');
        $m = (int) date('n');
        return $m >= 8 ? "$y/" . ($y + 1) : ($y - 1) . "/$y";
    }

    private function taList(): array
    {
        $years = DB::table('dosen_mata_kuliah')
            ->distinct()->pluck('tahun_akademik')->toArray();
        $cur = $this->getTaAktif();
        if (!in_array($cur, $years)) array_unshift($years, $cur);
        sort($years);
        return array_reverse($years);
    }

    /** index — list all assignments with filters */
    public function index(Request $request)
    {
        $role  = Auth::user()->role;
        $ta    = $request->input('ta', $this->getTaAktif());
        $sem   = $request->input('semester');
        $mkId  = $request->input('mk_id');

        $query = DosenMataKuliah::with(['dosen', 'mataKuliah'])
            ->whereHas('mataKuliah')
            ->where('tahun_akademik', $ta)
            ->when($sem,  fn($q) => $q->where('semester', $sem))
            ->when($mkId, fn($q) => $q->where('mata_kuliah_id', $mkId))
            ->orderBy('semester')
            ->orderBy('mata_kuliah_id');

        // Dosen only sees their own assignments
        if ($role === 'dosen') {
            $query->where('dosen_id', Auth::id());
        }

        $distribusi  = $query->get();
        $taList      = $this->taList();
        $canModify   = in_array($role, ['admin', 'akademik']);
        $mataKuliahs = \App\Models\MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $filterMk    = $mkId ? \App\Models\MataKuliah::find($mkId) : null;

        return view('distribusi-dosen.index', compact('distribusi', 'ta', 'sem', 'taList', 'canModify', 'mataKuliahs', 'mkId', 'filterMk'));
    }

    /** create — show assignment form */
    public function create(Request $request)
    {
        $this->authorizeModify();

        $dosens       = User::where('role', 'dosen')->orderBy('name')->get();
        $mataKuliahs  = MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $ta           = $this->getTaAktif();
        $preselectedMkId = $request->input('mk_id');

        return view('distribusi-dosen.form', compact('dosens', 'mataKuliahs', 'ta', 'preselectedMkId'));
    }

    /** store — save new assignment */
    public function store(Request $request)
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'dosen_id'       => 'required|exists:users,id',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'kelas'          => 'nullable|string|max:10',
            'semester'       => 'required|integer|min:1|max:8',
            'tahun_akademik' => 'required|string|max:20',
            'peran'          => 'required|in:pengampu,pengembang_rps',
            'jumlah_sks'     => 'nullable|integer|min:1|max:6',
            'status'         => 'required|in:aktif,nonaktif',
        ]);
        $exists = DosenMataKuliah::where('dosen_id',       $validated['dosen_id'])
            ->where('mata_kuliah_id', $validated['mata_kuliah_id'])
            ->where('kelas',          $validated['kelas'] ?? null)
            ->where('tahun_akademik', $validated['tahun_akademik'])
            ->where('peran',          $validated['peran'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Distribusi dosen ini sudah terdaftar.');
        }

        DosenMataKuliah::create($validated);

        return redirect()->route('distribusi-dosen.index', ['ta' => $validated['tahun_akademik']])
            ->with('success', 'Distribusi dosen berhasil ditambahkan.');
    }

    /** edit — load assignment for editing */
    public function edit(DosenMataKuliah $distribusiDosen)
    {
        $this->authorizeModify();

        $dosens      = User::where('role', 'dosen')->orderBy('name')->get();
        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $ta          = $distribusiDosen->tahun_akademik;

        return view('distribusi-dosen.form', compact('distribusiDosen', 'dosens', 'mataKuliahs', 'ta'));
    }

    /** update — save changes */
    public function update(Request $request, DosenMataKuliah $distribusiDosen)
    {
        $this->authorizeModify();

        $validated = $request->validate([
            'dosen_id'       => 'required|exists:users,id',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'kelas'          => 'nullable|string|max:10',
            'semester'       => 'required|integer|min:1|max:8',
            'tahun_akademik' => 'required|string|max:20',
            'peran'          => 'required|in:pengampu,pengembang_rps',
            'jumlah_sks'     => 'nullable|integer|min:1|max:6',
            'status'         => 'required|in:aktif,nonaktif',
        ]);

        $distribusiDosen->update($validated);

        return redirect()->route('distribusi-dosen.index', ['ta' => $validated['tahun_akademik']])
            ->with('success', 'Distribusi dosen berhasil diperbarui.');
    }

    /** destroy — remove assignment */
    public function destroy(DosenMataKuliah $distribusiDosen)
    {
        $this->authorizeModify();
        $ta = $distribusiDosen->tahun_akademik;
        $distribusiDosen->delete();

        return redirect()->route('distribusi-dosen.index', ['ta' => $ta])
            ->with('success', 'Distribusi dosen berhasil dihapus.');
    }

    /** export — stream XLSX download */
    public function export(Request $request)
    {
        $role = Auth::user()->role ?? '';
        if (!in_array($role, ['admin', 'akademik', 'kaprodi'])) {
            abort(403);
        }

        $ta  = $request->input('ta', $this->getTaAktif());
        $sem = $request->input('semester');
        $semInt = $sem ? (int) $sem : null;

        $safeTa  = str_replace('/', '-', $ta);
        $filename = "distribusi-dosen-{$safeTa}" . ($semInt ? "-smt{$semInt}" : '') . '.xlsx';

        return (new DistribusiDosenExport($ta, $semInt))->download($filename);
    }

    /** Abort if role is not admin or akademik */
    private function authorizeModify(): void
    {
        $role = Auth::user()->role ?? '';
        if (!in_array($role, ['admin', 'akademik'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah distribusi dosen.');
        }
    }
}
