<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $roleOrder = \App\Models\User::roleOrder();
        $users = User::with('program.faculty')
            ->get()
            ->sortBy([
                fn($a, $b) => ($roleOrder[$a->role] ?? 99) <=> ($roleOrder[$b->role] ?? 99),
                fn($a, $b) => $a->name <=> $b->name,
            ])
            ->values();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $faculties = Faculty::with('programs')->orderBy('nama')->get();
        return view('users.create', compact('faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6',
            'role'       => 'required|in:admin,dekan,wakildekan,kaprodi,dosen,akademik,kemahasiswaan,viewer',
            'jabatan'    => 'nullable|string|max:150',
            'nik'        => 'nullable|string|max:20',
            'nuptk'      => 'nullable|string|max:20',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role'       => $request->role,
            'jabatan'    => $request->jabatan,
            'nik'        => $request->nik,
            'nuptk'      => $request->nuptk,
            'program_id' => $request->program_id ?: null,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $faculties = Faculty::with('programs')->orderBy('nama')->get();
        return view('users.edit', compact('user', 'faculties'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'       => 'required|in:admin,dekan,wakildekan,kaprodi,dosen,akademik,kemahasiswaan,viewer',
            'jabatan'    => 'nullable|string|max:150',
            'nik'        => 'nullable|string|max:20',
            'nuptk'      => 'nullable|string|max:20',
            'password'   => 'nullable|string|min:6',
            'program_id' => 'nullable|exists:programs,id',
        ]);

        $data = [
            'name'       => $request->name,
            'email'      => $request->email,
            'role'       => $request->role,
            'jabatan'    => $request->jabatan,
            'nik'        => $request->nik,
            'nuptk'      => $request->nuptk,
            'program_id' => $request->program_id ?: null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
