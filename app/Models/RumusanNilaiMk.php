<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RumusanNilaiMk extends Model
{
    protected $table = 'rumusan_nilai_mk';

    protected $fillable = [
        'mata_kuliah_id',
        'cpl_id',
        'cpmk_id',
        'skor_maks',
        'skor_min',
        'total_maks',
        'total_min',
        'keterangan',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function cpmk(): BelongsTo
    {
        return $this->belongsTo(Cpmk::class);
    }

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }
}
