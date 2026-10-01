<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BapToken extends Model
{
    protected $table = 'bap_tokens';

    protected $fillable = [
        'bap_pertemuan_id',
        'token',
        'expired_at',
        'created_by',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function bapPertemuan(): BelongsTo
    {
        return $this->belongsTo(BapPertemuan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Check if token is still valid (not expired). */
    public function isValid(): bool
    {
        return $this->expired_at->isFuture();
    }

    /** Find latest valid token for a meeting. */
    public static function latestValid(int $bapPertemuanId): ?self
    {
        return static::where('bap_pertemuan_id', $bapPertemuanId)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();
    }
}
