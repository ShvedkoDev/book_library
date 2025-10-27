<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookNote extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'note',
        'page_number',
        'is_private',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
            'page_number' => 'integer',
        ];
    }

    /**
     * Get the user that owns the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that the note belongs to.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope to only include private notes.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope to get notes for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope to get notes for a specific book.
     */
    public function scopeForBook($query, Book $book)
    {
        return $query->where('book_id', $book->id);
    }
}
