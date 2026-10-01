<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpmkAchievement extends Model
{
    protected $table = 'cpmk_achievement';

    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'cpmk_id',
        'semester_aktif',
        'angkatan',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_partisipatif',
        'nilai_proyek',
        'nilai_cpmk',
        'threshold',
        'achieved',
        'rubric_level',
    ];

    protected $casts = [
        'achieved'          => 'boolean',
        'nilai_cpmk'        => 'float',
        'nilai_tugas'       => 'float',
        'nilai_uts'         => 'float',
        'nilai_uas'         => 'float',
        'nilai_partisipatif' => 'float',
        'nilai_proyek'      => 'float',
        'threshold'         => 'float',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }
    public function cpmk(): BelongsTo
    {
        return $this->belongsTo(Cpmk::class);
    }
}
