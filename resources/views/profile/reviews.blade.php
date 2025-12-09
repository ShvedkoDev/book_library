@extends('layouts.library')

@section('title', 'My Reviews - Activity - FSM National Vernacular Language Arts (VLA) Curriculum')
@section('description', 'View all book reviews you have submitted in the FSM National Vernacular Language Arts (VLA) Curriculum')

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

    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .review-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        transition: box-shadow 0.3s;
    }

    .review-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 1rem;
    }

    .review-title {
        font-size: 1.1rem;
        font-weight: 600;
        flex: 1;
    }

    .review-title a {
        color: #007cba;
        text-decoration: none;
    }

    .review-title a:hover {
        text-decoration: underline;
    }

    .review-status-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .review-status-badges {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .review-actions {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-left: 0.5rem;
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
    .review-actions .action-btn,
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

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge.approved {
        background: #d4edda;
        color: #28a745;
    }

    .status-badge.pending {
        background: #fff3cd;
        color: #f39c12;
    }

    .review-text-box {
        background: #f9f9f9;
        border-left: 3px solid #007cba;
        padding: 1rem;
        border-radius: 4px;
        font-size: 0.95rem;
        color: #555;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .review-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        color: #999;
    }

    .review-meta i {
        margin-right: 0.25rem;
    }

    .review-time {
        color: #999;
        font-size: 0.875rem;
        margin-top: 0.5rem;
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

    .form-group textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: inherit;
        font-size: 0.875rem;
        transition: border-color 0.2s;
        resize: vertical;
        min-height: 100px;
    }

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
            <i class="fas fa-comment" style="color: #007cba;"></i> My reviews
        </h1>
        <p style="color: #666; margin-top: 0.5rem;">Your book reviews and feedback</p>
    </div>

    <div class="profile-container">
        @include('profile.partials.profile-nav')

        <div class="profile-main">
            @if($reviews->count() > 0)
                <div class="reviews-list">
            @foreach($reviews as $review)
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-title">
                            <a href="{{ route('library.show', $review->book->slug) }}">
                                {{ $review->book->title }}
                            </a>
                        </div>
                        <div class="review-status-actions">
                            <div class="review-status-badges">
                                @if($review->is_approved)
                                    <span class="status-badge approved">
                                        <i class="fas fa-check-circle"></i> Approved
                                    </span>
                                @else
                                    <span class="status-badge pending">
                                        <i class="fas fa-clock"></i> Pending Approval
                                    </span>
                                @endif
                            </div>
                            @if(!$review->is_approved)
                                <div class="review-actions">
                                    <button class="action-btn edit-btn" onclick="openEditReviewModal({{ $review->id }}, '{{ addslashes($review->review) }}')" title="Edit review">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('library.reviews.destroy', $review) }}" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete-btn" title="Delete review">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="review-text-box">
                        {{ $review->review }}
                    </div>

                    <div class="review-meta">
                        <span>
                            <i class="fal fa-calendar"></i> Submitted on {{ $review->created_at->format('F j, Y') }}
                        </span>
                        @if($review->is_approved && $review->approved_at)
                            <span>
                                <i class="fal fa-check"></i> Approved on {{ $review->approved_at->format('F j, Y') }}
                            </span>
                        @endif
                    </div>

                    <div class="review-time">
                        <i class="fal fa-clock"></i> {{ $review->created_at->diffForHumans() }}
                    </div>
                </div>
            @endforeach
                </div>

                @if($reviews->hasPages())
                    <div class="pagination-wrapper">
                        {{ $reviews->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="fal fa-comment"></i>
                    <h2>No reviews yet</h2>
                    <p>Share your thoughts about books you've read.</p>
                    <a href="{{ route('library.index') }}" class="btn">Browse Library</a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Review Modal -->
<div class="modal-overlay" id="editReviewOverlay" onclick="closeEditReviewModal()"></div>
<div class="modal" id="editReviewModal">
    <div class="modal-header">
        <h3><i class="fas fa-edit"></i> Edit Review</h3>
        <button class="modal-close" onclick="closeEditReviewModal()">&times;</button>
    </div>
    <form id="editReviewForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label for="edit_review">Review *</label>
                <textarea id="edit_review" name="review" required minlength="10" maxlength="2000" rows="4" placeholder="Share your thoughts about this book..."></textarea>
                <small>Reviews are moderated and will appear after approval.</small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditReviewModal()">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function openEditReviewModal(reviewId, reviewText) {
    // Set form action
    document.getElementById('editReviewForm').action = `/library/reviews/${reviewId}`;

    // Fill form field
    document.getElementById('edit_review').value = reviewText;

    // Show modal
    document.getElementById('editReviewOverlay').classList.add('active');
    document.getElementById('editReviewModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeEditReviewModal() {
    document.getElementById('editReviewOverlay').classList.remove('active');
    document.getElementById('editReviewModal').classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditReviewModal();
    }
});
</script>
@endpush

@endsection
