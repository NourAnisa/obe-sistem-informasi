<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiCohort extends Model
{
    protected $table = 'evaluasi_cohort';

    protected $fillable = [
        'cpl_id',
        'angkatan',
        'tahun_akademik',
        'semester_ke',
        'total_mahasiswa',
        'jumlah_tercapai',
        'rata_nilai_cpl',
        'pct_lulus',
        'target_capaian',
        'status_target',
        'catatan',
    ];

    protected $casts = [
        'rata_nilai_cpl' => 'float',
        'pct_lulus'      => 'float',
        'target_capaian' => 'float',
    ];

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }
}
