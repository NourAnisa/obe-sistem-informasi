<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpsPertemuan extends Model
{
    protected $table = 'rps_pertemuan';

    protected $fillable = [
        'mata_kuliah_id',
        'minggu',
        'sub_cpmk_ids',
        'cpmk_label',
        'indikator',
        'indikator_en',
        'teknik_penilaian',
        'teknik_penilaian_en',
        'kreteria',
        'kreteria_en',
        'metode_sinkron',
        'metode_sinkron_en',
        'metode_asinkron',
        'metode_asinkron_en',
        'tugas',
        'tugas_en',
        'materi',
        'materi_en',
        'bobot',
        'dosen',
    ];

    protected $casts = [
        'sub_cpmk_ids' => 'array',
        'bobot'        => 'decimal:2',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }
}
