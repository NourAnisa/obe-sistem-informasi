<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSubCpmk extends Model
{
    protected $table = 'nilai_sub_cpmk';

    protected $fillable = [
        'mahasiswa_id',
        'sub_cpmk_id',
        'tugas',
        'uts',
        'uas',
        'partisipatif',
        'proyek',
        'nilai_subcpmk',
    ];

    protected $casts = [
        'tugas'         => 'float',
        'uts'           => 'float',
        'uas'           => 'float',
        'partisipatif'  => 'float',
        'proyek'        => 'float',
        'nilai_subcpmk' => 'float',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function subCpmk(): BelongsTo
    {
        return $this->belongsTo(SubCpmk::class, 'sub_cpmk_id');
    }
}
