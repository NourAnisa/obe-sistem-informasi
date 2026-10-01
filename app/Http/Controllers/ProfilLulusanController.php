<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicProgram;
use App\Models\ProfilLulusan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilLulusanController extends Controller
{
    use ResolvesPublicProgram;

    public function index(Request $request): View
    {
        $program     = $this->resolveProgram($request);
        $allPrograms = $this->allPrograms();

        $profilLulusans = ProfilLulusan::with('cpls')
            ->when($program, fn($q) => $q->where('program_id', $program->id))
            ->orderBy('kode')
            ->get();

        return view('profil-lulusan.index', compact('profilLulusans', 'program', 'allPrograms'));
    }
}
