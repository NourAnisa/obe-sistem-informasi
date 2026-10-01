<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicProgram;
use App\Models\BahanKajian;
use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BahanKajianController extends Controller
{
    use ResolvesPublicProgram;

    public function index(Request $request): View
    {
        $program     = $this->resolveProgram($request);
        $allPrograms = $this->allPrograms();

        $bahanKajians = BahanKajian::with(['cpls', 'mataKuliahs'])
            ->when($program, fn($q) => $q->where('program_id', $program->id))
            ->orderBy('kode')
            ->get();

        $cpls = Cpl::when($program, fn($q) => $q->where('program_id', $program->id))
            ->orderBy('kode')
            ->get();

        return view('bahan-kajian.index', compact('bahanKajians', 'cpls', 'program', 'allPrograms'));
    }


    // ── CRUD (auth: admin/kaprodi) ──────────────────────────

    public function adminIndex(): View
    {
        $bahanKajians = BahanKajian::with('program')->withCount(['cpls', 'mataKuliahs'])->orderBy('kode')->get();
        return view('admin.bahan-kajian.index', compact('bahanKajians'));
    }

    public function create(): View
    {
        $programs = \App\Models\Program::orderBy('nama')->get();
        return view('admin.bahan-kajian.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $programId = $user->role === 'admin' ? $request->input('program_id') : $user->program_id;

        $request->validate([
            'program_id' => $user->role === 'admin' ? 'required|exists:programs,id' : 'nullable',
            'kode'       => [
                'required',
                'string',
                'max:10',
                \Illuminate\Validation\Rule::unique('bahan_kajian', 'kode')
                    ->where('program_id', $programId)
            ],
            'nama'      => 'required|string|max:150',
            'referensi' => 'required|in:IS2020,CC2020',
        ]);

        $data = $request->only('kode', 'nama', 'referensi');
        $data['program_id'] = $programId;

        BahanKajian::create($data);

        return redirect()->route('admin.bahan-kajian.index')->with('success', 'Bahan Kajian berhasil ditambahkan.');
    }

    public function edit(BahanKajian $bahanKajian): View
    {
        $programs = \App\Models\Program::orderBy('nama')->get();
        return view('admin.bahan-kajian.edit', compact('bahanKajian', 'programs'));
    }

    public function update(Request $request, BahanKajian $bahanKajian)
    {
        $user = Auth::user();
        $programId = $user->role === 'admin' ? $request->input('program_id') : $bahanKajian->program_id;

        $request->validate([
            'program_id' => $user->role === 'admin' ? 'required|exists:programs,id' : 'nullable',
            'kode'       => [
                'required',
                'string',
                'max:10',
                \Illuminate\Validation\Rule::unique('bahan_kajian', 'kode')
                    ->where('program_id', $programId)
                    ->ignore($bahanKajian->id)
            ],
            'nama'      => 'required|string|max:150',
            'referensi' => 'required|in:IS2020,CC2020',
        ]);

        $data = $request->only('kode', 'nama', 'referensi');
        if ($user->role === 'admin') {
            $data['program_id'] = $programId;
        }

        $bahanKajian->update($data);

        return redirect()->route('admin.bahan-kajian.index')->with('success', 'Bahan Kajian berhasil diperbarui.');
    }

    public function destroy(BahanKajian $bahanKajian)
    {
        $bahanKajian->delete();
        return redirect()->route('admin.bahan-kajian.index')->with('success', 'Bahan Kajian berhasil dihapus.');
    }
}

