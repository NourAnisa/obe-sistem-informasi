<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiTindakLanjut extends Model
{
    protected $table = 'evaluasi_tindak_lanjut';
    protected $fillable = ['laporan_evaluasi_id', 'no_urut', 'aspek', 'permasalahan', 'rekomendasi', 'penanggung_jawab', 'target_semester'];
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanEvaluasi::class, 'laporan_evaluasi_id');
    }
}
