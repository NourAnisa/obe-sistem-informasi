<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioView extends Model
{
    protected $table = 'portfolio_views';

    protected $fillable = [
        'student_portfolio_id',
        'viewed_by',
        'viewer_type',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(StudentPortfolio::class, 'student_portfolio_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }
}
