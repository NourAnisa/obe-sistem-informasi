<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'faculty_id', 'nama', 'jenjang', 'kode_prodi',
        'logo_prodi_path', 'kaprodi', 'nik_kaprodi',
        'akreditasi', 'sks_total', 'total_semester', 'visi'
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'program_id');
    }

    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'program_id');
    }

    public function mataKuliahs(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'program_id');
    }

    public function cpls(): HasMany
    {
        return $this->hasMany(Cpl::class, 'program_id');
    }
}
