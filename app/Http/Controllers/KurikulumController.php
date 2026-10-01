<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesPublicProgram;
use App\Models\MataKuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KurikulumController extends Controller
{
    use ResolvesPublicProgram;

    public function index(Request $request): View
    {
        $program     = $this->resolveProgram($request);
        $allPrograms = $this->allPrograms();

        $semesters = collect(range(1, 8))->mapWithKeys(function ($sem) use ($program) {
            $mks = MataKuliah::where('semester', $sem)
                ->when($program, fn($q) => $q->where('program_id', $program->id))
                ->with('cpls')
                ->orderBy('kategori')
                ->orderBy('nama')
                ->get();
            return [$sem => [
                'mataKuliahs' => $mks,
                'totalSks'    => $mks->sum('sks'),
                'totalMk'     => $mks->count(),
            ]];
        });

        // Dua view berbeda berdasarkan auth — dipisah eksplisit untuk VILT migration
        if (Auth::check()) {
            return view('kurikulum.dashboard', compact('semesters', 'program', 'allPrograms'));
        }
        return view('kurikulum.index', compact('semesters', 'program', 'allPrograms'));
    }

    public function getMkBySemester(int $n): JsonResponse
    {
        $mks = MataKuliah::where('semester', $n)
            ->with('cpls')
            ->get();
        return response()->json($mks);
    }
}

