<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\UserBookmark;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    /**
     * Display the user's bookmarks.
     */
    public function index(): View
    {
        $bookmarks = Auth::user()->userBookmarks()
            ->with(['book.primaryThumbnail', 'book.authors', 'book.languages'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('bookmarks.index', compact('bookmarks'));
    }

    /**
     * Toggle bookmark for a book (add or remove).
     */
    public function toggle(Request $request, Book $book): RedirectResponse|JsonResponse
    {
        $result = UserBookmark::toggle(Auth::user(), $book);

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'bookmarked' => $result['bookmarked'],
                'message' => $result['message']
            ]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Remove a bookmark.
     */
    public function destroy(UserBookmark $bookmark): RedirectResponse
    {
        // Ensure user can only delete their own bookmarks
        if ($bookmark->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $bookmark->delete();

        return redirect()->back()->with('success', 'Bookmark removed from your collection');
    }
}
