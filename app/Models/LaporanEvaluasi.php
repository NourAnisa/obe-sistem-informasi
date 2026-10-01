<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaporanEvaluasi extends Model
{
    protected $table = 'laporan_evaluasi';
    protected $fillable = [
        'mata_kuliah_id',
        'semester',
        'tahun_akademik',
        'kelas',
        'jumlah_mahasiswa',
        'dosen_pjmk',
        'status',
        'catatan_umum',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }
    public function komponenNilai(): HasMany
    {
        return $this->hasMany(EvaluasiKomponenNilai::class, 'laporan_evaluasi_id');
    }
    public function cpmks(): HasMany
    {
        return $this->hasMany(EvaluasiCpmk::class, 'laporan_evaluasi_id');
    }
    public function cpls(): HasMany
    {
        return $this->hasMany(EvaluasiCpl::class, 'laporan_evaluasi_id');
    }
    public function distribusiNilai(): HasOne
    {
        return $this->hasOne(EvaluasiDistribusiNilai::class, 'laporan_evaluasi_id');
    }
    public function hambatans(): HasMany
    {
        return $this->hasMany(EvaluasiHambatan::class, 'laporan_evaluasi_id')->orderBy('no_urut');
    }
    public function tindakLanjuts(): HasMany
    {
        return $this->hasMany(EvaluasiTindakLanjut::class, 'laporan_evaluasi_id')->orderBy('no_urut');
    }
}
