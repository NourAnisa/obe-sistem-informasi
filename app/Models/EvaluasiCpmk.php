<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiCpmk extends Model
{
    protected $table = 'evaluasi_cpmk';
    protected $fillable = ['laporan_evaluasi_id', 'cpmk_id', 'kode_cpmk', 'deskripsi_cpmk', 'rata_rata_nilai', 'persen_lulus', 'target_capaian', 'tercapai', 'keterangan'];
    protected $casts = [
        'tercapai'       => 'boolean',
        'rata_rata_nilai' => 'float',
        'persen_lulus'   => 'float',
        'target_capaian' => 'float',
    ];
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanEvaluasi::class, 'laporan_evaluasi_id');
    }
}
