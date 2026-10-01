<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * ProgramScope — Otomatis memfilter query berdasarkan program_id user yang login.
 *
 * Aturan:
 *  - role 'admin'           → bypass (lihat semua prodi)
 *  - user tanpa program_id  → bypass (fallback aman)
 *  - semua role lain        → filter WHERE program_id = user->program_id
 *
 * Cara bypass manual di controller:
 *   MataKuliah::withoutGlobalScope(ProgramScope::class)->get();
 */
class ProgramScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Tidak ada session (artisan command, queue, dll.) → skip
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Admin universitas melihat semua data lintas prodi
        if ($user->role === 'admin') {
            return;
        }

        // User belum ditetapkan ke prodi → skip (aman, tidak error)
        if (!$user->program_id) {
            return;
        }

        $table = $model->getTable();
        $builder->where("{$table}.program_id", $user->program_id);
    }
}
