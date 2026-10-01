<?php

namespace App\Http\Controllers;

use App\Models\Bap;
use App\Models\BapMahasiswaEvaluasi;
use App\Models\BapPertemuan;
use App\Models\Mahasiswa;
use App\Models\MahasiswaMk;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BapEvaluasiController extends Controller
{
    /** GET /bap-evaluasi — list MK yang diikuti mahasiswa */
    public function index()
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        $enrollments = MahasiswaMk::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('semester_aktif', 'desc')
            ->get();

        return view('bap-evaluasi.index', compact('mahasiswa', 'enrollments'));
    }

    /** GET /bap-evaluasi/{kode} — form evaluasi per pertemuan */
    public function show(string $kode)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();
        $mk        = MataKuliah::where('kode', $kode)->firstOrFail();

        $enrollment = MahasiswaMk::where('mahasiswa_id', $mahasiswa->id)
            ->where('mata_kuliah_id', $mk->id)
            ->firstOrFail();

        $bap = Bap::with(['bapPertemuans.mahasiswaEvaluasis' => function ($q) use ($mahasiswa) {
            $q->where('mahasiswa_id', $mahasiswa->id);
        }])
            ->where('mata_kuliah_id', $mk->id)
            ->where('semester_aktif', $enrollment->semester_aktif)
            ->latest()
            ->first();

        if (!$bap) {
            return back()->with('error', 'BAP untuk mata kuliah ini belum tersedia.');
        }

        return view('bap-evaluasi.show', compact('mk', 'bap', 'mahasiswa', 'enrollment'));
    }

    /** PUT /bap-evaluasi/{kode} — simpan evaluasi mahasiswa */
    public function update(Request $request, string $kode)
    {
        $user      = Auth::user();
        $mahasiswa = Mahasiswa::where('user_id', $user->id)->firstOrFail();
        $mk        = MataKuliah::where('kode', $kode)->firstOrFail();

        $enrollment = MahasiswaMk::where('mahasiswa_id', $mahasiswa->id)
            ->where('mata_kuliah_id', $mk->id)
            ->firstOrFail();

        $bap = Bap::where('mata_kuliah_id', $mk->id)
            ->where('semester_aktif', $enrollment->semester_aktif)
            ->latest()
            ->firstOrFail();

        foreach ($request->pertemuan ?? [] as $bapPertemuanId => $data) {
            BapMahasiswaEvaluasi::updateOrCreate(
                [
                    'bap_pertemuan_id' => $bapPertemuanId,
                    'mahasiswa_id'     => $mahasiswa->id,
                ],
                [
                    'kehadiran'          => $data['kehadiran'] ?? 'hadir',
                    'materi_dirasakan'   => $data['materi_dirasakan'] ?? '',
                    'kesesuaian_materi'  => (int) ($data['kesesuaian_materi'] ?? 3),
                    'kesesuaian_metode'  => (int) ($data['kesesuaian_metode'] ?? 3),
                    'catatan'            => $data['catatan'] ?? '',
                ]
            );
        }

        return redirect()->route('bap-evaluasi.show', $kode)
            ->with('success', 'Evaluasi berhasil disimpan!');
    }

    /** GET /bap/{kode}/evaluasi-compare — perbandingan dosen vs mahasiswa (dosen/kaprodi) */
    public function compare(string $kode)
    {
        $mk  = MataKuliah::where('kode', $kode)->firstOrFail();
        $bap = Bap::with([
            'bapPertemuans.mahasiswaEvaluasis.mahasiswa',
        ])
            ->where('mata_kuliah_id', $mk->id)
            ->latest()
            ->firstOrFail();

        // Enrollment for this BAP semester
        $totalMahasiswa = MahasiswaMk::where('mata_kuliah_id', $mk->id)
            ->where('semester_aktif', $bap->semester_aktif)
            ->count();

        return view('bap-evaluasi.compare', compact('mk', 'bap', 'totalMahasiswa'));
    }
}
