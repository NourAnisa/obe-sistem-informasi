<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CpmkController extends Controller
{
    public function index(): View
    {
        $cpmks = Cpmk::with(['cpl.bahanKajians', 'subCpmks', 'mataKuliahs'])
            ->whereHas('cpl')
            ->orderBy('kode')
            ->get();
        $cpls = Cpl::orderBy('kode')->get();
        return view('admin.cpmk.index', compact('cpmks', 'cpls'));
    }

    public function create(): View
    {
        $cpls = Cpl::orderBy('kode')->get();
        return view('admin.cpmk.create', compact('cpls'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpl_id'    => 'required|exists:cpl,id',
            'kode'      => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('cpmk', 'kode')
                    ->where('cpl_id', $request->input('cpl_id'))
            ],
            'deskripsi' => 'required|string',
        ]);

        Cpmk::create($request->only('kode', 'deskripsi', 'deskripsi_en', 'cpl_id'));

        return redirect()->route('admin.cpmk.index')->with('success', 'CPMK berhasil ditambahkan.');
    }

    public function edit(Cpmk $cpmk): View
    {
        $cpmk->load('cpl.bahanKajians');
        $cpls = Cpl::orderBy('kode')->get();
        return view('admin.cpmk.edit', compact('cpmk', 'cpls'));
    }

    public function update(Request $request, Cpmk $cpmk)
    {
        $request->validate([
            'cpl_id'    => 'required|exists:cpl,id',
            'kode'      => [
                'required',
                'string',
                'max:20',
                \Illuminate\Validation\Rule::unique('cpmk', 'kode')
                    ->where('cpl_id', $request->input('cpl_id'))
                    ->ignore($cpmk->id)
            ],
            'deskripsi' => 'required|string',
        ]);

        $cpmk->update($request->only('kode', 'deskripsi', 'deskripsi_en', 'cpl_id'));

        return redirect()->route('admin.cpmk.index')->with('success', 'CPMK berhasil diperbarui.');
    }

    public function destroy(Cpmk $cpmk)
    {
        $cpmk->delete();
        return redirect()->route('admin.cpmk.index')->with('success', 'CPMK berhasil dihapus.');
    }
}
