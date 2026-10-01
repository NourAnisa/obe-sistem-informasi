<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CplTarget extends Model
{
    protected $table = 'cpl_target';

    protected $fillable = ['cpl_id', 'target_pct', 'threshold', 'keterangan'];

    protected $casts = [
        'target_pct' => 'float',
        'threshold'  => 'float',
    ];

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }
}
