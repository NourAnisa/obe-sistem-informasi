<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpmkRubric extends Model
{
    protected $table = 'cpmk_rubrics';

    protected $fillable = [
        'cpmk_id',
        'level',
        'min_score',
        'max_score',
        'deskripsi',
    ];

    protected $casts = [
        'min_score' => 'integer',
        'max_score' => 'integer',
    ];

    public function cpmk(): BelongsTo
    {
        return $this->belongsTo(Cpmk::class);
    }

    /** Default rubric descriptions for each level (used as fallback). */
    public static array $defaults = [
        'novice' => [
            'min_score' => 0,
            'max_score' => 59,
            'deskripsi' => 'Mahasiswa belum mampu mencapai indikator CPMK secara mandiri. Memerlukan bimbingan intensif.',
        ],
        'developing' => [
            'min_score' => 60,
            'max_score' => 79,
            'deskripsi' => 'Mahasiswa mulai mencapai indikator namun masih perlu bimbingan dan pendampingan lebih lanjut.',
        ],
        'proficient' => [
            'min_score' => 80,
            'max_score' => 100,
            'deskripsi' => 'Mahasiswa mampu mencapai indikator secara mandiri dan konsisten sesuai standar kompetensi.',
        ],
    ];

    /** Determine rubric level from a numeric nilai. */
    public static function levelFromNilai(float|null $nilai): string
    {
        if ($nilai === null) return 'novice';
        if ($nilai < 60)    return 'novice';
        if ($nilai < 80)    return 'developing';
        return 'proficient';
    }

    /** Get badge CSS info for a level. */
    public static function badge(string $level): array
    {
        return match ($level) {
            'proficient' => ['label' => 'Proficient', 'bg' => '#dcfce7', 'color' => '#065f46', 'dot' => '#16a34a'],
            'developing' => ['label' => 'Developing', 'bg' => '#fef9c3', 'color' => '#713f12', 'dot' => '#d97706'],
            default      => ['label' => 'Novice',     'bg' => '#fee2e2', 'color' => '#991b1b', 'dot' => '#dc2626'],
        };
    }
}
