<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BapMahasiswaEvaluasi extends Model
{
    protected $table = 'bap_mahasiswa_evaluasi';

    protected $fillable = [
        'bap_pertemuan_id',
        'mahasiswa_id',
        'kehadiran',
        'materi_dirasakan',
        'kesesuaian_materi',
        'kesesuaian_metode',
        'catatan',
    ];

    protected $casts = [
        'kesesuaian_materi' => 'integer',
        'kesesuaian_metode' => 'integer',
    ];

    public function bapPertemuan(): BelongsTo
    {
        return $this->belongsTo(BapPertemuan::class);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
