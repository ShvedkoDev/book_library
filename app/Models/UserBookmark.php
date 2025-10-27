<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBookmark extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'collection_name',
        'notes',
    ];

    /**
     * Get the user that owns the bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book that is bookmarked.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Check if a specific book is bookmarked by a user.
     */
    public static function isBookmarked(User $user, Book $book): bool
    {
        return self::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->exists();
    }

    /**
     * Toggle bookmark for a user and book.
     */
    public static function toggle(User $user, Book $book): array
    {
        $bookmark = self::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return ['bookmarked' => false, 'message' => 'Bookmark removed'];
        }

        self::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        return ['bookmarked' => true, 'message' => 'Book added to your collection'];
    }
}
