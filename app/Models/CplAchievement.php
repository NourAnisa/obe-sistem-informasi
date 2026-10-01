<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CplAchievement extends Model
{
    protected $table = 'cpl_achievement';

    protected $fillable = [
        'mahasiswa_id',
        'cpl_id',
        'semester_aktif',
        'tahun_akademik',
        'angkatan',
        'nilai_cpl',
        'achieved',
        'threshold',
        'jumlah_cpmk',
        'jumlah_achieved',
    ];

    protected $casts = [
        'achieved'  => 'boolean',
        'nilai_cpl' => 'float',
        'threshold' => 'float',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }
}
