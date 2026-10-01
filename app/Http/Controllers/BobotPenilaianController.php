<?php

namespace App\Http\Controllers;

use App\Models\BobotPenilaian;
use App\Models\MataKuliah;
use App\Models\SubCpmk;
use App\Models\SubCpmkBobot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BobotPenilaianController extends Controller
{
    public function index(Request $request): View
    {
        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('nama')->get();
        $selectedMk  = null;
        $bobots      = collect();

        if ($request->filled('mk')) {
            $selectedMk = MataKuliah::where('kode', $request->mk)->first();
            if ($selectedMk) {
                // Load bobot_penilaian with cpmk, and for each cpmk load its CPL mappings
                $bobots = BobotPenilaian::where('mata_kuliah_id', $selectedMk->id)
                    ->with(['cpmk.cpl'])
                    ->get();
            }
        }

        return view('bobot-penilaian.index', compact('mataKuliahs', 'selectedMk', 'bobots'));
    }

    public function show(Request $request, $mkId)
    {
        $mk = MataKuliah::findOrFail($mkId);

        $cpmkIds = DB::table('mata_kuliah_cpmk')
            ->where('mata_kuliah_id', $mkId)
            ->pluck('cpmk_id');

        $subCpmks = SubCpmk::with(['cpmk', 'bobot'])
            ->whereIn('cpmk_id', $cpmkIds)
            ->orderBy('cpmk_id')
            ->orderBy('id')
            ->get();

        return view('bobot-penilaian.show', compact('mk', 'subCpmks'));
    }

    public function save(Request $request)
    {
        $mkId   = $request->input('mk_id');
        MataKuliah::findOrFail($mkId);
        $bobots = $request->input('bobot', []);

        $errors = [];
        foreach ($bobots as $subId => $val) {
            $total = (int)($val['tugas'] ?? 0) + (int)($val['uts'] ?? 0)
                   + (int)($val['uas'] ?? 0) + (int)($val['partisipatif'] ?? 0)
                   + (int)($val['proyek'] ?? 0);
            if ($total !== 100) {
                $sub = SubCpmk::find($subId);
                $errors[] = "SubCPMK {$sub->kode}: total bobot {$total} (harus 100)";
            }
        }

        if (!empty($errors)) {
            return back()->withErrors(['bobot' => implode('; ', $errors)])->withInput();
        }

        foreach ($bobots as $subId => $val) {
            SubCpmkBobot::updateOrCreate(
                ['sub_cpmk_id' => $subId],
                [
                    'bobot_tugas'        => (int)($val['tugas']        ?? 0),
                    'bobot_uts'          => (int)($val['uts']          ?? 0),
                    'bobot_uas'          => (int)($val['uas']          ?? 0),
                    'bobot_partisipatif' => (int)($val['partisipatif'] ?? 0),
                    'bobot_proyek'       => (int)($val['proyek']       ?? 0),
                ]
            );
        }

        return redirect()->route('bobot-penilaian.show', $mkId)
            ->with('success', 'Bobot berhasil disimpan.');
    }
}
