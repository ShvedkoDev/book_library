<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTermsAcceptance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'terms_version_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'terms_version_id' => 'integer',
        ];
    }

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function termsVersion()
    {
        return $this->belongsTo(TermsOfUseVersion::class, 'terms_version_id');
    }

    // Scopes

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByVersion($query, $versionId)
    {
        return $query->where('terms_version_id', $versionId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
