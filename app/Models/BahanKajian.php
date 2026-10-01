<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BahanKajian extends Model
{
    protected $table = 'bahan_kajian';

    protected $fillable = ['kode', 'nama', 'referensi', 'program_id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\ProgramScope);
    }

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(Cpl::class, 'cpl_bahan_kajian');
    }

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_bahan_kajian');
    }
}
