<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SubCpmkBobot extends Model
{
    protected $table = 'sub_cpmk_bobot';

    protected $fillable = [
        'sub_cpmk_id',
        'bobot_tugas',
        'bobot_uts',
        'bobot_uas',
        'bobot_partisipatif',
        'bobot_proyek',
    ];

    protected $casts = [
        'bobot_tugas'        => 'float',
        'bobot_uts'          => 'float',
        'bobot_uas'          => 'float',
        'bobot_partisipatif' => 'float',
        'bobot_proyek'       => 'float',
    ];

    public function subCpmk(): BelongsTo
    {
        return $this->belongsTo(SubCpmk::class, 'sub_cpmk_id');
    }

    /** Total bobot — should equal 100 for a valid setup. */
    public function getTotalAttribute(): int
    {
        return $this->bobot_tugas + $this->bobot_uts + $this->bobot_uas
            + $this->bobot_partisipatif + $this->bobot_proyek;
    }

    public function getIsValidAttribute(): bool
    {
        return $this->total === 100;
    }

    /**
     * Copy bobot from parent CPMK's bobot_penilaian row for the given mata_kuliah.
     * Falls back to equal 20% each if no bobot row found.
     */
    public static function copyFromCpmk(int $subCpmkId, int $cpmkId, ?int $mkId = null): self
    {
        $query = DB::table('bobot_penilaian')->where('cpmk_id', $cpmkId);
        if ($mkId) {
            $query->where('mata_kuliah_id', $mkId);
        }
        $bp = $query->first();

        return self::updateOrCreate(
            ['sub_cpmk_id' => $subCpmkId],
            [
                'bobot_tugas'        => $bp->bobot_tugas        ?? 20,
                'bobot_uts'          => $bp->bobot_uts          ?? 20,
                'bobot_uas'          => $bp->bobot_uas          ?? 20,
                'bobot_partisipatif' => $bp->bobot_partisipatif ?? 20,
                'bobot_proyek'       => $bp->bobot_proyek       ?? 20,
            ]
        );
    }
}
