<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsMahasiswa extends Model
{
    protected $table = 'krs_mahasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'kelas',
        'tahun_akademik',
        'semester',
        'status',
    ];

    // ── Relations ────────────────────────────────────────

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    // ── Static Helpers ───────────────────────────────────

    /**
     * Count active students enrolled in a course/class/TA combination.
     */
    public static function countAktif(int $mkId, string $ta, ?string $kelas = null): int
    {
        $q = static::where('mata_kuliah_id', $mkId)
            ->where('tahun_akademik', $ta)
            ->where('status', 'aktif');
        if ($kelas) {
            $q->where('kelas', $kelas);
        }
        return $q->count();
    }

    /**
     * Get active students for a course/class/TA (with mahasiswa relation loaded).
     */
    public static function getAktif(int $mkId, string $ta, ?string $kelas = null)
    {
        $q = static::with('mahasiswa')
            ->where('mata_kuliah_id', $mkId)
            ->where('tahun_akademik', $ta)
            ->where('status', 'aktif');
        if ($kelas) {
            $q->where('kelas', $kelas);
        }
        return $q->orderBy('kelas')
            ->get()
            ->sortBy(fn($k) => $k->mahasiswa?->nim ?? '');
    }
}
