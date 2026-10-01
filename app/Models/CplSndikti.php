<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CplSndikti extends Model
{
    protected $table = 'cpl_sndikti';

    protected $fillable = ['kode', 'kategori', 'deskripsi'];

    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(Cpl::class, 'cpl_sndikti_cpl');
    }
}
