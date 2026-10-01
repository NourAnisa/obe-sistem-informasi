<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentPortfolio extends Model
{
    protected $table = 'student_portfolios';

    protected $fillable = [
        'mahasiswa_id',
        'semester_aktif',
        'completion_percentage',
        'status',
        'description',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'completion_percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(PortfolioEvidence::class);
    }

    public function progressLogs(): HasMany
    {
        return $this->hasMany(PortfolioProgressLog::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(PortfolioView::class);
    }

    // Scopes
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrentSemester(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('semester_aktif', config('obe.tahun_akademik', '2025/2026'));
    }

    // Accessors
    public function getApprovedEvidencesCountAttribute(): int
    {
        return $this->evidences()->where('status', 'approved')->count();
    }

    public function getTotalEvidencesCountAttribute(): int
    {
        return $this->evidences()->count();
    }

    public function getCpmkCoverageAttribute(): array
    {
        return $this->evidences()
            ->whereIn('status', ['approved', 'under_review'])
            ->with('cpmkMappings')
            ->get()
            ->pluck('cpmkMappings')
            ->flatten()
            ->pluck('cpmk_id')
            ->unique()
            ->toArray();
    }

    // Methods
    public function updateCompletionPercentage(): void
    {
        $total = $this->evidences()->count();
        if ($total === 0) {
            $this->completion_percentage = 0;
        } else {
            $approved = $this->evidences()->where('status', 'approved')->count();
            $this->completion_percentage = round(($approved / $total) * 100, 2);
        }
        $this->save();

        // Log progress
        PortfolioProgressLog::create([
            'student_portfolio_id' => $this->id,
            'completion_percentage' => $this->completion_percentage,
            'total_evidences' => $total,
            'approved_evidences' => $approved,
        ]);
    }

    public function getPortfolioSummary(): array
    {
        return [
            'total_evidences' => $this->total_evidences_count,
            'approved_evidences' => $this->approved_evidences_count,
            'completion_percentage' => $this->completion_percentage,
            'cpmk_coverage_count' => count($this->cpmk_coverage),
            'status' => $this->status,
        ];
    }
}
