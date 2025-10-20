<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'book_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    // Relationships

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
