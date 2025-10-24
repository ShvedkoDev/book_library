<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Language;
use App\Models\ClassificationType;
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

        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('creators', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('publisher', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('collection', fn($q) => $q->where('title', 'like', "%{$search}%"));
            });
        }

        // Apply filters
        // Subject filter (Purpose classifications)
        if (!empty($filters['subjects'])) {
            $query->whereHas('purposeClassifications', fn($q) =>
                $q->whereIn('classification_values.id', $filters['subjects'])
            );
        }

        // Grade level filter (Learner level classifications)
        if (!empty($filters['grades'])) {
            $query->whereHas('learnerLevelClassifications', fn($q) =>
                $q->whereIn('classification_values.id', $filters['grades'])
            );
        }

        // Resource type filter (Type classifications)
        if (!empty($filters['types'])) {
            $query->whereHas('typeClassifications', fn($q) =>
                $q->whereIn('classification_values.id', $filters['types'])
            );
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

        // Get filter options for sidebar
        $availableLanguages = Language::whereHas('books', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        // Get classification types with values (skip for now if no books have them)
        $availableSubjects = ClassificationType::where('slug', 'purpose')
            ->with('classificationValues')
            ->get();

        $availableGrades = ClassificationType::where('slug', 'learner-level')
            ->with('classificationValues')
            ->get();

        $availableTypes = ClassificationType::where('slug', 'type')
            ->with('classificationValues')
            ->get();

        return view('library.index', compact(
            'books',
            'search',
            'filters',
            'perPage',
            'sortBy',
            'sortDirection',
            'availableLanguages',
            'availableSubjects',
            'availableGrades',
            'availableTypes'
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
        ])
        ->where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

        // Track book view
        $this->analytics->trackBookView($book, request());

        // Get related books
        $relatedByCollection = collect();
        if ($book->collection_id) {
            $relatedByCollection = Book::where('collection_id', $book->collection_id)
                ->where('id', '!=', $book->id)
                ->where('is_active', true)
                ->with(['files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true)])
                ->limit(6)
                ->get();
        }

        $relatedByLanguage = collect();
        if ($book->languages->isNotEmpty()) {
            $languageIds = $book->languages->pluck('id');
            $relatedByLanguage = Book::whereHas('languages', fn($q) => $q->whereIn('languages.id', $languageIds))
                ->where('id', '!=', $book->id)
                ->where('is_active', true)
                ->with(['files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true)])
                ->limit(6)
                ->get();
        }

        $relatedByCreator = collect();
        if ($book->creators->isNotEmpty()) {
            $creatorIds = $book->creators->pluck('id');
            $relatedByCreator = Book::whereHas('creators', fn($q) => $q->whereIn('creators.id', $creatorIds))
                ->where('id', '!=', $book->id)
                ->where('is_active', true)
                ->with(['files' => fn($q) => $q->where('file_type', 'thumbnail')->where('is_primary', true)])
                ->limit(6)
                ->get();
        }

        return view('library.show', compact(
            'book',
            'relatedByCollection',
            'relatedByLanguage',
            'relatedByCreator'
        ));
    }
}
