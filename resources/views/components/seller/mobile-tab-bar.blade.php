@props(['active' => 'Rack'])

<nav class="mobile-tabbar" aria-label="Primary">
    <a href="{{ route('seller.dashboard') }}"
       class="mtb-tab {{ $active === 'Rack' ? 'on' : '' }}"
       aria-label="Rack" wire:navigate>
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="{{ $active === 'Rack' ? '2.4' : '1.9' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Rack</span>
    </a>

    <a href="{{ route('seller.sales') }}"
       class="mtb-tab {{ $active === 'Sales' ? 'on' : '' }}"
       aria-label="Sales" wire:navigate>
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="{{ $active === 'Sales' ? '2.4' : '1.9' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        <span>Sales</span>
    </a>

    <a href="{{ route('seller.listings.create') }}" class="mtb-add" aria-label="New listing" wire:navigate>
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    </a>

    <a href="{{ route('seller.reviews') }}"
       class="mtb-tab {{ $active === 'Reviews' ? 'on' : '' }}"
       aria-label="Reviews" wire:navigate>
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="{{ $active === 'Reviews' ? '2.4' : '1.9' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        <span>Reviews</span>
    </a>

    <a href="{{ route('seller.wallet') }}"
       class="mtb-tab {{ $active === 'Wallet' ? 'on' : '' }}"
       aria-label="Wallet" wire:navigate>
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="{{ $active === 'Wallet' ? '2.4' : '1.9' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        <span>Wallet</span>
    </a>
</nav>
