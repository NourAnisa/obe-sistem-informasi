<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bap extends Model
{
    protected $table = 'bap';

    protected $fillable = [
        'mata_kuliah_id',
        'semester_aktif',
        'kelas',
        'ruangan',
        'room_id',
        'jumlah_mahasiswa',
        'catatan',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function bapPertemuans(): HasMany
    {
        return $this->hasMany(BapPertemuan::class)->orderBy('minggu');
    }

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** Nama ruangan: preferensikan Room::name jika room_id diset, else fallback ke string 'ruangan'. */
    public function getRuanganLabelAttribute(): string
    {
        return $this->room?->full_name ?? $this->ruangan ?? '—';
    }
}
