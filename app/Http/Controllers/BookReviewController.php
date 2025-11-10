<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookReviewController extends Controller
{
    /**
     * Update an existing review (only if not approved yet).
     */
    public function update(Request $request, BookReview $review): RedirectResponse
    {
        // Ensure user can only update their own reviews
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure review is not approved yet
        if ($review->is_approved) {
            return redirect()->back()->with('error', 'Cannot edit an approved review.');
        }

        $validated = $request->validate([
            'review' => 'required|string|min:10|max:2000',
        ]);

        $review->update([
            'review' => $validated['review'],
        ]);

        return redirect()->back()->with('success', 'Review updated successfully!');
    }

    /**
     * Delete a review (only if not approved yet).
     */
    public function destroy(BookReview $review): RedirectResponse
    {
        // Ensure user can only delete their own reviews
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure review is not approved yet
        if ($review->is_approved) {
            return redirect()->back()->with('error', 'Cannot delete an approved review.');
        }

        $review->delete();

        return redirect()->back()->with('success', 'Review deleted successfully!');
    }
}
