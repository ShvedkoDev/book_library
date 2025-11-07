<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mode',
        'options',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'skipped_rows',
        'created_count',
        'updated_count',
        'error_log',
        'error_summary',
        'success_log',
        'validation_errors',
        'started_at',
        'completed_at',
        'duration_seconds',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'error_summary' => 'array',
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'successful_rows' => 'integer',
            'failed_rows' => 'integer',
            'skipped_rows' => 'integer',
            'created_count' => 'integer',
            'updated_count' => 'integer',
            'file_size' => 'integer',
            'duration_seconds' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dataQualityIssues()
    {
        return $this->hasMany(DataQualityIssue::class);
    }

    // Scopes

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods

    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => now()->diffInSeconds($this->started_at),
        ]);
    }

    public function markAsFailed(string $error = null)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'error_log' => $error ? $this->error_log . "\n" . $error : $this->error_log,
        ]);
    }

    public function updateProgress(int $processed, int $successful = null, int $failed = null)
    {
        $data = ['processed_rows' => $processed];

        if ($successful !== null) {
            $data['successful_rows'] = $successful;
        }

        if ($failed !== null) {
            $data['failed_rows'] = $failed;
        }

        $this->update($data);
    }

    public function incrementCreated()
    {
        $this->increment('created_count');
        $this->increment('successful_rows');
        $this->increment('processed_rows');
    }

    public function incrementUpdated()
    {
        $this->increment('updated_count');
        $this->increment('successful_rows');
        $this->increment('processed_rows');
    }

    public function incrementFailed()
    {
        $this->increment('failed_rows');
        $this->increment('processed_rows');
    }

    public function incrementSkipped()
    {
        $this->increment('skipped_rows');
        $this->increment('processed_rows');
    }

    public function addError(int $row, string $column, string $message)
    {
        $errors = json_decode($this->error_log ?? '[]', true);
        $errors[] = [
            'row' => $row,
            'column' => $column,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
        $this->update(['error_log' => json_encode($errors)]);
    }

    public function getSuccessRate(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->successful_rows / $this->total_rows) * 100, 2);
    }

    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}
