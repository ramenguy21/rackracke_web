<div style="min-height:100vh;background:var(--paper);padding-bottom:120px">
  <div class="shell-wide page-enter">

    <div class="dash-hello">
      <div class="hello-left">
        <span class="eyebrow">Reviews · synced from buyers</span>
        @if ($totalReviews > 0)
          <h1>{{ number_format($avgRating, 1) }} <span class="it">/ 5</span></h1>
          <p class="muted" style="font-size:16px;margin:8px 0 0">{{ $totalReviews }} {{ $totalReviews === 1 ? 'review' : 'reviews' }} from buyers.</p>
        @else
          <h1>No <span class="it">reviews</span> yet.</h1>
          <p class="muted" style="font-size:16px;margin:8px 0 0">Reviews from buyers appear here as they come in.</p>
        @endif
      </div>
    </div>

    @if ($reviews->isEmpty())
      <div class="orders-empty" style="margin-top:var(--s-3)">
        <div class="ico" style="background:var(--paper-2)">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <h3>No reviews yet</h3>
        <p>When buyers rate a purchase from your shop, their review appears here.</p>
      </div>
    @else
      <div class="reviews-list reviews-list-full">
        @foreach ($reviews as $review)
          <div class="review-row">
            <div class="head">
              <span class="stars">
                @for ($i = 1; $i <= 5; $i++)
                  <span class="{{ $i <= $review->rating ? '' : 'muted' }}">★</span>
                @endfor
              </span>
              <span class="by">{{ $review->buyer_name }}</span>
              <span class="at">{{ $review->created_at->diffForHumans() }}</span>
            </div>
            <p>{{ $review->body }}</p>
          </div>
        @endforeach
      </div>
    @endif

  </div>
</div>
