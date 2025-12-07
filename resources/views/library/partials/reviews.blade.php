<!-- Rating Histogram -->
<div class="rating-histogram">
    <h3 class="section-title text-left">Rating distribution</h3>
    @if($totalRatings > 0)
        <div class="rating-center">
            <div class="rating-score-display">
                <div class="rating-score-number">{{ number_format($averageRating, 1) }}</div>
                <div class="stars rating-score-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="star {{ $i <= round($averageRating) ? '' : 'empty' }}">★</span>
                    @endfor
                </div>
                <div class="rating-score-count">{{ $totalRatings }} {{ Str::plural('rating', $totalRatings) }}</div>
            </div>
            <div class="rating-bars">
                @foreach([5, 4, 3, 2, 1] as $rating)
                    @php
                        $count = $ratingDistribution[$rating];
                        $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                    @endphp
                    <div class="rating-bar-row">
                        <span class="rating-bar-label">{{ $rating }} stars</span>
                        <div class="rating-bar-container">
                            <div class="rating-bar-fill" style="width: {{ $percentage }}%;"></div>
                        </div>
                        <span class="rating-bar-count">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="rating-empty-state">No ratings yet. Be the first to rate this book!</p>
    @endif
</div>

<!-- User Rating Form -->
@auth
    <div class="user-rating-form">
        <h3 class="section-title text-left">Rate this book</h3>
        <form action="{{ route('library.rate', $book->id) }}" method="POST" class="star-rating-form" id="detailed-rating-form">
            @csrf
            <div class="star-rating">
                @for($i = 1; $i <= 5; $i++)
                    <label>
                        <input type="radio" name="rating" value="{{ $i }}" style="display: none;"
                            {{ $userRating && $userRating->rating == $i ? 'checked' : '' }}>
                        <span class="rating-star" data-rating="{{ $i }}"
                            style="color: {{ $userRating && $i <= $userRating->rating ? '#ffc107' : '#ddd' }}; transition: color 0.2s;">★</span>
                    </label>
                @endfor
            </div>
            @if($userRating)
                <span class="rating-text" id="rating-text">Your rating: {{ $userRating->rating }}/5</span>
            @else
                <span class="rating-text" id="rating-text">Click to rate</span>
            @endif
        </form>
    </div>
@else
    <div class="review-guest-message guest-message">
        <p>Please <a href="{{ route('login', ['redirect' => url()->current()]) }}">log in</a> to rate this book.</p>
    </div>
@endauth

<!-- User Review Form -->
@auth
    <div class="user-review-form">
        <h3 class="section-title text-left">Write a review</h3>
        <form action="{{ route('library.review', $book->id) }}" method="POST">
            @csrf
            <textarea name="review" rows="5" placeholder="Share your thoughts about this book..."
                class="review-form-field"
                required minlength="10" maxlength="2000"></textarea>
            <div class="review-form-footer">
                <span class="review-form-note">Reviews are moderated and will appear after approval.</span>
                <button type="submit" class="btn-submit">
                    Submit review
                </button>
            </div>
        </form>
    </div>
@else
    <div class="review-guest-message guest-message">
        <p>Please <a href="{{ route('login', ['redirect' => url()->current()]) }}">log in</a> to write a review.</p>
    </div>
@endauth

<!-- Existing Reviews -->
<div class="existing-reviews">
    <h3 class="section-title text-left">User reviews ({{ $book->reviews->count() }})</h3>
    @forelse($book->reviews as $review)
        <div class="review-item">
            <div class="review-header">
                <div>
                    <span class="review-author">{{ $review->user->name }}</span>
                    @php
                        $reviewUserRating = $book->ratings()->where('user_id', $review->user_id)->first();
                    @endphp
                    @if($reviewUserRating)
                        <div class="stars review-rating">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="star {{ $i <= $reviewUserRating->rating ? '' : 'empty' }}">★</span>
                            @endfor
                        </div>
                    @endif
                </div>
                <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
            </div>
            <p class="review-text">{{ $review->review }}</p>
        </div>
    @empty
        <div class="review-item guest-message">
            <p>No reviews yet. Be the first to review this book!</p>
        </div>
    @endforelse
</div>
