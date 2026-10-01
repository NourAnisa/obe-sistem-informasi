<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\MataKuliah;
use App\Models\MbkmBkp;
use App\Models\SubCpmk;
use Illuminate\View\View;

class AkademikDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_mk'      => MataKuliah::count(),
            'total_sks'     => MataKuliah::sum('sks'),
            'total_cpl'     => Cpl::count(),
            'total_cpmk'    => Cpmk::count(),
            'total_subcpmk' => SubCpmk::count(),
            'mk_mbkm'       => MataKuliah::where('is_mbkm', true)->count(),
            'mk_wajib'      => MataKuliah::where('is_wajib', true)->count(),
            'total_dosen'   => MataKuliah::distinct('pjmk')->count('pjmk'),
        ];

        $sksBySemester = MataKuliah::selectRaw('semester, SUM(sks) as total_sks, COUNT(*) as total_mk')
            ->groupBy('semester')->orderBy('semester')->get();

        $mkByKategori = MataKuliah::selectRaw('kategori, COUNT(*) as total, SUM(sks) as total_sks')
            ->groupBy('kategori')->get();

        // All MK grouped by semester for document matrix
        $mkBySemester = MataKuliah::with(['cpls', 'cpmks'])
            ->orderBy('semester')->orderBy('nama')
            ->get()->groupBy('semester');

        $mbkmActivities = MbkmBkp::all();

        $cplList = Cpl::withCount(['mataKuliahs', 'cpmks'])->orderBy('kode')->get();

        return view('dashboard.akademik', compact(
            'stats',
            'sksBySemester',
            'mkByKategori',
            'mkBySemester',
            'mbkmActivities',
            'cplList'
        ));
    }
}
