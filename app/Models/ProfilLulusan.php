<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProfilLulusan extends Model
{
    protected $table = 'profil_lulusan';

    protected $fillable = ['kode', 'deskripsi', 'hard_skills', 'soft_skills', 'program_id'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(Cpl::class, 'cpl_profil_lulusan');
    }
}
