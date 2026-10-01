<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cpmk extends Model
{
    protected $table = 'cpmk';

    protected $fillable = ['kode', 'deskripsi', 'deskripsi_en', 'cpl_id'];

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_cpmk');
    }

    public function subCpmks(): HasMany
    {
        return $this->hasMany(SubCpmk::class);
    }

    public function bobotPenilaians(): HasMany
    {
        return $this->hasMany(BobotPenilaian::class);
    }
}
