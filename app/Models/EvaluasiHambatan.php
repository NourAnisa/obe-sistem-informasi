<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiHambatan extends Model
{
    protected $table = 'evaluasi_hambatan';
    protected $fillable = ['laporan_evaluasi_id', 'no_urut', 'jenis_hambatan', 'deskripsi', 'solusi_usulan'];
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanEvaluasi::class, 'laporan_evaluasi_id');
    }
}
