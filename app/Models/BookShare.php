<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookShare extends Model
{
    protected $fillable = [
        'book_id',
        'user_id',
        'share_method',
        'ip_address',
        'user_agent',
        'shared_url',
    ];

    /**
     * Get the book that was shared.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user who shared the book (if authenticated).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
