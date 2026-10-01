<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RpsReferensi extends Model
{
    protected $table = 'rps_referensi';

    protected $fillable = [
        'mata_kuliah_id',
        'judul',
        'penulis',
        'tahun',
        'penerbit',
        'kota',
        'url',
        'jenis',
        'urutan',
    ];

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class);
    }

    /** Formatted citation string — safe to render as {!! $ref->citation !!} */
    public function getCitationAttribute(): string
    {
        $parts = [];
        if ($this->penulis)  $parts[] = e($this->penulis);
        if ($this->tahun)    $parts[] = '(' . e($this->tahun) . ')';
        if ($this->judul)    $parts[] = '<em>' . e($this->judul) . '</em>';
        if ($this->penerbit) $parts[] = e($this->penerbit);
        if ($this->kota)     $parts[] = e($this->kota);
        if ($this->url) {
            $url = e($this->url);
            $parts[] = "<a href='{$url}' class='text-blue-600' target='_blank' rel='noopener noreferrer'>{$url}</a>";
        }
        return implode('. ', $parts);
    }
}
