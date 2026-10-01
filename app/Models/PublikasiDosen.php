<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PublikasiDosen extends Model
{
    protected $table = 'publikasi_dosen';

    protected $fillable = [
        'dosen_id',
        'judul',
        'tahun',
        'authors',
        'sumber',
        'link',
        'snippet',
        'tipe',
        'jenis',
        'serpapi_id',
        'dokumen_bukti',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function mataKuliahList(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'rps_publikasi', 'publikasi_dosen_id', 'mata_kuliah_id')
            ->withTimestamps();
    }

    /**
     * Klasifikasi otomatis berdasarkan judul.
     */
    public static function klasifikasi(string $judul): string
    {
        $keywords = ['pengabdian', 'pemberdayaan', 'pelatihan', 'workshop', 'pkm', 'community'];
        $lower = strtolower($judul);
        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) return 'pengabdian';
        }
        return 'penelitian';
    }
}
