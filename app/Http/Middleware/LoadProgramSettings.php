<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;
use Symfony\Component\HttpFoundation\Response;

class LoadProgramSettings
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $programId = $user->program_id;

            if ($programId) {
                // Fetch program-specific settings
                $program = Program::find($programId);
                if ($program) {
                    config([
                        'obe.fakultas'        => $program->faculty ? $program->faculty->nama : config('obe.fakultas'),
                        'obe.prodi'           => $program->nama,
                        'obe.jenjang'         => $program->jenjang,
                        'obe.logo_prodi_path' => $program->logo_prodi_path,
                        'obe.kaprodi'         => $program->kaprodi,
                        'obe.nik_kaprodi'     => $program->nik_kaprodi,
                        'obe.akreditasi'      => $program->akreditasi,
                        'obe.sks_total'       => $program->sks_total,
                        'obe.total_semester'  => $program->total_semester,
                        'obe.visi'            => $program->visi,
                    ]);
                }
            }
        }

        return $next($request);
    }
}
