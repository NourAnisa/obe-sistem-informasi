<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\SubCpmk;
use App\Models\BahanKajian;
use App\Models\ProfilLulusan;
use App\Models\Program;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function __invoke(Request $request)
    {
        // Ambil program aktif: dari query ?program_id=X atau fallback ke yang dikonfigurasi
        $programId = $request->query('program_id');
        $program   = null;

        if ($programId) {
            $program = Program::with('faculty')->find($programId);
        }

        if (! $program) {
            $program = Program::with('faculty')
                ->whereNotNull('kode_prodi')
                ->orderBy('id')
                ->first()
                ?? Program::with('faculty')->orderBy('id')->first();
        }

        // Query scope per program
        $mkQuery  = MataKuliah::query()->when($program, fn($q) => $q->where('program_id', $program->id));
        $cplQuery = Cpl::query()->when($program, fn($q) => $q->where('program_id', $program->id));

        // CPL IDs milik program ini, untuk filter CPMK/SubCpmk/BK downstream
        $cplIds = (clone $cplQuery)->pluck('id');

        $stats = [
            'total_mk'   => (clone $mkQuery)->count(),
            'total_sks'  => (clone $mkQuery)->sum('sks'),
            'total_cpl'  => (clone $cplQuery)->count(),
            'total_cpmk' => Cpmk::whereIn('cpl_id', $cplIds)->count(),
            'total_sub'  => SubCpmk::whereHas('cpmk', fn($q) => $q->whereIn('cpl_id', $cplIds))->count(),
            'total_bk'   => BahanKajian::when($program, fn($q) => $q->where('program_id', $program->id))->count(),
        ];

        // SKS & MK per semester (untuk chart)
        $sksBySemester = (clone $mkQuery)
            ->selectRaw('semester, SUM(sks) as total_sks, COUNT(*) as total_mk')
            ->groupBy('semester')
            ->orderBy('semester')
            ->get();

        $profilLulusans = ProfilLulusan::with('cpls')
            ->when($program, fn($q) => $q->where('program_id', $program->id))
            ->orderBy('kode')
            ->get();

        $cpls     = (clone $cplQuery)->orderBy('kode')->get();
        $recentMk = (clone $mkQuery)->orderBy('semester')->take(6)->get();

        // Semua program untuk switcher
        $allPrograms = Program::with('faculty')->orderBy('nama')->get();

        // Query string untuk meneruskan program_id ke semua link halaman publik lain
        $programQuery = $program ? '?program_id=' . $program->id : '';

        // Info prodi aktif
        $prodiInfo = [
            'nama'        => $program?->nama        ?? config('obe.prodi'),
            'jenjang'     => $program?->jenjang      ?? config('obe.jenjang'),
            'fakultas'    => $program?->faculty?->nama ?? ('Fakultas ' . config('obe.fakultas')),
            'universitas' => config('obe.universitas'),
            'singkat'     => config('obe.universitas_singkat'),
            'kurikulum'   => config('obe.kurikulum'),
            'tahun_ak'    => config('obe.tahun_akademik'),
            'alamat'      => config('obe.alamat'),
            'website'     => config('obe.website'),
        ];

        return view('public.landing', compact(
            'stats', 'sksBySemester', 'profilLulusans',
            'cpls', 'recentMk', 'prodiInfo', 'allPrograms', 'program', 'programQuery'
        ));
    }
}
