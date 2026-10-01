<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PortfolioEvidence extends Model
{
    protected $table = 'portfolio_evidences';

    protected $fillable = [
        'student_portfolio_id',
        'sub_cpmk_id',
        'evidence_type',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_mime_type',
        'file_size',
        'reflection_notes',
        'quality_rating',
        'status',
        'dosen_feedback',
        'reviewed_by',
        'reviewed_at',
        'external_link',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'quality_rating' => 'integer',
        'file_size' => 'integer',
    ];

    // Relationships
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(StudentPortfolio::class, 'student_portfolio_id');
    }

    public function subCpmk(): BelongsTo
    {
        return $this->belongsTo(SubCpmk::class, 'sub_cpmk_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function cpmkMappings(): BelongsToMany
    {
        return $this->belongsToMany(
            Cpmk::class,
            'evidence_cpmk_mapping',
            'evidence_id',
            'cpmk_id'
        )->withPivot('demonstration_level', 'comment')->withTimestamps();
    }

    // Scopes
    public function scopeApproved(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeSubmitted(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('status', ['submitted', 'under_review']);
    }

    public function scopeByType(\Illuminate\Database\Eloquent\Builder $query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('evidence_type', $type);
    }

    public function scopePending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'draft');
    }

    // Methods
    public function approve(int $userId, string $feedback = null, int $rating = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'dosen_feedback' => $feedback,
            'quality_rating' => $rating,
        ]);

        // Update portfolio completion
        $this->portfolio->updateCompletionPercentage();
    }

    public function reject(int $userId, string $feedback = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'dosen_feedback' => $feedback,
        ]);
    }

    public function submit(): void
    {
        $this->update(['status' => 'submitted']);
    }

    public function requestChanges(int $userId, string $feedback): void
    {
        $this->update([
            'status' => 'under_review',
            'reviewed_by' => $userId,
            'dosen_feedback' => $feedback,
        ]);
    }

    public function getQualityLabel(): string
    {
        return match ($this->quality_rating) {
            1 => 'Poor',
            2 => 'Below Average',
            3 => 'Average',
            4 => 'Good',
            5 => 'Excellent',
            default => 'Not Rated',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'submitted' => 'blue',
            'under_review' => 'amber',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    public function getMimeIcon(): string
    {
        return match (true) {
            str_contains($this->file_mime_type, 'pdf') => '📄',
            str_contains($this->file_mime_type, 'word') => '📝',
            str_contains($this->file_mime_type, 'excel') => '📊',
            str_contains($this->file_mime_type, 'image') => '🖼️',
            str_contains($this->file_mime_type, 'video') => '🎥',
            default => '📎',
        };
    }
}
