<!-- Add New Note Form -->
<div class="add-note-form">
    <h3 class="section-title text-left">Add a note</h3>
    <form action="{{ route('library.notes.store', $book->id) }}" method="POST">
        @csrf
        <div class="note-field-margin">
            <label for="page_number" class="note-field-label">Page number <span style="font-weight: normal">(optional)</span></label>
            <input
                    type="number"
                    name="page_number"
                    id="page_number"
                    min="1"
                    placeholder="e.g., 42"
                    class="note-field-page-input">
        </div>
        <div class="note-field-margin">
            <label for="note" class="note-field-label">Note *</label>
            <textarea
                name="note"
                id="note"
                rows="4"
                placeholder="Write your thoughts, observations, or reminders about this book..."
                class="note-field-input"
                required
                minlength="1"
                maxlength="5000"></textarea>
            <small class="note-field-small">Maximum 5,000 characters. Your notes are private.</small>
        </div>

        <button type="submit" class="btn-add-note">
            <i class="fal fa-plus"></i> Add note
        </button>
    </form>
</div>

<!-- Existing Notes -->
<div class="existing-notes">
    <h3 class="section-title text-left">
        Your notes
        @if($userNotes->isEmpty())
            <span>(none yet)</span>
        @endif
    </h3>
    @forelse($userNotes as $note)
        <div class="note-item">
            <div class="note-item-header">
                <div>
                    @if($note->page_number)
                        <span class="note-page-badge">
                            <i class="fal fa-book-open"></i> Page {{ $note->page_number }}
                        </span>
                    @endif
                    <div class="note-date">
                        <i class="fal fa-clock"></i> {{ $note->created_at->format('M d, Y') }}
                        @if($note->created_at != $note->updated_at)
                            (edited {{ $note->updated_at->diffForHumans() }})
                        @endif
                    </div>
                </div>
                <div class="note-actions">
                    <button onclick="editNote({{ $note->id }})" class="btn-note-edit">
                        <i class="fal fa-edit"></i> Edit
                    </button>
                    <form action="{{ route('library.notes.destroy', $note->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this note?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-note-delete">
                            <i class="fal fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>

            <div id="note-content-{{ $note->id }}" class="note-content">{{ $note->note }}</div>

            <!-- Edit Form (Hidden by default) -->
            <div id="note-edit-form-{{ $note->id }}" class="note-edit-form">
                <form action="{{ route('library.notes.update', $note->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <textarea
                        name="note"
                        rows="4"
                        required
                        minlength="1"
                        maxlength="5000">{{ $note->note }}</textarea>
                    <div class="note-field-margin">
                        <input
                            type="number"
                            name="page_number"
                            value="{{ $note->page_number }}"
                            min="1"
                            placeholder="Page number (optional)"
                            class="note-field-page-input">
                    </div>
                    <div class="note-edit-actions">
                        <button type="submit" class="btn-note-save">
                            <i class="fal fa-check"></i> Save
                        </button>
                        <button type="button" onclick="cancelEdit({{ $note->id }})" class="btn-note-cancel">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="notes-empty-state">
            <p>You haven't added any notes for this book yet. Use the form above to add your first note!</p>
        </div>
    @endforelse
</div>
