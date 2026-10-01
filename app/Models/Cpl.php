<?php

namespace App\Models;

use App\Models\Scopes\ProgramScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cpl extends Model
{
    protected $table = 'cpl';

    protected static function booted(): void
    {
        static::addGlobalScope(new ProgramScope);
    }

    protected $fillable = ['kode', 'deskripsi', 'deskripsi_en', 'kategori', 'total_skor_maks', 'program_id'];

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function getTotalSkorMinAttribute(): int
    {
        return (int) round($this->total_skor_maks * 0.6);
    }

    public function profilLulusans(): BelongsToMany
    {
        return $this->belongsToMany(ProfilLulusan::class, 'cpl_profil_lulusan');
    }

    public function bahanKajians(): BelongsToMany
    {
        return $this->belongsToMany(BahanKajian::class, 'cpl_bahan_kajian');
    }

    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'mata_kuliah_cpl');
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(Cpmk::class);
    }

    public function cplSndiktis(): BelongsToMany
    {
        return $this->belongsToMany(CplSndikti::class, 'cpl_sndikti_cpl');
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->kategori) {
            'Sikap'    => 'bg-red-100 text-red-700',
            'KU'       => 'bg-green-100 text-green-700',
            'KK'       => 'bg-blue-100 text-blue-700',
            'Sikap_KU' => 'bg-purple-100 text-purple-700',
            default    => 'bg-gray-100 text-gray-700',
        };
    }
}
