<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'dekan', 'wakildekan', 'kaprodi']);
    }

    /**
     * Hierarki role (urutan dari tertinggi ke terendah):
     * admin > dekan > wakildekan > kaprodi > dosen > akademik > kemahasiswaan > mahasiswa
     */
    public static function roleOrder(): array
    {
        return [
            'admin'          => 1,
            'dekan'          => 2,
            'wakildekan'     => 3,
            'kaprodi'        => 4,
            'dosen'          => 5,
            'akademik'       => 6,
            'kemahasiswaan'  => 7,
            'mahasiswa'      => 8,
        ];
    }

    public static function roleLabels(): array
    {
        return [
            'admin'          => 'Admin',
            'dekan'          => 'Dekan',
            'wakildekan'     => 'Wakil Dekan',
            'kaprodi'        => 'Kaprodi',
            'dosen'          => 'Dosen',
            'akademik'       => 'Akademik',
            'kemahasiswaan'  => 'Kemahasiswaan',
            'mahasiswa'      => 'Mahasiswa',
        ];
    }

    public function getRoleLabelAttribute(): string
    {
        return static::roleLabels()[$this->role] ?? $this->role;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'nik',
        'nuptk',
        'google_scholar_id',
        'last_sync_at',
        'program_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_sync_at'      => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function program(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Program::class, 'program_id');
    }

    public function publikasi(): HasMany
    {
        return $this->hasMany(\App\Models\PublikasiDosen::class, 'dosen_id');
    }

    public function mahasiswa(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Mahasiswa::class, 'user_id');
    }
}
