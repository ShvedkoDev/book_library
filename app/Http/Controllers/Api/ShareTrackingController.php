<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookShare;
use Illuminate\Http\Request;

class ShareTrackingController extends Controller
{
    /**
     * Track a book share action.
     */
    public function trackShare(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'share_method' => 'required|in:email,facebook,twitter,whatsapp,clipboard',
            'url' => 'nullable|string',
        ]);

        try {
            BookShare::create([
                'book_id' => $validated['book_id'],
                'user_id' => auth()->id(), // null if not authenticated
                'share_method' => $validated['share_method'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'shared_url' => $validated['url'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Share tracked successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track share',
            ], 500);
        }
    }
}
