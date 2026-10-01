<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiMahasiswa extends Model
{
    protected $table = 'nilai_mahasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'semester_aktif',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_partisipatif',
        'nilai_proyek',
        'nilai_akhir',
        'grade',
        'lulus',
    ];

    protected $casts = [
        'lulus'             => 'boolean',
        'nilai_tugas'       => 'float',
        'nilai_uts'         => 'float',
        'nilai_uas'         => 'float',
        'nilai_partisipatif' => 'float',
        'nilai_proyek'      => 'float',
        'nilai_akhir'       => 'float',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /** Hitung nilai_akhir dari bobot komponen dan nilai masing-masing. */
    public static function hitungNilaiAkhir(array $nilai, array $bobot): float
    {
        $total = max(
            1,
            $bobot['tugas'] + $bobot['uts'] + $bobot['uas'] +
                $bobot['partisipatif'] + $bobot['proyek']
        );

        return round(
            ($nilai['tugas']        * $bobot['tugas']        +
                $nilai['uts']          * $bobot['uts']          +
                $nilai['uas']          * $bobot['uas']          +
                $nilai['partisipatif'] * $bobot['partisipatif'] +
                $nilai['proyek']       * $bobot['proyek'])
                / $total,
            2
        );
    }

    /** Konversi nilai_akhir ke grade huruf. */
    public static function toGrade(float $nilai): string
    {
        return match (true) {
            $nilai >= 80 => 'A',
            $nilai >= 70 => 'B',
            $nilai >= 56 => 'C',
            $nilai >= 40 => 'D',
            default      => 'E',
        };
    }
}
