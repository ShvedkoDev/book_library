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

    /**
     * Get views for today (last 24 hours)
     */
    public function getViewsToday(): int
    {
        return BookView::where('created_at', '>=', now()->subDay())->count();
    }

    /**
     * Get downloads for today (last 24 hours)
     */
    public function getDownloadsToday(): int
    {
        return BookDownload::where('created_at', '>=', now()->subDay())->count();
    }

    /**
     * Get views for specified time period
     */
    public function getViews(int $days): int
    {
        return BookView::where('created_at', '>=', now()->subDays($days))->count();
    }

    /**
     * Get downloads for specified time period
     */
    public function getDownloads(int $days): int
    {
        return BookDownload::where('created_at', '>=', now()->subDays($days))->count();
    }

    /**
     * Get searches for specified time period
     */
    public function getSearches(int $days): int
    {
        return SearchQuery::where('created_at', '>=', now()->subDays($days))->count();
    }

    /**
     * Get unique books viewed for specified time period
     */
    public function getUniqueBooksViewed(int $days): int
    {
        return BookView::where('created_at', '>=', now()->subDays($days))
            ->distinct('book_id')
            ->count('book_id');
    }

    /**
     * Get unique users for specified time period (users who viewed, downloaded, or searched)
     */
    public function getUniqueUsers(int $days): int
    {
        $startDate = now()->subDays($days);

        $viewUserIds = BookView::where('created_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->pluck('user_id');

        $downloadUserIds = BookDownload::where('created_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->pluck('user_id');

        $searchUserIds = SearchQuery::where('created_at', '>=', $startDate)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->pluck('user_id');

        return $viewUserIds->merge($downloadUserIds)
            ->merge($searchUserIds)
            ->unique()
            ->count();
    }

    /**
     * Get daily unique user counts for chart
     */
    public function getDailyUniqueUsers(int $days = 30): array
    {
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $startOfDay = now()->subDays($i)->startOfDay();
            $endOfDay = now()->subDays($i)->endOfDay();

            $viewUserIds = BookView::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->pluck('user_id');

            $downloadUserIds = BookDownload::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->pluck('user_id');

            $searchUserIds = SearchQuery::whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->pluck('user_id');

            $uniqueCount = $viewUserIds->merge($downloadUserIds)
                ->merge($searchUserIds)
                ->unique()
                ->count();

            $data[$date] = $uniqueCount;
        }

        return $data;
    }
}
