<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CqiAction extends Model
{
    protected $table = 'cqi_actions';

    protected $fillable = [
        'cpl_id',
        'angkatan',
        'tahun_akademik',
        'nilai_cpl',
        'threshold',
        'masalah',
        'rencana_perbaikan',
        'pic',
        'target_semester',
        'status',
    ];

    protected $casts = [
        'nilai_cpl' => 'float',
        'threshold' => 'float',
        'angkatan'  => 'integer',
    ];

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'done'        => ['label' => 'Selesai',     'cls' => 'bg-green-100 text-green-700 border border-green-300'],
            'in_progress' => ['label' => 'Dalam Proses', 'cls' => 'bg-blue-100 text-blue-700 border border-blue-300'],
            default       => ['label' => 'Open',        'cls' => 'bg-red-100 text-red-700 border border-red-300'],
        };
    }
}
