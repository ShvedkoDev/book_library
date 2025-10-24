<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookView;
use App\Models\BookDownload;
use App\Models\SearchQuery;
use App\Models\FilterAnalytic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AnalyticsService
{
    /**
     * Track a book view
     */
    public function trackBookView(Book $book, Request $request): void
    {
        // Create view record
        BookView::create([
            'book_id' => $book->id,
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Increment book view counter
        $book->increment('view_count');
    }

    /**
     * Track a file download
     */
    public function trackBookDownload(Book $book, Request $request): void
    {
        // Create download record
        BookDownload::create([
            'book_id' => $book->id,
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Increment book download counter
        $book->increment('download_count');
    }

    /**
     * Track a search query
     */
    public function trackSearch(string $query, int $resultsCount, Request $request): void
    {
        // Only track non-empty queries
        if (empty(trim($query))) {
            return;
        }

        SearchQuery::create([
            'query' => trim($query),
            'results_count' => $resultsCount,
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Track filter usage
     */
    public function trackFilter(string $filterType, string $filterValue, ?string $filterSlug, Request $request): void
    {
        FilterAnalytic::create([
            'filter_type' => $filterType,
            'filter_value' => $filterValue,
            'filter_slug' => $filterSlug,
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Track multiple filters at once
     */
    public function trackFilters(array $filters, Request $request): void
    {
        foreach ($filters as $filterType => $filterValues) {
            if (is_array($filterValues) && !empty($filterValues)) {
                foreach ($filterValues as $filterValue) {
                    $this->trackFilter(
                        $filterType,
                        $filterValue,
                        $filterValue, // Using value as slug for now
                        $request
                    );
                }
            }
        }
    }

    /**
     * Get popular books by views
     */
    public function getPopularBooksByViews(int $limit = 10, int $days = 30)
    {
        return Book::active()
            ->where('view_count', '>', 0)
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get popular books by downloads
     */
    public function getPopularBooksByDownloads(int $limit = 10, int $days = 30)
    {
        return Book::active()
            ->where('download_count', '>', 0)
            ->orderBy('download_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently viewed books
     */
    public function getRecentlyViewedBooks(int $limit = 10)
    {
        return Book::active()
            ->join('book_views', 'books.id', '=', 'book_views.book_id')
            ->select('books.*')
            ->distinct()
            ->orderBy('book_views.created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get analytics dashboard data
     */
    public function getDashboardStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_views' => BookView::where('created_at', '>=', $startDate)->count(),
            'total_downloads' => BookDownload::where('created_at', '>=', $startDate)->count(),
            'total_searches' => SearchQuery::where('created_at', '>=', $startDate)->count(),
            'unique_books_viewed' => BookView::where('created_at', '>=', $startDate)
                ->distinct('book_id')
                ->count('book_id'),
            'popular_queries' => SearchQuery::getPopularQueries(10, $days),
            'zero_result_queries' => SearchQuery::getZeroResultQueries(10, $days),
            'popular_filters' => FilterAnalytic::getPopularFilters(null, 10, $days),
            'filter_stats' => FilterAnalytic::getFilterStats($days),
        ];
    }
}
