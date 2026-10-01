<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicProgram;
use App\Models\MbkmBkp;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MbkmController extends Controller
{
    use ResolvesPublicProgram;

    public function index(Request $request): View
    {
        $program     = $this->resolveProgram($request);
        $allPrograms = $this->allPrograms();

        $bkps = MbkmBkp::orderBy('no')->get();

        $distribusi = collect(range(1, 8))->mapWithKeys(function ($sem) use ($program) {
            $mks = MataKuliah::where('semester', $sem)
                ->when($program, fn($q) => $q->where('program_id', $program->id))
                ->get();
            return [$sem => [
                'MKF'  => $mks->where('kategori', 'MKF')->values(),
                'MKPU' => $mks->where('kategori', 'MKPU')->values(),
                'MKWK' => $mks->where('kategori', 'MKWK')->values(),
                'MKP'  => $mks->where('kategori', 'MKP')->values(),
                'MKPP' => $mks->where('kategori', 'MKPP')->values(),
                'MKKP' => $mks->where('kategori', 'MKKP')->values(),
            ]];
        });

        return view('mbkm.index', compact('bkps', 'distribusi', 'program', 'allPrograms'));
    }
}

