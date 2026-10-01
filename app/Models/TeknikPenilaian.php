<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeknikPenilaian extends Model
{
    protected $table = 'teknik_penilaian';

    protected $fillable = [
        'mata_kuliah_id',
        'cpmk_id',
        'is_mbkm',
        'has_quiz',
        'has_tugas',
        'has_uts',
        'has_uas',
        'has_partisipatif',
        'has_proyek',
    ];

    protected $casts = [
        'is_mbkm'          => 'boolean',
        'has_quiz'         => 'boolean',
        'has_tugas'        => 'boolean',
        'has_uts'          => 'boolean',
        'has_uas'          => 'boolean',
        'has_partisipatif' => 'boolean',
        'has_proyek'       => 'boolean',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function cpmk(): BelongsTo
    {
        return $this->belongsTo(Cpmk::class);
    }
}
