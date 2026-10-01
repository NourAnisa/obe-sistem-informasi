<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicProgram;
use App\Models\Cpl;
use App\Models\MataKuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CplController extends Controller
{
    use ResolvesPublicProgram;

    public function index(Request $request): View
    {
        $program     = $this->resolveProgram($request);
        $allPrograms = $this->allPrograms();

        $cpls = Cpl::with(['mataKuliahs', 'cpmks.subCpmks', 'profilLulusans'])
            ->when($program, fn($q) => $q->where('program_id', $program->id))
            ->orderBy('kode')
            ->get();

        $semesters = range(1, 8);
        $matrix = [];
        foreach ($cpls as $cpl) {
            $matrix[$cpl->kode] = [];
            foreach ($semesters as $sem) {
                $matrix[$cpl->kode][$sem] = $cpl->mataKuliahs
                    ->where('semester', $sem)
                    ->pluck('kode')
                    ->all();
            }
        }

        return view('cpl.index', compact('cpls', 'semesters', 'matrix', 'program', 'allPrograms'));
    }


    public function getMataKuliah(int $id): JsonResponse
    {
        $cpl = Cpl::with('mataKuliahs')->findOrFail($id);
        return response()->json($cpl->mataKuliahs);
    }

    // ── CRUD (auth: admin/kaprodi) ──────────────────────────

    public function adminIndex(): View
    {
        $cpls = Cpl::withCount(['cpmks', 'mataKuliahs'])->orderBy('kode')->get();
        return view('admin.cpl.index', compact('cpls'));
    }

    public function create(): View
    {
        return view('admin.cpl.create');
    }

    public function store(Request $request)
    {
        $programId = auth()->user()->role === 'admin'
            ? ($request->program_id ?: auth()->user()->program_id)
            : auth()->user()->program_id;

        $request->validate([
            'kode'            => ['required', 'string', 'max:20',
                                  \Illuminate\Validation\Rule::unique('cpl', 'kode')->where('program_id', $programId)],
            'deskripsi'       => 'required|string',
            'kategori'        => 'required|in:Sikap,KU,KK,PP,Sikap_KU',
            'total_skor_maks' => 'required|integer|min:0|max:100',
        ]);

        Cpl::create([
            ...$request->only('kode', 'deskripsi', 'deskripsi_en', 'kategori', 'total_skor_maks'),
            'program_id' => $programId,
        ]);

        return redirect()->route('admin.cpl.index')->with('success', 'CPL berhasil ditambahkan.');
    }

    public function edit(Cpl $cpl): View
    {
        return view('admin.cpl.edit', compact('cpl'));
    }

    public function update(Request $request, Cpl $cpl)
    {
        $programId = (auth()->user()->role === 'admin' && $request->filled('program_id'))
            ? $request->program_id
            : $cpl->program_id;

        $request->validate([
            'kode'            => ['required', 'string', 'max:20',
                                  \Illuminate\Validation\Rule::unique('cpl', 'kode')
                                    ->where('program_id', $programId)
                                    ->ignore($cpl->id)],
            'deskripsi'       => 'required|string',
            'kategori'        => 'required|in:Sikap,KU,KK,PP,Sikap_KU',
            'total_skor_maks' => 'required|integer|min:0|max:100',
        ]);

        $cpl->update([
            ...$request->only('kode', 'deskripsi', 'deskripsi_en', 'kategori', 'total_skor_maks'),
            'program_id' => $programId,
        ]);

        return redirect()->route('admin.cpl.index')->with('success', 'CPL berhasil diperbarui.');
    }

    public function destroy(Cpl $cpl)
    {
        $cpl->delete();
        return redirect()->route('admin.cpl.index')->with('success', 'CPL berhasil dihapus.');
    }
}

