<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenMataKuliah extends Model
{
    protected $table = 'dosen_mata_kuliah';

    protected $fillable = [
        'dosen_id',
        'mata_kuliah_id',
        'kelas',
        'semester',
        'tahun_akademik',
        'peran',
        'jumlah_sks',
        'status',
    ];

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /** Returns MK IDs for a given dosen + TA (used by restriction helpers). */
    public static function mkIdsForDosen(int $dosenId, string $ta): \Illuminate\Support\Collection
    {
        return static::where('dosen_id', $dosenId)
            ->where('tahun_akademik', $ta)
            ->pluck('mata_kuliah_id');
    }
}
