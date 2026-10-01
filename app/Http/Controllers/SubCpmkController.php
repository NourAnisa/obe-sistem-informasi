<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use App\Models\Cpl;
use App\Models\SubCpmk;
use App\Models\SubCpmkBobot;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubCpmkController extends Controller
{
    public function index(): View
    {
        $subCpmks = SubCpmk::with(['cpmk.cpl'])
            ->orderBy('kode')
            ->get();
        $cpmks = Cpmk::with('cpl')->orderBy('kode')->get();
        return view('admin.sub-cpmk.index', compact('subCpmks', 'cpmks'));
    }

    public function create(): View
    {
        $cpmks = Cpmk::with('cpl')->orderBy('kode')->get();
        return view('admin.sub-cpmk.create', compact('cpmks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode'      => 'required|string|max:30|unique:sub_cpmk,kode',
            'deskripsi' => 'required|string',
            'cpmk_id'   => 'required|exists:cpmk,id',
            'catatan'   => 'nullable|string',
        ]);

        $subCpmk = SubCpmk::create($request->only('kode', 'deskripsi', 'deskripsi_en', 'cpmk_id', 'catatan'));

        // Auto-copy bobot from parent CPMK's bobot_penilaian
        SubCpmkBobot::copyFromCpmk($subCpmk->id, $subCpmk->cpmk_id);

        return redirect()->route('admin.sub-cpmk.index')->with('success', 'Sub-CPMK berhasil ditambahkan.');
    }

    public function edit(SubCpmk $subCpmk): View
    {
        $cpmks = Cpmk::with('cpl')->orderBy('kode')->get();
        return view('admin.sub-cpmk.edit', compact('subCpmk', 'cpmks'));
    }

    public function update(Request $request, SubCpmk $subCpmk)
    {
        $request->validate([
            'kode'      => 'required|string|max:30|unique:sub_cpmk,kode,' . $subCpmk->id,
            'deskripsi' => 'required|string',
            'cpmk_id'   => 'required|exists:cpmk,id',
            'catatan'   => 'nullable|string',
        ]);

        $subCpmk->update($request->only('kode', 'deskripsi', 'deskripsi_en', 'cpmk_id', 'catatan'));

        return redirect()->route('admin.sub-cpmk.index')->with('success', 'Sub-CPMK berhasil diperbarui.');
    }

    public function destroy(SubCpmk $subCpmk)
    {
        $subCpmk->delete();
        return redirect()->route('admin.sub-cpmk.index')->with('success', 'Sub-CPMK berhasil dihapus.');
    }
}
