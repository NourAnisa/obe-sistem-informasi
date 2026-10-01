<?php

namespace App\Models;

use App\Models\Scopes\ProgramScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    protected $table = 'mata_kuliah';

    protected static function booted(): void
    {
        static::addGlobalScope(new ProgramScope);
    }

    protected $fillable = [
        'kode',
        'nama',
        'sks',
        'sks_teori',
        'sks_praktikum',
        'semester',
        'kategori',
        'pjmk',
        'deskripsi',
        'deskripsi_en',
        'is_mbkm',
        'is_wajib',
        'program_id',
    ];

    protected $casts = [
        'is_mbkm'  => 'boolean',
        'is_wajib' => 'boolean',
    ];

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(Cpl::class, 'mata_kuliah_cpl');
    }

    public function bahanKajians(): BelongsToMany
    {
        return $this->belongsToMany(BahanKajian::class, 'mata_kuliah_bahan_kajian');
    }

    public function cpmks(): BelongsToMany
    {
        return $this->belongsToMany(Cpmk::class, 'mata_kuliah_cpmk');
    }

    public function subCpmks(): BelongsToMany
    {
        return $this->belongsToMany(SubCpmk::class, 'mata_kuliah_sub_cpmk');
    }

    public function bobotPenilaians(): HasMany
    {
        return $this->hasMany(BobotPenilaian::class);
    }

    public function teknikPenilaians(): HasMany
    {
        return $this->hasMany(TeknikPenilaian::class);
    }

    public function rumusanNilais(): HasMany
    {
        return $this->hasMany(RumusanNilaiMk::class);
    }

    public function rpsReferensis(): HasMany
    {
        return $this->hasMany(RpsReferensi::class)->orderBy('jenis')->orderBy('urutan');
    }

    public function rpsDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RpsDetail::class);
    }

    public function rpsPertemuans(): HasMany
    {
        return $this->hasMany(RpsPertemuan::class)->orderBy('id');
    }

    public function courseSchedules(): HasMany
    {
        return $this->hasMany(CourseSchedule::class);
    }

    public function dosenMataKuliahs(): HasMany
    {
        return $this->hasMany(DosenMataKuliah::class);
    }

    public function publikasiDosen(): BelongsToMany
    {
        return $this->belongsToMany(PublikasiDosen::class, 'rps_publikasi', 'mata_kuliah_id', 'publikasi_dosen_id')
            ->withTimestamps();
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->kategori) {
            'MKF'  => 'bg-purple-100 text-purple-800',
            'MKPU' => 'bg-blue-100 text-blue-800',
            'MKWK' => 'bg-green-100 text-green-800',
            'MKPP' => 'bg-orange-100 text-orange-800',
            'MKP'  => 'bg-yellow-100 text-yellow-800',
            'MKKP' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-700',
        };
    }
}
