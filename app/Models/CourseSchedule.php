<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CourseSchedule extends Model
{
    protected $table = 'course_schedules';

    protected $fillable = [
        'mata_kuliah_id',
        'class_name',
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'semester',
        'academic_year',
        'student_count',
        'is_locked',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'locked_by');
    }

    // ──────────────────────────────────────────────────────────────
    // Computed attributes
    // ──────────────────────────────────────────────────────────────

    /** Durasi dalam menit. */
    public function getDurationMinutesAttribute(): int
    {
        [$sh, $sm] = explode(':', $this->start_time);
        [$eh, $em] = explode(':', $this->end_time);
        return ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
    }

    /** Jumlah SKS setara (1 SKS = 50 menit). */
    public function getSksAttribute(): int
    {
        return (int) round($this->duration_minutes / 50);
    }

    /** True if schedule is locked. */
    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    // ──────────────────────────────────────────────────────────────
    // Lock / Unlock helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Lock this schedule.
     * @param  int|null $userId  Defaults to currently authenticated user.
     */
    public function lock(?int $userId = null): bool
    {
        if ($this->is_locked) return false;

        return $this->update([
            'is_locked' => true,
            'locked_by' => $userId ?? Auth::id(),
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock this schedule.
     */
    public function unlock(): bool
    {
        if (!$this->is_locked) return false;

        return $this->update([
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────

    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }
}

