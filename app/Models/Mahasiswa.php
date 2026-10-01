<?php

namespace App\Models;

use App\Models\Scopes\ProgramScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';

    protected static function booted(): void
    {
        static::addGlobalScope(new ProgramScope);
    }

    protected $fillable = [
        'user_id',
        'nim',
        'nama',
        'angkatan',
        'prodi',
        'program_id',
        'aktif',
        'dosen_pa_id',
        'semester',
        'tahun_akademik',
        'ipk',
        'ips',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'ipk'   => 'float',
        'ips'   => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function dosenPa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_pa_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(MahasiswaMk::class);
    }

    public function evaluasis(): HasMany
    {
        return $this->hasMany(BapMahasiswaEvaluasi::class);
    }

    /** Batas maksimum SKS yang bisa diambil berdasarkan semester, IPK, dan IPS. */
    public function maxSks(): int
    {
        // Semester 1: maks 20 SKS (belum ada IPK/IPS)
        if (($this->semester ?? 1) <= 1) {
            return 20;
        }
        // Semester 2+: 24 SKS jika IPK ≥ 3.00 DAN IPS ≥ 3.00, else 20 SKS
        return ($this->ipk >= 3.00 && $this->ips >= 3.00) ? 24 : 20;
    }

    /** Daftar semester MK yang boleh diambil mahasiswa ini.
     *  Mahasiswa semester N (ganjil) boleh ambil semua semester ganjil ≤ N+2 dan genap ≤ N.
     *  Mahasiswa semester N (genap) boleh ambil semua semester genap ≤ N+2 dan ganjil ≤ N.
     *  Untuk semester 2+: jika IPK & IPS memenuhi, boleh ambil 1 kelompok semester lebih tinggi.
     */
    public function allowedSemesters(): array
    {
        $sem     = (int) ($this->semester ?? 1);
        $isOdd   = $sem % 2 === 1; // mahasiswa di semester ganjil

        // Kelompok yang "searah": ganjil jika semester mahasiswa ganjil, genap jika genap
        $sameGroup = $isOdd
            ? [1, 3, 5, 7]  // semester ganjil
            : [2, 4, 6, 8]; // semester genap

        // Boleh ambil semester searah up to semester aktif (+2 jika nilai bagus)
        $maxSameGroup = $sem;
        if ($sem > 1 && $this->ipk >= 3.00 && $this->ips >= 3.00) {
            $maxSameGroup = $sem + 2; // boleh ambil 1 step lebih tinggi
        }

        $allowed = array_filter($sameGroup, fn($s) => $s <= $maxSameGroup);

        // Semester berlawanan: hanya yang sudah dilewati (≤ sem - 1)
        $otherGroup = $isOdd ? [2, 4, 6, 8] : [1, 3, 5, 7];
        $allowed    = array_merge($allowed, array_filter($otherGroup, fn($s) => $s < $sem));

        sort($allowed);
        return array_values(array_unique($allowed));
    }
}
