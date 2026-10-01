<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpsDetail extends Model
{
    protected $table = 'rps_detail';

    protected $fillable = [
        'mata_kuliah_id',
        'tautan_kelas_daring',
        'ketentuan_tambahan',
        'jadwal_kuliah',
        'dosen_pengampu',
        'catatan_blueprint',
        'blueprint_overrides',
    ];

    protected $casts = [
        'ketentuan_tambahan'  => 'array',
        'jadwal_kuliah'       => 'array',
        'dosen_pengampu'      => 'array',
        'blueprint_overrides' => 'array',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }
}
