<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BobotPenilaian extends Model
{
    protected $table = 'bobot_penilaian';

    protected $fillable = [
        'mata_kuliah_id',
        'cpmk_id',
        'bobot',
        'bobot_tugas',
        'bobot_uts',
        'bobot_uas',
        'bobot_partisipatif',
        'bobot_proyek',
        'skor_maks',
        'skor_min',
    ];

    protected $casts = [
        'bobot'              => 'float',
        'bobot_tugas'        => 'float',
        'bobot_uts'          => 'float',
        'bobot_uas'          => 'float',
        'bobot_partisipatif' => 'float',
        'bobot_proyek'       => 'float',
        'skor_maks'          => 'float',
        'skor_min'           => 'float',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function cpmk(): BelongsTo
    {
        return $this->belongsTo(Cpmk::class);
    }

    public function getTotalAttribute(): int
    {
        return $this->bobot_tugas + $this->bobot_uts + $this->bobot_uas
            + $this->bobot_partisipatif + $this->bobot_proyek;
    }

    public function getIsValidAttribute(): bool
    {
        return $this->total === 100;
    }
}
