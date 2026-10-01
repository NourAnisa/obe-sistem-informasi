<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicProgram;
use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PemetaanController extends Controller
{
    use ResolvesPublicProgram;

    public function index(Request $request): View
    {
        $program     = $this->resolveProgram($request);
        $allPrograms = $this->allPrograms();

        $cpls = Cpl::when($program, fn($q) => $q->where('program_id', $program->id))
            ->with(['cpmks.subCpmks', 'cpmks.mataKuliahs'])
            ->orderBy('kode')
            ->get();

        // Dua view berbeda berdasarkan auth — dipisah eksplisit untuk VILT migration
        if (Auth::check()) {
            return view('pemetaan.dashboard', compact('cpls', 'program', 'allPrograms'));
        }
        return view('pemetaan.index', compact('cpls', 'program', 'allPrograms'));
    }
}
