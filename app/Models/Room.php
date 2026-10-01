<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'code',
        'name',
        'building',
        'floor',
        'capacity',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity'  => 'integer',
    ];

    public function courseSchedules(): HasMany
    {
        return $this->hasMany(CourseSchedule::class);
    }

    public function blockRules(): HasMany
    {
        return $this->hasMany(RoomBlockRule::class);
    }

    public function baps(): HasMany
    {
        return $this->hasMany(Bap::class);
    }

    /** Label lengkap ruangan untuk tampilan. */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->building, "Lt.{$this->floor}", $this->code]);
        return $this->name . ($parts ? ' (' . implode(' ', $parts) . ')' : '');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'class'  => 'Kelas',
            'lab'    => 'Laboratorium',
            'aula'   => 'Aula',
            'online' => 'Online/Daring',
            'field'  => 'Lapangan',
            default  => ucfirst($this->type),
        };
    }
}
