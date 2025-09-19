<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class CmsAuditLog extends Model
{
    use HasFactory;

    protected $table = 'cms_audit_log';

    protected $fillable = [
        'event',
        'auditable_type',
        'auditable_id',
        'user_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    const EVENT_ROLE_ASSIGNED = 'role_assigned';
    const EVENT_ROLE_REMOVED = 'role_removed';
    const EVENT_PERMISSION_CHANGED = 'permission_changed';
    const EVENT_CONTENT_ACCESSED = 'content_accessed';
    const EVENT_CONTENT_CREATED = 'content_created';
    const EVENT_CONTENT_UPDATED = 'content_updated';
    const EVENT_CONTENT_DELETED = 'content_deleted';
    const EVENT_CONTENT_PUBLISHED = 'content_published';
    const EVENT_WORKFLOW_SUBMITTED = 'workflow_submitted';
    const EVENT_WORKFLOW_APPROVED = 'workflow_approved';
    const EVENT_WORKFLOW_REJECTED = 'workflow_rejected';
    const EVENT_LOGIN = 'login';
    const EVENT_LOGOUT = 'logout';
    const EVENT_FAILED_LOGIN = 'failed_login';
    const EVENT_PERMISSION_DENIED = 'permission_denied';
    const EVENT_MEDIA_UPLOADED = 'media_uploaded';
    const EVENT_MEDIA_DELETED = 'media_deleted';
    const EVENT_SETTINGS_CHANGED = 'settings_changed';

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function logEvent(
        string $event,
        ?Model $auditable = null,
        ?User $user = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): self {
        return self::create([
            'event' => $event,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->id,
            'user_id' => $user?->id ?: auth()->id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
        ]);
    }
}
