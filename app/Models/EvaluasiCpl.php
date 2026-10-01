<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiCpl extends Model
{
    protected $table = 'evaluasi_cpl';
    protected $fillable = ['laporan_evaluasi_id', 'cpl_id', 'kode_cpl', 'nilai_cpl', 'target_cpl', 'tercapai', 'gap', 'keterangan'];
    protected $casts = [
        'tercapai'   => 'boolean',
        'nilai_cpl'  => 'float',
        'target_cpl' => 'float',
        'gap'        => 'float',
    ];
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanEvaluasi::class, 'laporan_evaluasi_id');
    }
}
