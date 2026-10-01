<?php

namespace App\Http\Controllers;

use App\Models\KrsMahasiswa;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KrsMahasiswaController extends Controller
{
    private function authorize(string $action = 'full'): void
    {
        $role = Auth::user()->role ?? '';
        if ($action === 'full' && !in_array($role, ['admin', 'akademik'])) {
            abort(403, 'Akses ditolak.');
        }
        if (!in_array($role, ['admin', 'akademik', 'kaprodi'])) {
            abort(403, 'Akses ditolak.');
        }
    }

    /** GET /krs-mahasiswa */
    public function index(Request $request)
    {
        $this->authorize('read');

        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $mkId     = $request->input('mk_id');
        $kelas    = $request->input('kelas');
        $status   = $request->input('status', 'aktif');

        $query = KrsMahasiswa::with(['mahasiswa', 'mataKuliah'])
            ->where('tahun_akademik', $ta);

        if ($mkId)  $query->where('mata_kuliah_id', $mkId);
        if ($kelas) $query->where('kelas', $kelas);
        if ($status !== 'all') $query->where('status', $status);

        $krs = $query->orderBy('kelas')
            ->get()
            ->sortBy(fn($k) => $k->mahasiswa?->nim ?? '');

        $mataKuliahs  = MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $kelasList    = ['A', 'B', 'C', 'D', 'E', 'F'];
        $taList       = DB::table('krs_mahasiswa')->distinct()->pluck('tahun_akademik')
            ->push($ta)->unique()->sort()->reverse()->values();

        // Summary cards — single query instead of 3 (fixes 3x DB round-trips)
        $summaryRaw = DB::table('krs_mahasiswa')
            ->where('tahun_akademik', $ta)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as aktif,
                SUM(CASE WHEN status = 'drop' THEN 1 ELSE 0 END) as `drop`
            ")
            ->first();
        $summary = [
            'total' => $summaryRaw->total ?? 0,
            'aktif' => $summaryRaw->aktif ?? 0,
            'drop'  => $summaryRaw->drop ?? 0,
        ];

        return view('krs-mahasiswa.index', compact(
            'krs',
            'ta',
            'taList',
            'mkId',
            'kelas',
            'status',
            'mataKuliahs',
            'kelasList',
            'summary'
        ));
    }

    /** GET /krs-mahasiswa/create */
    public function create(Request $request)
    {
        $this->authorize('full');
        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $mahasiswas  = Mahasiswa::orderBy('angkatan')->orderBy('nim')->get();
        $ta          = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $kelasList   = ['A', 'B', 'C', 'D', 'E', 'F'];
        return view('krs-mahasiswa.form', compact('mataKuliahs', 'mahasiswas', 'ta', 'kelasList'));
    }

    /** POST /krs-mahasiswa */
    public function store(Request $request)
    {
        $this->authorize('full');

        $request->validate([
            'mahasiswa_id'   => 'required|exists:mahasiswas,id',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'kelas'          => 'required|max:10',
            'tahun_akademik' => 'required|max:20',
            'semester'       => 'required|integer|min:1|max:8',
            'status'         => 'required|in:aktif,drop',
        ]);

        KrsMahasiswa::updateOrCreate(
            [
                'mahasiswa_id'   => $request->mahasiswa_id,
                'mata_kuliah_id' => $request->mata_kuliah_id,
                'tahun_akademik' => $request->tahun_akademik,
            ],
            [
                'kelas'    => $request->kelas,
                'semester' => $request->semester,
                'status'   => $request->status,
            ]
        );

        return redirect()->route('krs-mahasiswa.index', ['ta' => $request->tahun_akademik])
            ->with('success', 'KRS mahasiswa berhasil disimpan.');
    }

    /** GET /krs-mahasiswa/{id}/edit */
    public function edit(KrsMahasiswa $krsMahasiswa)
    {
        $this->authorize('full');
        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('kode')->get();
        $mahasiswas  = Mahasiswa::orderBy('angkatan')->orderBy('nim')->get();
        $kelasList   = ['A', 'B', 'C', 'D', 'E', 'F'];
        return view('krs-mahasiswa.form', compact('krsMahasiswa', 'mataKuliahs', 'mahasiswas', 'kelasList'));
    }

    /** PUT /krs-mahasiswa/{id} */
    public function update(Request $request, KrsMahasiswa $krsMahasiswa)
    {
        $this->authorize('full');

        $request->validate([
            'kelas'    => 'required|max:10',
            'semester' => 'required|integer|min:1|max:8',
            'status'   => 'required|in:aktif,drop',
        ]);

        $krsMahasiswa->update($request->only('kelas', 'semester', 'status'));

        return redirect()->route('krs-mahasiswa.index', ['ta' => $krsMahasiswa->tahun_akademik])
            ->with('success', 'KRS diperbarui.');
    }

    /** DELETE /krs-mahasiswa/{id} */
    public function destroy(KrsMahasiswa $krsMahasiswa)
    {
        $this->authorize('full');
        $ta = $krsMahasiswa->tahun_akademik;
        $krsMahasiswa->delete();
        return redirect()->route('krs-mahasiswa.index', ['ta' => $ta])
            ->with('success', 'KRS dihapus.');
    }

    /**
     * POST /krs-mahasiswa/import-bulk
     * Bulk import: sync all aktif mahasiswas for a MK+kelas+TA.
     */
    public function importBulk(Request $request)
    {
        $this->authorize('full');

        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'kelas'          => 'required|max:10',
            'tahun_akademik' => 'required|max:20',
            'semester'       => 'required|integer|min:1|max:8',
            'mahasiswa_ids'  => 'required|array',
            'mahasiswa_ids.*' => 'exists:mahasiswas,id',
        ]);

        // Bulk upsert instead of N separate queries (fixes N+1)
        $data = collect($request->mahasiswa_ids)->map(fn($id) => [
            'mahasiswa_id'   => $id,
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'tahun_akademik' => $request->tahun_akademik,
            'kelas'          => $request->kelas,
            'semester'       => $request->semester,
            'status'         => 'aktif',
            'created_at'     => now(),
            'updated_at'     => now(),
        ])->toArray();

        KrsMahasiswa::upsert(
            $data,
            ['mahasiswa_id', 'mata_kuliah_id', 'tahun_akademik'],
            ['kelas', 'semester', 'status', 'updated_at']
        );

        $count = count($data);

        return redirect()->route('krs-mahasiswa.index', ['ta' => $request->tahun_akademik])
            ->with('success', "{$count} mahasiswa berhasil di-import ke KRS.");
    }
}
