<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_id',
        'palm_code',
        'title',
        'subtitle',
        'translated_title',
        'slug',
        'physical_type',
        'collection_id',
        'publisher_id',
        'publication_year',
        'pages',
        'description',
        'toc',
        'notes_issue',
        'notes_content',
        'contact',
        'access_level',
        'vla_standard',
        'vla_benchmark',
        'is_featured',
        'is_active',
        'view_count',
        'download_count',
        'sort_order',
        'duplicated_from_book_id',
        'duplicated_at',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically generate slug when creating a new book
        static::creating(function ($book) {
            if (empty($book->slug)) {
                $slug = Str::slug($book->title);

                // Ensure uniqueness
                $originalSlug = $slug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $book->slug = $slug;
            }
        });

        // Update slug when title changes
        static::updating(function ($book) {
            if ($book->isDirty('title') && empty($book->slug)) {
                $slug = Str::slug($book->title);

                // Ensure uniqueness
                $originalSlug = $slug;
                $counter = 1;
                while (static::where('slug', $slug)->where('id', '!=', $book->id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }

                $book->slug = $slug;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'collection_id' => 'integer',
            'publisher_id' => 'integer',
            'publication_year' => 'integer',
            'pages' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'view_count' => 'integer',
            'download_count' => 'integer',
            'sort_order' => 'integer',
            'duplicated_from_book_id' => 'integer',
            'duplicated_at' => 'datetime',
        ];
    }

    // Accessors & Mutators for Filament forms

    public function getKeywordListAttribute()
    {
        return $this->keywords->pluck('keyword')->toArray();
    }

    public function setKeywordListAttribute($value)
    {
        // This will be handled in the Filament Resource using afterSave hook
        // Store temporarily for processing
        $this->attributes['_keyword_list'] = json_encode($value ?? []);
    }

    // Relationships - Core

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    // Relationships - Creators

    public function bookCreators()
    {
        return $this->hasMany(BookCreator::class);
    }

    public function creators()
    {
        return $this->belongsToMany(Creator::class, 'book_creators')
            ->withPivot('creator_type', 'role_description', 'sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function authors()
    {
        return $this->belongsToMany(Creator::class, 'book_creators')
            ->wherePivot('creator_type', 'author')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function illustrators()
    {
        return $this->belongsToMany(Creator::class, 'book_creators')
            ->wherePivot('creator_type', 'illustrator')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function editors()
    {
        return $this->belongsToMany(Creator::class, 'book_creators')
            ->wherePivot('creator_type', 'editor')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    // Relationships - Languages

    public function bookLanguages()
    {
        return $this->hasMany(BookLanguage::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'book_languages')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryLanguage()
    {
        return $this->belongsToMany(Language::class, 'book_languages')
            ->wherePivot('is_primary', true)
            ->withTimestamps()
            ->first();
    }

    // Relationships - Classifications

    public function bookClassifications()
    {
        return $this->hasMany(BookClassification::class);
    }

    public function classificationValues()
    {
        return $this->belongsToMany(ClassificationValue::class, 'book_classifications')
            ->withTimestamps();
    }

    public function getClassificationsByType($typeSlug)
    {
        return $this->classificationValues()
            ->whereHas('classificationType', function ($q) use ($typeSlug) {
                $q->where('slug', $typeSlug);
            })
            ->get();
    }

    // Classification type-specific relationships for Filament
    public function purposeClassifications()
    {
        return $this->belongsToMany(ClassificationValue::class, 'book_classifications')
            ->whereHas('classificationType', function ($q) {
                $q->where('slug', 'purpose');
            })
            ->withTimestamps();
    }

    public function genreClassifications()
    {
        return $this->belongsToMany(ClassificationValue::class, 'book_classifications')
            ->whereHas('classificationType', function ($q) {
                $q->where('slug', 'genre');
            })
            ->withTimestamps();
    }

    public function subgenreClassifications()
    {
        return $this->belongsToMany(ClassificationValue::class, 'book_classifications')
            ->whereHas('classificationType', function ($q) {
                $q->where('slug', 'sub-genre');
            })
            ->withTimestamps();
    }

    public function typeClassifications()
    {
        return $this->belongsToMany(ClassificationValue::class, 'book_classifications')
            ->whereHas('classificationType', function ($q) {
                $q->where('slug', 'type');
            })
            ->withTimestamps();
    }

    public function themesClassifications()
    {
        return $this->belongsToMany(ClassificationValue::class, 'book_classifications')
            ->whereHas('classificationType', function ($q) {
                $q->where('slug', 'themes-uses');
            })
            ->withTimestamps();
    }

    public function learnerLevelClassifications()
    {
        return $this->belongsToMany(ClassificationValue::class, 'book_classifications')
            ->whereHas('classificationType', function ($q) {
                $q->where('slug', 'learner-level');
            })
            ->withTimestamps();
    }

    // Relationships - Geographic

    public function bookLocations()
    {
        return $this->hasMany(BookLocation::class);
    }

    public function geographicLocations()
    {
        return $this->belongsToMany(GeographicLocation::class, 'book_locations', 'book_id', 'location_id')
            ->withTimestamps();
    }

    // Relationships - Keywords

    public function keywords()
    {
        return $this->hasMany(BookKeyword::class);
    }

    // Relationships - Files

    public function files()
    {
        return $this->hasMany(BookFile::class);
    }

    public function primaryPdf()
    {
        return $this->hasOne(BookFile::class)
            ->where('file_type', 'pdf')
            ->where('is_primary', true);
    }

    public function primaryThumbnail()
    {
        return $this->hasOne(BookFile::class)
            ->where('file_type', 'thumbnail')
            ->where('is_primary', true);
    }

    public function audioFiles()
    {
        return $this->hasMany(BookFile::class)->where('file_type', 'audio');
    }

    public function videoFiles()
    {
        return $this->hasMany(BookFile::class)->where('file_type', 'video');
    }

    // Relationships - Library References

    public function libraryReferences()
    {
        return $this->hasMany(LibraryReference::class);
    }

    // Relationships - Book Relationships

    public function bookRelationships()
    {
        return $this->hasMany(BookRelationship::class, 'book_id');
    }

    public function relatedBooks($type = null)
    {
        $query = $this->belongsToMany(Book::class, 'book_relationships', 'book_id', 'related_book_id')
            ->withPivot('relationship_type', 'relationship_code', 'description')
            ->withTimestamps();

        if ($type) {
            $query->wherePivot('relationship_type', $type);
        }

        return $query;
    }

    public function sameVersionBooks()
    {
        return $this->relatedBooks('same_version');
    }

    public function sameLanguageBooks()
    {
        return $this->relatedBooks('same_language');
    }

    public function supportingMaterials()
    {
        return $this->relatedBooks('supporting');
    }

    public function otherLanguageBooks()
    {
        return $this->relatedBooks('other_language');
    }

    // Relationships - User Engagement

    public function ratings()
    {
        return $this->hasMany(BookRating::class);
    }

    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(BookBookmark::class);
    }

    public function userBookmarks()
    {
        return $this->hasMany(UserBookmark::class);
    }

    public function bookmarkedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_bookmarks')->withTimestamps();
    }

    public function views()
    {
        return $this->hasMany(BookView::class);
    }

    public function downloads()
    {
        return $this->hasMany(BookDownload::class);
    }

    public function notes()
    {
        return $this->hasMany(BookNote::class);
    }

    public function updated_by_user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relationships - Data Quality

    public function dataQualityIssues()
    {
        return $this->hasMany(DataQualityIssue::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeAccessLevel($query, $level)
    {
        if (is_array($level)) {
            return $query->whereIn('access_level', $level);
        }
        return $query->where('access_level', $level);
    }

    public function scopeFullAccess($query)
    {
        return $query->where('access_level', 'full');
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('access_level', ['full', 'limited']);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('subtitle', 'like', "%{$term}%")
                ->orWhere('translated_title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('publication_year', $year);
    }

    public function scopeByYearRange($query, $startYear, $endYear)
    {
        return $query->whereBetween('publication_year', [$startYear, $endYear]);
    }

    public function scopeByCollection($query, $collectionId)
    {
        return $query->where('collection_id', $collectionId);
    }

    public function scopeByPublisher($query, $publisherId)
    {
        return $query->where('publisher_id', $publisherId);
    }

    public function scopeWithCreator($query, $creatorId, $type = null)
    {
        return $query->whereHas('bookCreators', function ($q) use ($creatorId, $type) {
            $q->where('creator_id', $creatorId);
            if ($type) {
                $q->where('creator_type', $type);
            }
        });
    }

    public function scopeWithLanguage($query, $languageId)
    {
        return $query->whereHas('bookLanguages', function ($q) use ($languageId) {
            $q->where('language_id', $languageId);
        });
    }

    public function scopeWithClassification($query, $classificationValueId)
    {
        return $query->whereHas('bookClassifications', function ($q) use ($classificationValueId) {
            $q->where('classification_value_id', $classificationValueId);
        });
    }

    public function scopeWithLocation($query, $locationId)
    {
        return $query->whereHas('bookLocations', function ($q) use ($locationId) {
            $q->where('location_id', $locationId);
        });
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('view_count', 'desc')->limit($limit);
    }

    public function scopeMostDownloaded($query, $limit = 10)
    {
        return $query->orderBy('download_count', 'desc')->limit($limit);
    }

    public function scopeRecentlyAdded($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    // Helper Methods

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    public function getAverageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function getTotalRatings()
    {
        return $this->ratings()->count();
    }

    public function getApprovedReviews()
    {
        return $this->reviews()->where('is_approved', true)->where('is_active', true)->get();
    }

    public function isBookmarkedBy($userId)
    {
        return $this->userBookmarks()->where('user_id', $userId)->exists();
    }

    public function isRatedBy($userId)
    {
        return $this->ratings()->where('user_id', $userId)->exists();
    }

    public function getUserRating($userId)
    {
        return $this->ratings()->where('user_id', $userId)->first();
    }

    public function getNotesForUser($userId)
    {
        return $this->notes()->where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get thumbnail URL for this book
     * Uses ThumbnailService to generate placeholder if needed
     */
    public function getThumbnailUrl(): string
    {
        $thumbnailService = app(\App\Services\ThumbnailService::class);
        return $thumbnailService->getThumbnailUrl($this);
    }

    /**
     * Check if book has an existing thumbnail file
     */
    public function hasThumbnail(): bool
    {
        return $this->primaryThumbnail()
            ->whereNotNull('file_path')
            ->exists();
    }

    // ===============================================================
    // DUPLICATION METHODS
    // ===============================================================

    /**
     * Relationship: Book this was duplicated from
     */
    public function duplicatedFrom()
    {
        return $this->belongsTo(Book::class, 'duplicated_from_book_id');
    }

    /**
     * Relationship: Books that were duplicated from this book
     */
    public function duplicates()
    {
        return $this->hasMany(Book::class, 'duplicated_from_book_id');
    }

    /**
     * Check if this book is a duplicate
     *
     * @return bool
     */
    public function isDuplicate(): bool
    {
        return !is_null($this->duplicated_from_book_id);
    }

    /**
     * Check if this book has been duplicated
     *
     * @return bool
     */
    public function hasBeenDuplicated(): bool
    {
        return $this->duplicates()->exists();
    }

    /**
     * Get the number of times this book has been duplicated
     *
     * @return int
     */
    public function getDuplicateCount(): int
    {
        return $this->duplicates()->count();
    }

    /**
     * Duplicate this book with all relationships
     *
     * This is a convenience method that uses the BookDuplicationService
     * for the actual duplication logic.
     *
     * @param array $options Duplication options (see BookDuplicationService)
     * @return Book The newly created duplicate
     * @throws \Exception If duplication fails
     *
     * @example
     * // Basic duplication
     * $duplicate = $book->duplicate();
     *
     * @example
     * // Custom duplication
     * $duplicate = $book->duplicate([
     *     'copy_files' => true,
     *     'clear_title' => false,
     *     'append_copy_suffix' => true,
     * ]);
     */
    public function duplicate(array $options = []): Book
    {
        $duplicationService = app(\App\Services\BookDuplicationService::class);
        return $duplicationService->duplicate($this, $options);
    }

    /**
     * Get duplication statistics for this book
     *
     * @return array Statistics including duplicate count, source info, etc.
     */
    public function getDuplicationStats(): array
    {
        $duplicationService = app(\App\Services\BookDuplicationService::class);
        return $duplicationService->getDuplicationStats($this);
    }

    /**
     * Get the original source book (traverses duplication chain)
     *
     * @return Book|null The original source book, or null if not a duplicate
     */
    public function getOriginalSource(): ?Book
    {
        $duplicationService = app(\App\Services\BookDuplicationService::class);
        return $duplicationService->getOriginalSource($this);
    }

    /**
     * Validate that this book can be safely duplicated
     *
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function canBeDuplicated(): array
    {
        $duplicationService = app(\App\Services\BookDuplicationService::class);
        return $duplicationService->validateForDuplication($this);
    }

    /**
     * Scope: Get only books that are duplicates
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDuplicates($query)
    {
        return $query->whereNotNull('duplicated_from_book_id');
    }

    /**
     * Scope: Get only original books (not duplicates)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOriginals($query)
    {
        return $query->whereNull('duplicated_from_book_id');
    }

    /**
     * Scope: Get books duplicated within a date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDuplicatedBetween($query, $startDate, $endDate = null)
    {
        $query->whereNotNull('duplicated_at')
            ->where('duplicated_at', '>=', $startDate);

        if ($endDate) {
            $query->where('duplicated_at', '<=', $endDate);
        }

        return $query;
    }
}
