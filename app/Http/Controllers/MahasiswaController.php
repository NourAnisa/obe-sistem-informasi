<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\MahasiswaMk;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller
{
    /** GET /mahasiswa — list all mahasiswa (admin/kaprodi) */
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['user', 'enrollments.mataKuliah', 'dosenPa', 'program'])
            ->orderBy('nama');

        if ($search = $request->input('q')) {
            $query->where(fn($q) => $q->where('nim', 'like', "%{$search}%")
                ->orWhere('nama', 'like', "%{$search}%"));
        }

        $mahasiswas  = $query->paginate(50)->withQueryString();
        $mataKuliahs = MataKuliah::orderBy('nama')->get();
        $dosenList   = User::where('role', 'dosen')->orderBy('name')->get();
        $programs    = \App\Models\Program::with('faculty')->orderBy('nama')->get();

        return view('mahasiswa.index', compact('mahasiswas', 'mataKuliahs', 'dosenList', 'programs'));
    }

    /** POST /mahasiswa — create mahasiswa + user account */
    public function store(Request $request)
    {
        $request->validate([
            'nim'        => 'required|unique:mahasiswas,nim',
            'nama'       => 'required',
            'angkatan'   => 'nullable|digits:4',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        // Tentukan program_id: ambil dari form (admin), atau dari user yang login
        $programId = $request->filled('program_id')
            ? $request->program_id
            : auth()->user()->program_id;

        $user = User::create([
            'name'       => $request->nama,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => 'mahasiswa',
            'program_id' => $programId,
        ]);

        Mahasiswa::create([
            'user_id'    => $user->id,
            'nim'        => $request->nim,
            'nama'       => $request->nama,
            'angkatan'   => $request->angkatan,
            'program_id' => $programId,
        ]);

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan!');
    }

    /** DELETE /mahasiswa/{id} */
    public function destroy(int $id)
    {
        $mhs = Mahasiswa::findOrFail($id);
        if ($mhs->user) {
            $mhs->user->delete();
        }
        $mhs->delete();
        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa dihapus.');
    }

    /** POST /mahasiswa/{id}/enroll — daftarkan ke MK */
    public function enroll(Request $request, int $id)
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'semester_aktif' => 'required|string',
        ]);

        MahasiswaMk::updateOrCreate(
            [
                'mahasiswa_id'   => $id,
                'mata_kuliah_id' => $request->mata_kuliah_id,
                'semester_aktif' => $request->semester_aktif,
            ],
            ['is_pjmk' => $request->boolean('is_pjmk')]
        );

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil didaftarkan ke MK!');
    }

    /** DELETE /mahasiswa/enrollment/{id} */
    public function unenroll(int $id)
    {
        MahasiswaMk::findOrFail($id)->delete();
        return redirect()->route('mahasiswa.index')->with('success', 'Pendaftaran dihapus.');
    }

    /** POST /mahasiswa/enrollment/{id}/toggle-pjmk */
    public function togglePjmk(int $id)
    {
        $enrollment = MahasiswaMk::findOrFail($id);
        if (!$enrollment->is_pjmk) {
            MahasiswaMk::where('mata_kuliah_id', $enrollment->mata_kuliah_id)
                ->where('semester_aktif', $enrollment->semester_aktif)
                ->update(['is_pjmk' => false]);
        }
        $enrollment->update(['is_pjmk' => !$enrollment->is_pjmk]);
        return redirect()->route('mahasiswa.index')->with('success', 'Status PJMK diperbarui.');
    }

    /** POST /mahasiswa/{id}/assign-pa */
    public function assignPa(Request $request, int $id)
    {
        $request->validate([
            'dosen_pa_id' => 'nullable|exists:users,id',
        ]);
        $mhs = Mahasiswa::findOrFail($id);
        $mhs->update(['dosen_pa_id' => $request->dosen_pa_id ?: null]);
        return redirect()->route('mahasiswa.index')->with('success', 'Dosen PA berhasil diperbarui.');
    }

    public function updateIpkIps(Request $request, int $id)
    {
        $request->validate([
            'ipk' => 'required|numeric|min:0|max:4',
            'ips' => 'required|numeric|min:0|max:4',
        ]);
        Mahasiswa::findOrFail($id)->update([
            'ipk' => $request->ipk,
            'ips' => $request->ips,
        ]);
        return redirect()->route('mahasiswa.index')->with('success', 'IPK & IPS berhasil diperbarui.');
    }
}
