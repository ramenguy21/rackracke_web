@php
    $stateKey = (string) $order->state;
    $cfg = $statusConfig[$stateKey] ?? ['label' => $stateKey, 'class' => ''];
    $isIssue = in_array($cfg['class'], ['cancelled', 'returned']);
@endphp

<div class="order-row sale-row">
    <a href="{{ route('seller.listings.show', $order->listing) }}" class="thumb" wire:navigate>
        @if ($order->listing->photos && count($order->listing->photos) > 0)
            <img src="{{ Storage::disk('s3')->url($order->listing->photos[0]) }}" alt="{{ $order->listing->title }}">
        @endif
    </a>

    <div class="meta">
        <a href="{{ route('seller.listings.show', $order->listing) }}" class="title title-link" wire:navigate>
            {{ $order->listing->title }}
        </a>
        <div class="sub">
            <span>{{ $order->created_at->diffForHumans() }}</span>
        </div>
        <div class="sale-status {{ $cfg['class'] }}">
            <span class="prep-dot" style="{{ in_array($cfg['class'], ['awaiting']) ? '' : 'display:none' }}"></span>
            {{ $cfg['label'] }}
        </div>
    </div>

    <div class="amount">
        <span class="v {{ $isIssue ? 'muted' : '' }}" style="{{ $isIssue ? 'text-decoration:line-through' : '' }}">
            {{ $rs($order->sellerNet()) }}
        </span>
        <span class="net">net · after {{ $order->take_rate_pct }}% fee</span>
        @if ($isIssue)
            <span class="net" style="color:#B72A2A">{{ $cfg['class'] === 'cancelled' ? 'cancelled · not charged' : 'refunded to buyer' }}</span>
        @endif
    </div>

    <div class="actions">
        <a href="{{ route('seller.listings.show', $order->listing) }}" class="row-view" title="View piece" wire:navigate>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>
        </a>
    </div>
</div>
