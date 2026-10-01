<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioProgressLog extends Model
{
    protected $table = 'portfolio_progress_logs';

    protected $fillable = [
        'student_portfolio_id',
        'completion_percentage',
        'total_evidences',
        'approved_evidences',
        'notes',
        'logged_at',
    ];

    protected $casts = [
        'completion_percentage' => 'decimal:2',
        'total_evidences' => 'integer',
        'approved_evidences' => 'integer',
        'logged_at' => 'datetime',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(StudentPortfolio::class, 'student_portfolio_id');
    }
}
