<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBlockRule extends Model
{
    protected $table = 'room_block_rules';

    protected $fillable = [
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'description',
    ];

    protected $casts = [];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Cek apakah aturan blok ini overlap dengan rentang waktu yang diberikan.
     */
    public function overlaps(string $start, string $end): bool
    {
        return $this->start_time < $end && $this->end_time > $start;
    }
}
