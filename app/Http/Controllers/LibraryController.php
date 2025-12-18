<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookRelationship;
use App\Models\Language;
use App\Models\ClassificationType;
use App\Models\AccessRequest;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    protected $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Normalize search term by removing apostrophes and diacritics
     */
    private function normalizeSearchTerm($term)
    {
        // Remove apostrophes
        $term = str_replace("'", '', $term);

        // Convert to lowercase
        $term = mb_strtolower($term, 'UTF-8');

        // Remove common diacritics
        $diacritics = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ã' => 'a', 'õ' => 'o', 'ñ' => 'n', 'ç' => 'c',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'Â' => 'A', 'Ê' => 'E', 'Î' => 'I', 'Ô' => 'O', 'Û' => 'U',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
            'Ã' => 'A', 'Õ' => 'O', 'Ñ' => 'N', 'Ç' => 'C',
        ];

        return str_replace(array_keys($diacritics), array_values($diacritics), $term);
    }

    /**
     * Generate MySQL expression to normalize a field for searching
     * (removes apostrophes and diacritics)
     */
    private function getNormalizedFieldExpression($fieldName)
    {
        // Build nested REPLACE() calls to remove apostrophes and diacritics
        // Order matters: remove apostrophes first, then diacritics
        $expression = "LOWER({$fieldName})";

        // Remove apostrophes
        $expression = "REPLACE({$expression}, \"'\", '')";

        // Remove diacritics (both uppercase and lowercase)
        $diacritics = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ã' => 'a', 'õ' => 'o', 'ñ' => 'n', 'ç' => 'c',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'Â' => 'A', 'Ê' => 'E', 'Î' => 'I', 'Ô' => 'O', 'Û' => 'U',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
            'Ã' => 'A', 'Õ' => 'O', 'Ñ' => 'N', 'Ç' => 'C',
        ];

        foreach ($diacritics as $from => $to) {
            $expression = "REPLACE({$expression}, '{$from}', '{$to}')";
        }

        return $expression;
    }

    /**
     * Display a listing of books with search and filters.
     */
    public function index(Request $request)
    {
        // Get filter and search parameters
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'title');
        $sortDirection = $request->input('sort_direction', 'asc');

        $filters = [
            'subjects' => $request->input('subjects', []),
            'genres' => $request->input('genres', []),
            'subgenres' => $request->input('subgenres', []),
            'grades' => $request->input('grades', []),
            'types' => $request->input('types', []),
            'languages' => $request->input('languages', []),
            'years' => $request->input('years', []),
        ];

        // Build the query
        $query = Book::query()
            ->select([
                'books.id',
                'books.title',
                'books.subtitle',
                'books.description',
                'books.publication_year',
                'books.access_level',
                'books.created_at',
                'books.slug',
                'books.view_count'
            ])
            ->with([
                'languages:id,name,code',
                'creators:id,name',
                'publisher:id,name',
                'files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true),
                'collection:id,title',
                'purposeClassifications:id,value',
                'learnerLevelClassifications:id,value'
            ])
            ->where('is_active', true);

        // Apply search with diacritics and apostrophe insensitivity
        if ($search) {
            // Normalize the search term (remove apostrophes and diacritics)
            $normalizedSearch = $this->normalizeSearchTerm($search);

            $query->where(function($q) use ($normalizedSearch) {
                // Define all book fields to search (including ALL metadata fields)
                $bookFields = [
                    'books.title',
                    'books.subtitle',
                    'books.translated_title',
                    'books.description',
                    'books.abstract',
                    'books.toc',
                    'books.notes_issue',
                    'books.notes_version',
                    'books.notes_content',
                    'books.internal_id',
                    'books.palm_code',
                ];

                // Search in all book fields with normalization
                foreach ($bookFields as $field) {
                    $normalizedField = $this->getNormalizedFieldExpression($field);
                    $q->orWhereRaw("{$normalizedField} LIKE ?", ["%{$normalizedSearch}%"]);
                }

                // Search in creators' names (with normalization)
                $q->orWhereHas('creators', function($creatorQuery) use ($normalizedSearch) {
                    $normalizedField = $this->getNormalizedFieldExpression('name');
                    $creatorQuery->whereRaw("{$normalizedField} LIKE ?", ["%{$normalizedSearch}%"]);
                });

                // Search in publisher name (with normalization)
                $q->orWhereHas('publisher', function($publisherQuery) use ($normalizedSearch) {
                    $normalizedField = $this->getNormalizedFieldExpression('name');
                    $publisherQuery->whereRaw("{$normalizedField} LIKE ?", ["%{$normalizedSearch}%"]);
                });

                // Search in collection title (with normalization)
                $q->orWhereHas('collection', function($collectionQuery) use ($normalizedSearch) {
                    $normalizedField = $this->getNormalizedFieldExpression('title');
                    $collectionQuery->whereRaw("{$normalizedField} LIKE ?", ["%{$normalizedSearch}%"]);
                });

                // Search in keywords
                $q->orWhereHas('keywords', function($keywordQuery) use ($normalizedSearch) {
                    $normalizedField = $this->getNormalizedFieldExpression('keyword');
                    $keywordQuery->whereRaw("{$normalizedField} LIKE ?", ["%{$normalizedSearch}%"]);
                });

                // Search in languages
                $q->orWhereHas('languages', function($languageQuery) use ($normalizedSearch) {
                    $normalizedField = $this->getNormalizedFieldExpression('name');
                    $languageQuery->whereRaw("{$normalizedField} LIKE ?", ["%{$normalizedSearch}%"]);
                });
            });
        }

        // Apply filters
        // Subject filter (Purpose classifications)
        if (!empty($filters['subjects'])) {
            $query->whereHas('purposeClassifications', fn($q) =>
                $q->whereIn('classification_values.id', $filters['subjects'])
            );
        }

        // Genre filter
        if (!empty($filters['genres'])) {
            $query->whereHas('genreClassifications', fn($q) =>
                $q->whereIn('classification_values.id', $filters['genres'])
            );
        }

        // Subgenre filter
        if (!empty($filters['subgenres'])) {
            $query->whereHas('subgenreClassifications', fn($q) =>
                $q->whereIn('classification_values.id', $filters['subgenres'])
            );
        }

        // Physical Type filter (books.physical_type_id)
        if (!empty($filters['types'])) {
            $query->whereIn('physical_type_id', $filters['types']);
        }

        // Language filter
        if (!empty($filters['languages'])) {
            $query->whereHas('languages', fn($q) =>
                $q->whereIn('languages.code', $filters['languages'])
            );
        }

        // Publication year filter
        if (!empty($filters['years'])) {
            $query->whereIn('publication_year', $filters['years']);
        }

        // Apply sorting
        $validSortColumns = ['title', 'publication_year', 'created_at', 'view_count'];
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = 'title';
        }
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $books = $query->paginate($perPage)->withQueryString();

        // Track search query if present
        if ($search) {
            $this->analytics->trackSearch($search, $books->total(), $request);
        }

        // Track filter usage if filters are applied
        if (!empty(array_filter($filters))) {
            $this->analytics->trackFilters($filters, $request);
        }

        // Get filter options for sidebar - only show options that are actually used by active books
        $availableLanguages = Language::whereHas('books', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get classification types with values - only include values that are actually used by books
        $availableSubjects = ClassificationType::where('slug', 'purpose')
            ->with(['classificationValues' => function($q) {
                $q->whereHas('books', fn($bookQuery) => $bookQuery->where('is_active', true));
            }])
            ->get();

        // Get Genre values (children of selected Purpose values)
        $availableGenres = ClassificationType::where('slug', 'genre')
            ->with(['classificationValues' => function($q) use ($filters) {
                $q->whereHas('books', fn($bookQuery) => $bookQuery->where('is_active', true));
                // If subjects are selected, only show genres that are children of those subjects
                if (!empty($filters['subjects'])) {
                    $q->whereIn('parent_id', $filters['subjects']);
                }
            }])
            ->get();

        // Get Subgenre values (children of selected Genre values)
        $availableSubgenres = ClassificationType::where('slug', 'sub-genre')
            ->with(['classificationValues' => function($q) use ($filters) {
                $q->whereHas('books', fn($bookQuery) => $bookQuery->where('is_active', true));
                // If genres are selected, only show subgenres that are children of those genres
                if (!empty($filters['genres'])) {
                    $q->whereIn('parent_id', $filters['genres']);
                }
            }])
            ->get();

        // Get Physical Types (from physical_types table, not classifications)
        $availablePhysicalTypes = \App\Models\PhysicalType::whereHas('books', function($q) {
            $q->where('is_active', true);
        })->orderBy('sort_order')->get();

        return view('library.index', compact(
            'books',
            'search',
            'filters',
            'perPage',
            'sortBy',
            'sortDirection',
            'availableLanguages',
            'availableSubjects',
            'availableGenres',
            'availableSubgenres',
            'availablePhysicalTypes'
        ));
    }

    /**
     * Display the specified book.
     */
    public function show(string $slug)
    {
        // Find book by slug with all relationships
        $book = Book::with([
            'languages',
            'creators',
            'authors',
            'illustrators',
            'editors',
            'publisher',
            'collection',
            'files',
            'geographicLocations',
            'purposeClassifications.classificationType',
            'genreClassifications.classificationType',
            'subgenreClassifications.classificationType',
            'typeClassifications.classificationType',
            'themesClassifications.classificationType',
            'learnerLevelClassifications.classificationType',
            'libraryReferences',
            'bookRelationships.relatedBook.files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true),
            'ratings',
            'reviews' => fn($q) => $q->where('is_approved', true)->where('is_active', true)->with('user')->latest(),
            'keywords',
        ])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

        // Track book view
        $this->analytics->trackBookView($book, request());

        // Calculate rating statistics
        $averageRating = $book->ratings()->avg('rating') ?? 0;
        $totalRatings = $book->ratings()->count();
        $ratingDistribution = [
            5 => $book->ratings()->where('rating', 5)->count(),
            4 => $book->ratings()->where('rating', 4)->count(),
            3 => $book->ratings()->where('rating', 3)->count(),
            2 => $book->ratings()->where('rating', 2)->count(),
            1 => $book->ratings()->where('rating', 1)->count(),
        ];

        // Get user's rating if authenticated
        $userRating = null;
        $userAccessRequest = null;
        $userNotes = collect();
        if (auth()->check()) {
            $userRating = $book->ratings()->where('user_id', auth()->id())->first();

            // Check if user has an existing access request for this book
            $userAccessRequest = AccessRequest::where('book_id', $book->id)
                ->where(function($q) {
                    $q->where('user_id', auth()->id())
                      ->orWhere('email', auth()->user()->email);
                })
                ->latest()
                ->first();

            // Get user's notes for this book
            $userNotes = $book->getNotesForUser(auth()->id());
        }

        $relatedBookEagerLoads = [
            'publisher:id,name',
            'languages:id,name,code',
            'purposeClassifications:id,value',
            'learnerLevelClassifications:id,value',
            'files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true),
        ];

        $relatedOtherEditions = $book->sameVersionBooks()
            ->with($relatedBookEagerLoads)
            ->where('books.is_active', true)
            ->orderBy('books.title')
            ->get();

        $relatedOtherLanguageVersions = $book->translatedBooks()
            ->with($relatedBookEagerLoads)
            ->where('books.is_active', true)
            ->orderBy('books.title')
            ->get();

        $relatedCloselyTitles = $book->relatedBooks()
            ->whereIn('book_relationships.relationship_type', [
                BookRelationship::TYPE_SUPPORTING,
                BookRelationship::TYPE_SAME_LANGUAGE,
            ])
            ->with($relatedBookEagerLoads)
            ->where('books.is_active', true)
            ->orderBy('books.title')
            ->limit(20)
            ->get();

        $relatedByCollection = $book->collection_id
            ? Book::where('collection_id', $book->collection_id)
                ->where('id', '!=', $book->id)
                ->where('is_active', true)
                ->with(['files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true)])
                ->get()
            : collect();

        $relatedByLanguage = $book->languages->isNotEmpty()
            ? Book::whereHas('languages', fn($q) => $q->whereIn('languages.id', $book->languages->pluck('id')))
                ->where('id', '!=', $book->id)
                ->where('is_active', true)
                ->with(['files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true)])
                ->get()
            : collect();

        $relatedByCreator = $book->creators->isNotEmpty()
            ? Book::whereHas('creators', fn($q) => $q->whereIn('creators.id', $book->creators->pluck('id')))
                ->where('id', '!=', $book->id)
                ->where('is_active', true)
                ->with(['files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true)])
                ->get()
            : collect();

        $hasAdvancedRelatedBookSections = $relatedOtherEditions->isNotEmpty()
            || $relatedOtherLanguageVersions->isNotEmpty()
            || $relatedCloselyTitles->isNotEmpty();

        $hasLegacyRelatedBookSections = $relatedByCollection->isNotEmpty()
            || $relatedByLanguage->isNotEmpty()
            || $relatedByCreator->isNotEmpty();

        $hasRelatedBookSections = $hasAdvancedRelatedBookSections || $hasLegacyRelatedBookSections;

        return view('library.show', compact(
            'book',
            'averageRating',
            'totalRatings',
            'ratingDistribution',
            'userRating',
            'userAccessRequest',
            'userNotes',
            'relatedOtherEditions',
            'relatedOtherLanguageVersions',
            'relatedCloselyTitles',
            'relatedByCollection',
            'relatedByLanguage',
            'relatedByCreator',
            'hasAdvancedRelatedBookSections',
            'hasRelatedBookSections'
        ));
    }

    /**
     * Show PDF viewer page (canvas-based viewer for all access levels)
     */
    public function viewPdfViewer(Book $book, $fileId)
    {
        // Find the file
        $file = $book->files()->where('file_type', 'pdf')->findOrFail($fileId);

        // Check access level
        if ($book->access_level === 'unavailable') {
            abort(403, 'This book is not available for viewing. Please request access.');
        }

        // Check if file exists in storage (with Unicode normalization)
        $filePath = $file->file_path ? \Normalizer::normalize($file->file_path, \Normalizer::NFC) : null;
        if (!$filePath || !\Storage::disk('public')->exists($filePath)) {
            abort(404, 'PDF file not found');
        }

        // Always show canvas-based viewer (for both full and limited access)
        return view('library.pdf-viewer', compact('book', 'file'));
    }

    /**
     * Stream PDF file directly (used by PDF.js viewer and full access)
     * Note: Book page views are tracked in show() method, not here.
     * This only serves the PDF file for the viewer.
     */
    public function viewPdf(Book $book, $fileId)
    {
        // Find the file
        $file = $book->files()->where('file_type', 'pdf')->findOrFail($fileId);

        // Check access level
        if ($book->access_level === 'unavailable') {
            abort(403, 'This book is not available for viewing. Please request access.');
        }

        // Normalize Unicode path for filesystem compatibility
        $normalizedPath = $file->file_path ? \Normalizer::normalize($file->file_path, \Normalizer::NFC) : null;

        // Check if file exists in storage
        if (!$normalizedPath || !\Storage::disk('public')->exists($normalizedPath)) {
            abort(404, 'PDF file not found');
        }

        // Get file path (use normalized path)
        $filePath = storage_path('app/public/' . $normalizedPath);

        // Stream the PDF file for inline viewing with proper headers
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($file->filename ?? basename($normalizedPath)) . '"',
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Download a book file
     */
    public function download(Book $book, $fileId)
    {
        // Find the file
        $file = $book->files()->findOrFail($fileId);

        // Check access level
        if ($book->access_level === 'unavailable') {
            abort(403, 'This book is not available for download. Please request access.');
        }

        // Normalize Unicode path for filesystem compatibility
        $normalizedPath = $file->file_path ? \Normalizer::normalize($file->file_path, \Normalizer::NFC) : null;

        // Check if file exists in storage
        if (!$normalizedPath || !\Storage::disk('public')->exists($normalizedPath)) {
            abort(404, 'File not found');
        }

        // Track the download
        $this->analytics->trackBookDownload($book, request());

        // Return the file for download (use normalized path)
        return \Storage::disk('public')->download(
            $normalizedPath,
            $file->filename ?? basename($normalizedPath)
        );
    }

    /**
     * Submit a rating for a book
     */
    public function submitRating(Request $request, Book $book)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Update or create rating
        $book->ratings()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['rating' => $validated['rating']]
        );

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            // Recalculate rating statistics
            $averageRating = $book->ratings()->avg('rating') ?? 0;
            $totalRatings = $book->ratings()->count();
            $ratingDistribution = [];
            for ($i = 1; $i <= 5; $i++) {
                $ratingDistribution[$i] = $book->ratings()->where('rating', $i)->count();
            }

            return response()->json([
                'success' => true,
                'rating' => $validated['rating'],
                'averageRating' => round($averageRating, 1),
                'totalRatings' => $totalRatings,
                'ratingDistribution' => $ratingDistribution,
                'message' => 'Your rating has been saved!'
            ]);
        }

        return back()->with('success', 'Your rating has been saved!');
    }

    /**
     * Delete a user's rating for a book
     */
    public function deleteRating(Book $book)
    {
        // Find and delete the user's rating
        $rating = $book->ratings()->where('user_id', auth()->id())->first();

        if ($rating) {
            $rating->delete();

            // Recalculate rating statistics
            $averageRating = $book->ratings()->avg('rating') ?? 0;
            $totalRatings = $book->ratings()->count();
            $ratingDistribution = [];
            for ($i = 1; $i <= 5; $i++) {
                $ratingDistribution[$i] = $book->ratings()->where('rating', $i)->count();
            }

            return response()->json([
                'success' => true,
                'averageRating' => round($averageRating, 1),
                'totalRatings' => $totalRatings,
                'ratingDistribution' => $ratingDistribution,
                'message' => 'Your rating has been removed!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No rating found to delete'
        ], 404);
    }

    /**
     * Submit a review for a book
     */
    public function submitReview(Request $request, Book $book)
    {
        $validated = $request->validate([
            'review' => 'required|string|min:10|max:2000',
        ]);

        // Create review (requires approval)
        $book->reviews()->create([
            'user_id' => auth()->id(),
            'review' => $validated['review'],
            'is_approved' => false, // Reviews require admin approval
            'is_active' => true,
        ]);

        return back()->with('success', 'Your review has been submitted and is pending approval. Thank you!');
    }

    /**
     * Submit an access request for a book
     */
    public function requestAccess(Request $request, Book $book)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        // Check if user already has a pending or approved request
        $existingRequest = AccessRequest::where('book_id', $book->id)
            ->where(function($q) use ($validated) {
                $q->where('user_id', auth()->id())
                  ->orWhere('email', $validated['email']);
            })
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            if ($existingRequest->status === 'pending') {
                return back()->with('info', 'You already have a pending access request for this book. Please wait for our response.');
            } elseif ($existingRequest->status === 'approved') {
                return back()->with('info', 'Your access request has already been approved. Please check your email for instructions.');
            }
        }

        // Create access request
        AccessRequest::create([
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Your access request has been submitted. We will contact you via email shortly.');
    }
}
