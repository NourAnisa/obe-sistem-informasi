<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiDistribusiNilai extends Model
{
    protected $table = 'evaluasi_distribusi_nilai';
    protected $fillable = ['laporan_evaluasi_id', 'jumlah_lulus', 'jumlah_tidak_lulus', 'persen_lulus', 'rata_rata_final', 'jml_a', 'jml_b', 'jml_c', 'jml_d', 'jml_e'];

    protected $casts = [
        'jumlah_lulus'       => 'integer',
        'jumlah_tidak_lulus' => 'integer',
        'persen_lulus'       => 'float',
        'rata_rata_final'    => 'float',
        'jml_a'              => 'integer',
        'jml_b'              => 'integer',
        'jml_c'              => 'integer',
        'jml_d'              => 'integer',
        'jml_e'              => 'integer',
    ];
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanEvaluasi::class, 'laporan_evaluasi_id');
    }
}
