<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * CMS Content Workflow Model
 *
 * Manages content review and approval workflow
 */
class CmsContentWorkflow extends Model
{
    use HasFactory;

    protected $table = 'cms_content_workflow';

    protected $fillable = [
        'workflowable_type',
        'workflowable_id',
        'status',
        'previous_status',
        'author_id',
        'reviewer_id',
        'approver_id',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'published_at',
        'review_notes',
        'revision_history',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'revision_history' => 'array',
    ];

    // Workflow status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Get the workflowable model (Page, etc.)
     */
    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the author of the content
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the assigned reviewer
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending review
     */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    /**
     * Scope for approved content
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope by author
     */
    public function scopeByAuthor(Builder $query, int $authorId): Builder
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Scope by reviewer
     */
    public function scopeByReviewer(Builder $query, int $reviewerId): Builder
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    /**
     * Submit content for review
     */
    public function submitForReview(?User $reviewer = null): self
    {
        $this->update([
            'status' => self::STATUS_PENDING_REVIEW,
            'previous_status' => $this->status,
            'submitted_at' => now(),
            'reviewer_id' => $reviewer?->id,
        ]);

        $this->addToRevisionHistory('Submitted for review');

        return $this;
    }

    /**
     * Approve content
     */
    public function approve(User $approver, ?string $notes = null): self
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'previous_status' => $this->status,
            'approver_id' => $approver->id,
            'approved_at' => now(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        $this->addToRevisionHistory('Approved by ' . $approver->name, $notes);

        return $this;
    }

    /**
     * Reject content
     */
    public function reject(User $reviewer, string $reason): self
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'previous_status' => $this->status,
            'reviewer_id' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);

        $this->addToRevisionHistory('Rejected by ' . $reviewer->name, $reason);

        return $this;
    }

    /**
     * Publish content
     */
    public function publish(): self
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'previous_status' => $this->status,
            'published_at' => now(),
        ]);

        $this->addToRevisionHistory('Published');

        // Update the actual content model status
        if ($this->workflowable && method_exists($this->workflowable, 'publish')) {
            $this->workflowable->publish();
        }

        return $this;
    }

    /**
     * Return to draft
     */
    public function returnToDraft(): self
    {
        $this->update([
            'status' => self::STATUS_DRAFT,
            'previous_status' => $this->status,
            'reviewer_id' => null,
            'approver_id' => null,
            'submitted_at' => null,
            'reviewed_at' => null,
            'approved_at' => null,
        ]);

        $this->addToRevisionHistory('Returned to draft');

        return $this;
    }

    /**
     * Archive content
     */
    public function archive(): self
    {
        $this->update([
            'status' => self::STATUS_ARCHIVED,
            'previous_status' => $this->status,
        ]);

        $this->addToRevisionHistory('Archived');

        return $this;
    }

    /**
     * Add entry to revision history
     */
    public function addToRevisionHistory(string $action, ?string $notes = null): self
    {
        $history = $this->revision_history ?: [];

        $history[] = [
            'action' => $action,
            'notes' => $notes,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['revision_history' => $history]);

        return $this;
    }

    /**
     * Check if content is in draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if content is pending review
     */
    public function isPendingReview(): bool
    {
        return $this->status === self::STATUS_PENDING_REVIEW;
    }

    /**
     * Check if content is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if content is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if content is published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if content is archived
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Get available workflow statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_REVIEW => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Get workflow statistics
     */
    public static function getWorkflowStats(): array
    {
        return [
            'total' => self::count(),
            'pending_review' => self::pendingReview()->count(),
            'approved' => self::approved()->count(),
            'rejected' => self::byStatus(self::STATUS_REJECTED)->count(),
            'published' => self::byStatus(self::STATUS_PUBLISHED)->count(),
        ];
    }

    /**
     * Assign reviewer
     */
    public function assignReviewer(User $reviewer): self
    {
        $this->update(['reviewer_id' => $reviewer->id]);
        $this->addToRevisionHistory('Reviewer assigned: ' . $reviewer->name);

        return $this;
    }

    /**
     * Get time in current status
     */
    public function getTimeInStatus(): \Carbon\Carbon
    {
        return $this->updated_at->diffForHumans();
    }

    /**
     * Check if workflow can be transitioned to status
     */
    public function canTransitionTo(string $status): bool
    {
        $allowedTransitions = [
            self::STATUS_DRAFT => [self::STATUS_PENDING_REVIEW, self::STATUS_ARCHIVED],
            self::STATUS_PENDING_REVIEW => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_DRAFT],
            self::STATUS_APPROVED => [self::STATUS_PUBLISHED, self::STATUS_DRAFT],
            self::STATUS_REJECTED => [self::STATUS_DRAFT],
            self::STATUS_PUBLISHED => [self::STATUS_ARCHIVED],
            self::STATUS_ARCHIVED => [self::STATUS_DRAFT],
        ];

        return in_array($status, $allowedTransitions[$this->status] ?? []);
    }
}