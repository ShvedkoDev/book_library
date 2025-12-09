@extends('layouts.library')

@section('title', 'My Notes - Activity - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', 'View all your personal notes on books in the FSM National Vernacular Language Arts (VLA) Curriculum')

@push('styles')
<style>
    .activity-header {
        padding: 2rem 0;
        border-bottom: 1px solid #e0e0e0;
        margin-bottom: 2rem;
    }

    .activity-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.5rem 0;
    }

    .activity-header .breadcrumb {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .activity-header .breadcrumb a {
        color: #007cba;
        text-decoration: none;
    }

    .activity-header .breadcrumb a:hover {
        text-decoration: underline;
    }

    .notes-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .note-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        transition: box-shadow 0.3s;
    }

    .note-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .note-title-section {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .note-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .note-title a {
        color: #007cba;
        text-decoration: none;
    }

    .note-title a:hover {
        text-decoration: underline;
    }

    .page-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        background: #e6f3f9;
        color: #007cba;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .note-privacy-section {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .privacy-icon {
        color: #999;
        font-size: 1rem;
        margin-left: 0.5rem;
    }

    .note-actions {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-left: auto;
    }

    .delete-form {
        display: inline-flex;
        margin: 0;
    }

    .action-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        margin: 0 !important;
        border-radius: 4px;
        transition: all 0.2s;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
    }

    /* Override WordPress global input + button margin */
    .note-actions .action-btn,
    .delete-form .action-btn {
        margin-top: 0 !important;
    }

    .action-btn i {
        line-height: 1;
    }

    .action-btn.edit-btn {
        color: #007cba;
    }

    .action-btn.edit-btn:hover {
        background: #e6f3f9;
    }

    .action-btn.delete-btn {
        color: #dc3545;
    }

    .action-btn.delete-btn:hover {
        background: #ffe6e6;
    }

    .note-text-box {
        background: #fffbf0;
        border-left: 3px solid #fd7e14;
        padding: 1rem;
        border-radius: 4px;
        font-size: 0.95rem;
        color: #555;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .note-meta {
        font-size: 0.875rem;
        color: #999;
    }

    .note-meta i {
        margin-right: 0.25rem;
    }

    .note-updated {
        color: #666;
        margin-left: 0.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .empty-state i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 1rem;
    }

    .empty-state h2 {
        font-size: 1.5rem;
        color: #666;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        font-size: 1rem;
        color: #999;
        margin-bottom: 2rem;
    }

    .empty-state .btn {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        background: #007cba;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 600;
        transition: background 0.3s;
    }

    .empty-state .btn:hover {
        background: #005a87;
    }

    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
        animation: fadeIn 0.2s;
    }

    .modal-overlay.active {
        display: block;
    }

    .modal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 6px;
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        max-width: 500px;
        width: 90%;
        max-height: 85vh;
        overflow-y: auto;
        animation: slideIn 0.2s;
    }

    .modal.active {
        display: block;
    }

    .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #333;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        color: #999;
        cursor: pointer;
        padding: 0;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        line-height: 1;
    }

    .modal-close:hover {
        background: #f0f0f0;
        color: #333;
    }

    .modal-body {
        padding: 1.25rem;
    }

    .modal-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    /* Override WordPress global styles for modal buttons */
    .modal-footer .btn {
        margin-top: 0 !important;
    }

    .form-group {
        margin-bottom: 0.75rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 600;
        color: #333;
        font-size: 0.875rem;
    }

    .form-group textarea,
    .form-group input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: inherit;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #007cba;
        box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
    }

    .form-group small {
        display: block;
        margin-top: 0.25rem;
        color: #666;
        font-size: 0.75rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: #007cba;
        color: white;
    }

    .btn-primary:hover {
        background: #005a8a;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translate(-50%, -45%);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="activity-header">
        <h1>
            <i class="fas fa-sticky-note" style="color: #fd7e14;"></i> My notes
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Your personal notes and annotations</p>
    </div>

    <div class="profile-container">
        @include('profile.partials.profile-nav')

        <div class="profile-main">
            @if($notes->count() > 0)
                <div class="notes-list">
            @foreach($notes as $note)
                <div class="note-card">
                    <div class="note-header">
                        <div class="note-title-section">
                            <div class="note-title">
                                <a href="{{ route('library.show', $note->book->slug) }}">
                                    {{ $note->book->title }}
                                </a>
                            </div>

                            @if($note->page_number)
                                <span class="page-badge">
                                    <i class="fas fa-file-alt"></i> Page {{ $note->page_number }}
                                </span>
                            @endif
                        </div>

                        <div class="note-actions">
                            <button class="action-btn edit-btn" onclick="openEditNoteModal({{ $note->id }}, '{{ addslashes($note->note) }}', {{ $note->page_number ?? 'null' }})" title="Edit note">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('library.notes.destroy', $note) }}" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this note?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete-btn" title="Delete note">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @if($note->is_private)
                                <i class="fas fa-lock privacy-icon" title="Private note"></i>
                            @else
                                <i class="fas fa-unlock privacy-icon" title="Public note"></i>
                            @endif
                        </div>
                    </div>

                    <div class="note-text-box">
                        {{ $note->note }}
                    </div>

                    <div class="note-meta">
                        <i class="fal fa-clock"></i> Created {{ $note->created_at->diffForHumans() }} ({{ $note->created_at->format('F j, Y \a\t g:i A') }})
                        @if($note->updated_at != $note->created_at)
                            <span class="note-updated">
                                · Updated {{ $note->updated_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
                </div>

                @if($notes->hasPages())
                    <div class="pagination-wrapper">
                        {{ $notes->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="fal fa-sticky-note"></i>
                    <h2>No notes yet</h2>
                    <p>Create notes while reading to remember important details.</p>
                    <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Note Modal -->
<div class="modal-overlay" id="editNoteOverlay" onclick="closeEditNoteModal()"></div>
<div class="modal" id="editNoteModal">
    <div class="modal-header">
        <h3><i class="fas fa-edit"></i> Edit Note</h3>
        <button class="modal-close" onclick="closeEditNoteModal()">&times;</button>
    </div>
    <form id="editNoteForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label for="edit_note">Note *</label>
                <textarea id="edit_note" name="note" required minlength="1" maxlength="5000" rows="4" placeholder="Write your thoughts, observations, or reminders about this book..."></textarea>
                <small>Maximum 5,000 characters. Your notes are private.</small>
            </div>
            <div class="form-group">
                <label for="edit_page_number">Page Number (optional)</label>
                <input type="number" id="edit_page_number" name="page_number" min="1" placeholder="e.g., 42">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditNoteModal()">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function openEditNoteModal(noteId, noteText, pageNumber) {
    // Set form action
    document.getElementById('editNoteForm').action = `/notes/${noteId}`;

    // Fill form fields
    document.getElementById('edit_note').value = noteText;
    document.getElementById('edit_page_number').value = pageNumber || '';

    // Show modal
    document.getElementById('editNoteOverlay').classList.add('active');
    document.getElementById('editNoteModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeEditNoteModal() {
    document.getElementById('editNoteOverlay').classList.remove('active');
    document.getElementById('editNoteModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditNoteModal();
    }
});
</script>
@endpush

@endsection
