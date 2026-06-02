@php
  $rs = fn(int $n) => 'Rs. ' . number_format($n);
  $conditionLabels = [
    'new_with_tags' => 'New with tags',
    'excellent'     => 'Excellent',
    'good'          => 'Good',
    'fair'          => 'Fair',
  ];
  $statusClass = [
    'live'           => 'chip-blue',
    'sold'           => 'chip-yellow',
    'pending_review' => '',
    'draft'          => '',
    'withdrawn'      => '',
  ][$listing->status] ?? '';
  $statusLabel = [
    'live'           => '● Live',
    'sold'           => 'Sold',
    'pending_review' => 'Pending review',
    'draft'          => 'Draft',
    'withdrawn'      => 'Withdrawn',
  ][$listing->status] ?? $listing->status;
@endphp

<div class="shell" x-data="{ activePhoto: 0, showOverflow: false, showDelete: $wire.entangle('showDeleteConfirm') }">
  <div class="pd2 page-enter">

    {{-- Back + overflow --}}
    <div class="backbar">
      <a href="{{ route('seller.dashboard') }}" class="btn btn-ghost btn-sm" wire:navigate>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to rack
      </a>
      <div class="actions" style="position:relative">
        <a href="{{ route('seller.listings.edit', $listing) }}" class="btn btn-soft btn-sm" wire:navigate>Edit</a>
        <button class="btn btn-ghost btn-sm" @click="showOverflow = !showOverflow">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
        </button>

        {{-- Overflow menu --}}
        <div x-show="showOverflow" x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="overflow-menu" @click.away="showOverflow = false" style="display:none">
          <a href="{{ route('seller.listings.edit', $listing) }}" wire:navigate>
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit listing
          </a>
          <button class="danger" @click="showOverflow = false; showDelete = true">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            Delete listing
          </button>
        </div>
      </div>
    </div>

    {{-- ── Hero image + thumbnails ──────────────────────────────────── --}}
    @if (!empty($listing->photos))
      <div class="pd2-hero">
        <img
          :src="[{{ collect($listing->photos)->map(fn($p) => "'" . Storage::disk('s3')->temporaryUrl($p, now()->addDay()) . "'")->join(', ') }}][activePhoto]"
          alt="{{ $listing->title }}"
          style="transition: opacity 0.3s ease"
        >
        <div class="badge">
          <span class="chip {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
      </div>

      @if (count($listing->photos) > 1)
        <div class="pd2-thumbs">
          @foreach ($listing->photos as $i => $photo)
            <button
              type="button"
              class="t"
              :class="{ on: activePhoto === {{ $i }} }"
              @click="activePhoto = {{ $i }}"
            >
              <img src="{{ Storage::disk('s3')->temporaryUrl($photo, now()->addDay()) }}" alt="">
            </button>
          @endforeach
        </div>
      @endif
    @else
      <div class="pd2-hero">
        <div style="width:100%;height:100%;background:var(--paper-2);display:flex;align-items:center;justify-content:center;color:var(--muted)">
          <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </div>
        <div class="badge">
          <span class="chip {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
      </div>
    @endif

    {{-- ── Info ──────────────────────────────────────────────────────── --}}
    <div class="pd2-info">
      <h1>{{ $listing->title }}</h1>

      <div class="pd2-price-row">
        <span class="pd2-price">{{ $rs($listing->price_pkr) }}</span>
      </div>

      <div class="pd2-chips">
        @if ($listing->condition)
          <span class="chip">{{ $conditionLabels[$listing->condition] ?? $listing->condition }}</span>
        @endif
        @if ($listing->status === 'live')
          <span class="chip" style="background:rgba(0,3,255,0.08);border-color:rgba(0,3,255,0.15);color:var(--blue)">
            <span class="live-dot" style="width:6px;height:6px"></span> Live on rackrake
          </span>
        @endif
      </div>

      @if ($listing->description)
        <p class="pd2-desc">{{ $listing->description }}</p>
      @endif

      {{-- Pending review notice --}}
      @if ($listing->status === 'pending_review')
        <div class="pd2-status-banner">
          <div class="prep-dot"></div>
          <div class="meta">
            <strong>Pending review</strong>
            <span>We'll review your listing and push it live shortly.</span>
          </div>
        </div>
      @endif

    </div>

    {{-- Actions --}}
    <div class="pd2-actions">
      <a href="{{ route('seller.listings.edit', $listing) }}" class="btn btn-ink" wire:navigate>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit
      </a>
      <button class="btn btn-ghost" @click="showDelete = true" style="color:#B72A2A;border-color:rgba(183,42,42,0.25)">
        Delete
      </button>
    </div>

  </div>

  {{-- ── Delete confirm sheet ─────────────────────────────────────── --}}
  <template x-if="showDelete">
    <div class="sheet-backdrop" @click="showDelete = false">
      <div class="sheet confirm-sheet" @click.stop>
        <div class="sheet-handle"></div>
        <h3>Delete <span class="it">this piece?</span></h3>
        <p>This listing will be permanently removed. If it's live on Shopify, buyers can no longer find it.</p>
        <div class="row" style="gap:var(--s-3);margin-top:var(--s-5)">
          <button class="btn btn-soft" style="flex:1" @click="showDelete = false">Cancel</button>
          <button
            class="btn btn-ghost"
            style="flex:1;color:#B72A2A;border-color:rgba(183,42,42,0.3);background:rgba(183,42,42,0.06)"
            wire:click="delete"
            wire:loading.attr="disabled"
          >
            <span wire:loading.remove wire:target="delete">Delete</span>
            <span wire:loading wire:target="delete">Deleting…</span>
          </button>
        </div>
      </div>
    </div>
  </template>

</div>
