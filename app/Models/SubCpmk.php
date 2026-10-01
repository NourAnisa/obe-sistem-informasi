<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubCpmk extends Model
{
    protected $table = 'sub_cpmk';

    protected $fillable = ['kode', 'deskripsi', 'deskripsi_en', 'cpmk_id', 'catatan'];

    public function cpmk(): BelongsTo
    {
        return $this->belongsTo(Cpmk::class);
    }

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_sub_cpmk');
    }

    public function bobot(): HasOne
    {
        return $this->hasOne(SubCpmkBobot::class, 'sub_cpmk_id');
    }

    public function nilaiSubCpmks(): HasMany
    {
        return $this->hasMany(NilaiSubCpmk::class);
    }
}
