<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Display the user's activity dashboard
     */
    public function activity(Request $request): View
    {
        $user = $request->user();

        // Get activity counts
        $stats = [
            'ratings_count' => $user->ratings()->count(),
            'reviews_count' => $user->reviews()->count(),
            'downloads_count' => $user->downloads()->count(),
            'bookmarks_count' => $user->userBookmarks()->count(),
            'notes_count' => $user->bookNotes()->count(),
            'views_count' => $user->views()->count(),
        ];

        return view('profile.activity', compact('user', 'stats'));
    }

    /**
     * Display the user's ratings
     */
    public function ratings(Request $request): View
    {
        $user = $request->user();

        $ratings = $user->ratings()
            ->with('book:id,title,slug,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.ratings', compact('user', 'ratings'));
    }

    /**
     * Display the user's reviews
     */
    public function reviews(Request $request): View
    {
        $user = $request->user();

        $reviews = $user->reviews()
            ->with('book:id,title,slug,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.reviews', compact('user', 'reviews'));
    }

    /**
     * Display the user's downloads
     */
    public function downloads(Request $request): View
    {
        $user = $request->user();

        $downloads = $user->downloads()
            ->with('book:id,title,slug,access_level,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.downloads', compact('user', 'downloads'));
    }

    /**
     * Display the user's bookmarks
     */
    public function bookmarks(Request $request): View
    {
        $user = $request->user();

        $bookmarks = $user->userBookmarks()
            ->with('book:id,title,slug,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.bookmarks', compact('user', 'bookmarks'));
    }

    /**
     * Display the user's notes
     */
    public function notes(Request $request): View
    {
        $user = $request->user();

        $notes = $user->bookNotes()
            ->with('book:id,title,slug,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.notes', compact('user', 'notes'));
    }

    /**
     * Display complete activity timeline
     */
    public function timeline(Request $request): View
    {
        $user = $request->user();

        // Collect all activities with timestamps
        $activities = collect();

        // Add ratings
        $user->ratings()
            ->with('book:id,title,slug')
            ->get()
            ->each(function ($rating) use ($activities) {
                $activities->push([
                    'type' => 'rating',
                    'icon' => 'star',
                    'color' => 'yellow',
                    'date' => $rating->created_at,
                    'book' => $rating->book,
                    'data' => $rating,
                ]);
            });

        // Add reviews
        $user->reviews()
            ->with('book:id,title,slug')
            ->get()
            ->each(function ($review) use ($activities) {
                $activities->push([
                    'type' => 'review',
                    'icon' => 'chat',
                    'color' => 'blue',
                    'date' => $review->created_at,
                    'book' => $review->book,
                    'data' => $review,
                ]);
            });

        // Add downloads
        $user->downloads()
            ->with('book:id,title,slug')
            ->get()
            ->each(function ($download) use ($activities) {
                $activities->push([
                    'type' => 'download',
                    'icon' => 'download',
                    'color' => 'green',
                    'date' => $download->created_at,
                    'book' => $download->book,
                    'data' => $download,
                ]);
            });

        // Add bookmarks
        $user->userBookmarks()
            ->with('book:id,title,slug')
            ->get()
            ->each(function ($bookmark) use ($activities) {
                $activities->push([
                    'type' => 'bookmark',
                    'icon' => 'bookmark',
                    'color' => 'purple',
                    'date' => $bookmark->created_at,
                    'book' => $bookmark->book,
                    'data' => $bookmark,
                ]);
            });

        // Add notes
        $user->bookNotes()
            ->with('book:id,title,slug')
            ->get()
            ->each(function ($note) use ($activities) {
                $activities->push([
                    'type' => 'note',
                    'icon' => 'pencil',
                    'color' => 'orange',
                    'date' => $note->created_at,
                    'book' => $note->book,
                    'data' => $note,
                ]);
            });

        // Sort by date descending and paginate
        $timeline = $activities->sortByDesc('date')->values();

        // Manual pagination
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $paginatedItems = $timeline->slice($offset, $perPage)->values();
        $total = $timeline->count();

        return view('profile.timeline', compact('user', 'paginatedItems', 'total', 'currentPage', 'perPage'));
    }

    /**
     * Admin view: Display any user's activity (admin only)
     */
    public function viewUserActivity(User $user): View
    {
        // Check if current user is admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Get activity counts
        $stats = [
            'ratings_count' => $user->ratings()->count(),
            'reviews_count' => $user->reviews()->count(),
            'downloads_count' => $user->downloads()->count(),
            'bookmarks_count' => $user->userBookmarks()->count(),
            'notes_count' => $user->bookNotes()->count(),
            'views_count' => $user->views()->count(),
        ];

        return view('profile.admin-view', compact('user', 'stats'));
    }

    /**
     * Admin view: Display user's ratings (admin only)
     */
    public function viewUserRatings(User $user): View
    {
        // Check if current user is admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $ratings = $user->ratings()
            ->with('book:id,title,slug,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.admin-ratings', compact('user', 'ratings'));
    }

    /**
     * Admin view: Display user's reviews (admin only)
     */
    public function viewUserReviews(User $user): View
    {
        // Check if current user is admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $reviews = $user->reviews()
            ->with('book:id,title,slug,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.admin-reviews', compact('user', 'reviews'));
    }

    /**
     * Admin view: Display user's downloads (admin only)
     */
    public function viewUserDownloads(User $user): View
    {
        // Check if current user is admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $downloads = $user->downloads()
            ->with('book:id,title,slug,access_level,publication_year')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('profile.admin-downloads', compact('user', 'downloads'));
    }
}
