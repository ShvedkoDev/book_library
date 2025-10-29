<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookNoteController extends Controller
{
    /**
     * Display notes for a specific book (for authenticated user).
     */
    public function index(Book $book): View
    {
        $notes = $book->getNotesForUser(Auth::id());

        return view('library.notes.index', compact('book', 'notes'));
    }

    /**
     * Store a new note.
     */
    public function store(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|min:1|max:5000',
            'page_number' => 'nullable|integer|min:1',
        ]);

        BookNote::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'note' => $validated['note'],
            'page_number' => $validated['page_number'] ?? null,
            'is_private' => true,
        ]);

        return redirect()->back()->with('success', 'Note saved successfully!');
    }

    /**
     * Update an existing note.
     */
    public function update(Request $request, BookNote $note): RedirectResponse
    {
        // Ensure user can only update their own notes
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'note' => 'required|string|min:1|max:5000',
            'page_number' => 'nullable|integer|min:1',
        ]);

        $note->update([
            'note' => $validated['note'],
            'page_number' => $validated['page_number'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Note updated successfully!');
    }

    /**
     * Delete a note.
     */
    public function destroy(BookNote $note): RedirectResponse
    {
        // Ensure user can only delete their own notes
        if ($note->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $note->delete();

        return redirect()->back()->with('success', 'Note deleted successfully!');
    }
}
