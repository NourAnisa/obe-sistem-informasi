<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BapPertemuan extends Model
{
    protected $table = 'bap_pertemuan';

    protected $fillable = [
        'bap_id',
        'minggu',
        'tanggal',
        'materi',
        'metode_pembelajaran',
        'jumlah_hadir',
        'jumlah_ijin',
        'jumlah_sakit',
        'jumlah_tk',
        'keterangan',
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'jumlah_hadir'  => 'integer',
        'jumlah_ijin'   => 'integer',
        'jumlah_sakit'  => 'integer',
        'jumlah_tk'     => 'integer',
    ];

    public function bap(): BelongsTo
    {
        return $this->belongsTo(Bap::class);
    }

    public function mahasiswaEvaluasis(): HasMany
    {
        return $this->hasMany(BapMahasiswaEvaluasi::class);
    }
}
