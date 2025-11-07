<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataQualityIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'csv_import_id',
        'issue_type',
        'severity',
        'field_name',
        'message',
        'context',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'context' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book that has this issue
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the CSV import that created this issue
     */
    public function csvImport(): BelongsTo
    {
        return $this->belongsTo(CsvImport::class);
    }

    /**
     * Get the user who resolved this issue
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Mark this issue as resolved
     */
    public function markAsResolved(?string $notes = null, ?int $userId = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId ?? auth()->id(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Scope to get only unresolved issues
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope to get only critical issues
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope to get issues for a specific import
     */
    public function scopeForImport($query, int $importId)
    {
        return $query->where('csv_import_id', $importId);
    }

    /**
     * Scope to get issues by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('issue_type', $type);
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            default => 'gray',
        };
    }

    /**
     * Get severity icon for UI
     */
    public function getSeverityIconAttribute(): string
    {
        return match ($this->severity) {
            'critical' => 'heroicon-o-exclamation-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            'info' => 'heroicon-o-information-circle',
            default => 'heroicon-o-question-mark-circle',
        };
    }
}
