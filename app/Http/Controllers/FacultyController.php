<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Program;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    // ── FAKULTAS ──────────────────────────────────────────────

    public function index()
    {
        $faculties = Faculty::withCount('programs')->orderBy('nama')->get();
        return view('admin.faculty.index', compact('faculties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:faculties,nama',
            'kode' => 'nullable|string|max:10',
        ]);

        Faculty::create($data);
        return back()->with('success', 'Fakultas berhasil ditambahkan.');
    }

    public function update(Request $request, Faculty $faculty)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:faculties,nama,' . $faculty->id,
            'kode' => 'nullable|string|max:10',
        ]);

        $faculty->update($data);
        return back()->with('success', 'Fakultas berhasil diperbarui.');
    }

    public function destroy(Faculty $faculty)
    {
        if ($faculty->programs()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus — masih ada program studi terdaftar.');
        }
        $faculty->delete();
        return back()->with('success', 'Fakultas berhasil dihapus.');
    }

    // ── PROGRAM STUDI ─────────────────────────────────────────

    public function programs()
    {
        $faculties = Faculty::orderBy('nama')->get();
        $programs  = Program::with('faculty')->orderBy('faculty_id')->orderBy('nama')->get();
        return view('admin.faculty.programs', compact('faculties', 'programs'));
    }

    public function storeProgram(Request $request)
    {
        $data = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'nama'       => 'required|string|max:100|unique:programs,nama',
            'jenjang'    => 'required|in:D3,D4,S1,S2,S3,Profesi',
            'kode_prodi' => 'nullable|string|max:20',
        ]);

        Program::create($data);
        return back()->with('success', 'Program Studi berhasil ditambahkan.');
    }

    public function updateProgram(Request $request, Program $program)
    {
        $data = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'nama'       => 'required|string|max:100|unique:programs,nama,' . $program->id,
            'jenjang'    => 'required|in:D3,D4,S1,S2,S3,Profesi',
            'kode_prodi' => 'nullable|string|max:20',
        ]);

        $program->update($data);
        return back()->with('success', 'Program Studi berhasil diperbarui.');
    }

    public function destroyProgram(Program $program)
    {
        $program->delete();
        return back()->with('success', 'Program Studi berhasil dihapus.');
    }

    /** API: Return programs by faculty (for dynamic dropdown in user forms) */
    public function programsByFaculty(Faculty $faculty)
    {
        return response()->json($faculty->programs()->orderBy('jenjang')->orderBy('nama')->get(['id', 'nama', 'jenjang']));
    }
}
