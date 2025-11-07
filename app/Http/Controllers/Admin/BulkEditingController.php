<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BulkEditingController extends Controller
{
    /**
     * Get paginated books for bulk editing
     */
    public function index(Request $request)
    {
        $query = Book::with(['publisher', 'collection', 'languages', 'creators']);

        // Apply filters if provided
        if ($request->has('title') && $request->title) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('publisher_id') && $request->publisher_id) {
            $query->where('publisher_id', $request->publisher_id);
        }

        if ($request->has('collection_id') && $request->collection_id) {
            $query->where('collection_id', $request->collection_id);
        }

        if ($request->has('access_level') && $request->access_level) {
            $query->where('access_level', $request->access_level);
        }

        // Sort by ID by default
        $query->orderBy('id', 'asc');

        // Pagination
        $perPage = $request->get('size', 50); // Default 50 per page
        $page = $request->get('page', 1);

        $books = $query->paginate($perPage, ['*'], 'page', $page);

        // Format data for Tabulator
        return response()->json([
            'last_page' => $books->lastPage(),
            'data' => $books->items(),
        ]);
    }

    /**
     * Bulk update books
     */
    public function bulkUpdate(Request $request)
    {
        $changes = $request->input('changes', []);

        if (empty($changes)) {
            return response()->json([
                'success' => false,
                'message' => 'No changes provided',
            ], 400);
        }

        // Validate changes
        $validated = $request->validate([
            'changes' => 'required|array',
            'changes.*.id' => 'required|exists:books,id',
            'changes.*.title' => 'sometimes|string|max:500',
            'changes.*.subtitle' => 'sometimes|nullable|string|max:500',
            'changes.*.translated_title' => 'sometimes|nullable|string|max:500',
            'changes.*.publication_year' => 'sometimes|nullable|integer|min:1900|max:' . date('Y'),
            'changes.*.pages' => 'sometimes|nullable|integer|min:1',
            'changes.*.publisher_id' => 'sometimes|nullable|exists:publishers,id',
            'changes.*.collection_id' => 'sometimes|nullable|exists:collections,id',
            'changes.*.access_level' => 'sometimes|in:full,limited,unavailable',
            'changes.*.physical_type' => 'sometimes|nullable|in:book,journal,magazine,workbook,poster,other',
            'changes.*.is_featured' => 'sometimes|boolean',
            'changes.*.is_active' => 'sometimes|boolean',
        ]);

        \DB::beginTransaction();
        try {
            $updatedCount = 0;

            foreach ($validated['changes'] as $change) {
                $book = Book::find($change['id']);
                if ($book) {
                    // Remove id from update data
                    unset($change['id']);
                    $book->update($change);
                    $updatedCount++;
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$updatedCount} book(s)",
                'count' => $updatedCount,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error updating books: ' . $e->getMessage(),
            ], 500);
        }
    }
}
