<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * ProgramObserver — Otomatis mengisi program_id saat create/update
 * untuk model yang memiliki kolom program_id (MataKuliah, Cpl, Mahasiswa).
 *
 * Jika admin yang membuat (tanpa program_id), tidak diisi otomatis
 * sehingga admin harus memilih prodi secara eksplisit dari form.
 */
class ProgramObserver
{
    public function creating(Model $model): void
    {
        if (!Auth::check()) return;

        $user = Auth::user();

        // Jika model belum punya program_id dan user punya program_id → isi otomatis
        if (empty($model->program_id) && $user->program_id) {
            $model->program_id = $user->program_id;
        }
    }
}
