<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Program;
use Illuminate\Http\Request;

/**
 * Resolve program aktif untuk halaman publik.
 * Prioritas: ?program_id query param → program dengan kode_prodi → program pertama.
 */
trait ResolvesPublicProgram
{
    protected function resolveProgram(Request $request): ?Program
    {
        $programId = $request->query('program_id');

        if ($programId) {
            $program = Program::with('faculty')->find($programId);
            if ($program) return $program;
        }

        if ($request->user() && $request->user()->program_id) {
            $program = Program::with('faculty')->find($request->user()->program_id);
            if ($program) return $program;
        }

        return Program::with('faculty')
            ->whereNotNull('kode_prodi')
            ->orderBy('id')
            ->first()
            ?? Program::with('faculty')->orderBy('id')->first();
    }

    protected function allPrograms()
    {
        return Program::with('faculty')->orderBy('nama')->get();
    }
}
