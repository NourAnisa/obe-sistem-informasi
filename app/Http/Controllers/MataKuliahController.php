<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicProgram;
use App\Models\MataKuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    use ResolvesPublicProgram;

    public function index(Request $request): View
    {
        $program     = $this->resolveProgram($request);
        $allPrograms = $this->allPrograms();

        $query = MataKuliah::with(['cpls', 'bahanKajians'])
            ->when($program, fn($q) => $q->where('program_id', $program->id));

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('nama', 'like', "%$s%")->orWhere('kode', 'like', "%$s%"));
        }

        $sort = $request->input('sort', 'semester');
        $query->orderBy($sort)->orderBy('nama');

        $mataKuliahs = $query->paginate(20)->withQueryString();
        $kategoris   = MataKuliah::when($program, fn($q) => $q->where('program_id', $program->id))
            ->distinct('kategori')->pluck('kategori')->sort()->values();

        return view('mata-kuliah.index', compact('mataKuliahs', 'kategoris', 'program', 'allPrograms'));
    }


    public function show(string $kode): View
    {
        $mk = MataKuliah::where('kode', $kode)
            ->with(['cpls', 'bahanKajians', 'cpmks.subCpmks', 'bobotPenilaians.cpmk'])
            ->firstOrFail();

        return view('mata-kuliah.show', compact('mk'));
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $mks = MataKuliah::when($request->semester, fn($q, $s) => $q->where('semester', $s))
            ->select('id', 'kode', 'nama', 'sks', 'semester', 'kategori')
            ->get();
        return response()->json($mks);
    }

    // ── CRUD (auth: admin/kaprodi) ──────────────────────────

    public function adminIndex(Request $request): View
    {
        $query = MataKuliah::withCount(['cpls', 'cpmks', 'dosenMataKuliahs']);

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('nama', 'like', "%$s%")->orWhere('kode', 'like', "%$s%"));
        }
        if ($request->filled('cpl_id')) {
            $query->whereHas('cpls', fn($q) => $q->where('cpl.id', $request->cpl_id));
        }

        $mataKuliahs = $query->orderBy('semester')->orderBy('nama')
            ->paginate(25)->withQueryString();
        $kategoris   = MataKuliah::distinct('kategori')->pluck('kategori')->sort()->values();
        $filterCpl   = $request->filled('cpl_id')
            ? \App\Models\Cpl::find($request->cpl_id)
            : null;

        return view('admin.mk.index', compact('mataKuliahs', 'kategoris', 'filterCpl'));
    }

    public function create(): View
    {
        $cpls = \App\Models\Cpl::orderBy('kode')->get();
        return view('admin.mk.create', compact('cpls'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $programId = $user->role === 'admin'
            ? ($request->program_id ?: $user->program_id)
            : $user->program_id;

        $request->validate([
            'program_id'    => $user->role === 'admin' ? 'nullable|exists:programs,id' : 'nullable',
            'kode'          => [
                'required',
                'string',
                'max:30',
                \Illuminate\Validation\Rule::unique('mata_kuliah', 'kode')
                    ->where('program_id', $programId)
            ],
            'nama'          => 'required|string|max:150',
            'sks'           => 'required|integer|min:1|max:6',
            'sks_teori'     => 'nullable|integer|min:0',
            'sks_praktikum' => 'nullable|integer|min:0',
            'semester'      => 'required|integer|min:1|max:8',
            'kategori'      => 'required|in:MKF,MKPU,MKWK,MKPP,MKP,MKKP',
            'pjmk'          => 'nullable|string|max:150',
            'deskripsi'     => 'nullable|string',
        ]);

        $mk = MataKuliah::create([
            'kode'          => $request->kode,
            'nama'          => $request->nama,
            'sks'           => $request->sks,
            'sks_teori'     => $request->sks_teori ?? $request->sks,
            'sks_praktikum' => $request->sks_praktikum ?? 0,
            'semester'      => $request->semester,
            'kategori'      => $request->kategori,
            'pjmk'          => $request->pjmk,
            'deskripsi'     => $request->deskripsi,
            'is_mbkm'       => $request->boolean('is_mbkm'),
            'is_wajib'      => $request->boolean('is_wajib'),
            'program_id'    => $programId,
        ]);

        return redirect()->route('admin.mk.index')->with('success', "MK {$mk->kode} berhasil ditambahkan.");
    }

    public function edit(MataKuliah $mataKuliah): View
    {
        $cpls = \App\Models\Cpl::orderBy('kode')->get();
        return view('admin.mk.edit', compact('mataKuliah', 'cpls'));
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $user = Auth::user();
        $programId = $user->role === 'admin' && $request->filled('program_id')
            ? $request->program_id
            : ($mataKuliah->program_id ?? $user->program_id);

        $request->validate([
            'program_id'    => $user->role === 'admin' ? 'nullable|exists:programs,id' : 'nullable',
            'kode'          => [
                'required',
                'string',
                'max:30',
                \Illuminate\Validation\Rule::unique('mata_kuliah', 'kode')
                    ->where('program_id', $programId)
                    ->ignore($mataKuliah->id)
            ],
            'nama'          => 'required|string|max:150',
            'sks'           => 'required|integer|min:1|max:6',
            'sks_teori'     => 'nullable|integer|min:0',
            'sks_praktikum' => 'nullable|integer|min:0',
            'semester'      => 'required|integer|min:1|max:8',
            'kategori'      => 'required|in:MKF,MKPU,MKWK,MKPP,MKP,MKKP',
            'pjmk'          => 'nullable|string|max:150',
            'deskripsi'     => 'nullable|string',
        ]);

        $mataKuliah->update([
            'kode'          => $request->kode,
            'nama'          => $request->nama,
            'sks'           => $request->sks,
            'sks_teori'     => $request->sks_teori ?? $request->sks,
            'sks_praktikum' => $request->sks_praktikum ?? 0,
            'semester'      => $request->semester,
            'kategori'      => $request->kategori,
            'pjmk'          => $request->pjmk,
            'deskripsi'     => $request->deskripsi,
            'is_mbkm'       => $request->boolean('is_mbkm'),
            'is_wajib'      => $request->boolean('is_wajib'),
            'program_id'    => $programId,
        ]);

        return redirect()->route('admin.mk.index')->with('success', "MK {$mataKuliah->kode} berhasil diperbarui.");
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $mataKuliah->delete();
        return redirect()->route('admin.mk.index')->with('success', 'Mata Kuliah berhasil dihapus.');
    }
}
