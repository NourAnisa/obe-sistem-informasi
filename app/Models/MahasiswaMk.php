<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaMk extends Model
{
    protected $table = 'mahasiswa_mk';

    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'semester_aktif',
        'is_pjmk',
        'status',
        'catatan_pa',
    ];

    protected $casts = [
        'is_pjmk' => 'boolean',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
