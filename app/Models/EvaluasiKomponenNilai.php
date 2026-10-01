<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiKomponenNilai extends Model
{
    protected $table = 'evaluasi_komponen_nilai';
    protected $fillable = ['laporan_evaluasi_id', 'komponen', 'bobot_persen', 'rata_rata', 'nilai_min', 'nilai_max', 'std_deviasi'];

    protected $casts = [
        'bobot_persen' => 'float',
        'rata_rata'    => 'float',
        'nilai_min'    => 'float',
        'nilai_max'    => 'float',
        'std_deviasi'  => 'float',
    ];
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanEvaluasi::class, 'laporan_evaluasi_id');
    }
}
