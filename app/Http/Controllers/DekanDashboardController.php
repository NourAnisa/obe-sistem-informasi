<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\MataKuliah;
use App\Models\Cpl;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DekanDashboardController extends Controller
{
    public function index()
    {
        // Ringkasan per fakultas & prodi (tampilan lintas-prodi)
        $faculties = Faculty::withCount('programs')->orderBy('nama')->get();

        // Statistik global seluruh universitas
        $totalProdi   = Program::count();
        $totalMk      = MataKuliah::count();
        $totalSks     = MataKuliah::sum('sks');
        $totalCpl     = Cpl::count();
        $totalDosen   = User::where('role', 'dosen')->count();
        $totalKaprodi = User::where('role', 'kaprodi')->count();

        // Statistik per program studi
        $prodiStats = Program::with('faculty')
            ->withCount([
                'mataKuliahs as total_mk',
                'cpls as total_cpl',
            ])
            ->addSelect([
                'programs.*',
                DB::raw('(SELECT COUNT(DISTINCT pjmk) FROM mata_kuliah WHERE mata_kuliah.program_id = programs.id) as total_dosen'),
                DB::raw('(SELECT COALESCE(SUM(sks),0) FROM mata_kuliah WHERE mata_kuliah.program_id = programs.id) as total_sks'),
            ])
            ->orderBy('faculty_id')
            ->orderBy('nama')
            ->get();

        // MK tanpa CPL per prodi
        $mkTanpaCpl = DB::table('mata_kuliah')
            ->select('program_id', DB::raw('COUNT(*) as jumlah'))
            ->whereNotIn('id', DB::table('mata_kuliah_cpl')->select('mata_kuliah_id'))
            ->groupBy('program_id')
            ->pluck('jumlah', 'program_id');

        return view('dashboard.dekan', compact(
            'faculties',
            'prodiStats',
            'mkTanpaCpl',
            'totalProdi',
            'totalMk',
            'totalSks',
            'totalCpl',
            'totalDosen',
            'totalKaprodi'
        ));
    }
}
