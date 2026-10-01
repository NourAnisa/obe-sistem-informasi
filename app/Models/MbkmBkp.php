<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MbkmBkp extends Model
{
    protected $table = 'mbkm_bkp';

    protected $fillable = [
        'no',
        'bentuk_kegiatan',
        'sks_reguler',
        'sks_mbkm_maks',
        'deskripsi',
        'konversi_mk',
    ];
}
