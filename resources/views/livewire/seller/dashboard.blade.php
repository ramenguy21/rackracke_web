@php
  $rs       = fn(int $n) => 'Rs. ' . number_format($n);
  $isEmpty  = $counts['all'] === 0;
  $initial  = strtoupper(substr($seller->shop_name, 0, 1));
  $day      = now()->format('l · M j');
@endphp

<div class="dash2">
  <div class="shell-wide">

    {{-- ── Greeting ──────────────────────────────────────────────────── --}}
    <div class="dash-hello">
      <div class="hello-left">
        <span class="eyebrow">{{ $day }}</span>
        <h1>
          Hi <span class="it" style="font-family:var(--logo);font-style:normal">{{ $seller->shop_name }},</span>
        </h1>
        @if ($isEmpty)
          <p class="muted" style="font-size:17px;margin:10px 0 0;line-height:1.4">Welcome to your rack.</p>
        @else
          <p class="muted" style="font-size:17px;margin:10px 0 0;line-height:1.4">Buyers are browsing your rack right now.</p>
        @endif
      </div>
    </div>

    {{-- ── Vacation banner ───────────────────────────────────────────── --}}
    {{-- Rendered when vacation mode is on (TODO: wire from settings) --}}

    {{-- ── Snapshot cards ───────────────────────────────────────────── --}}
    @unless ($isEmpty)
      <div class="snapshot snapshot-2" style="margin-bottom:var(--s-5)">
        <a href="{{ route('seller.wallet') }}" class="snap snap-feature" wire:navigate>
          <div class="snap-head">
            <div class="snap-title">Money you've earned</div>
            <div class="snap-cap">on its way to your bank</div>
          </div>
          <div class="v-row">
            <span class="v">{{ $rs($pendingPayout) }}</span>
            @if ($pendingPayout > 0)
              <span class="delta">↗ pending</span>
            @endif
          </div>
          <div class="sub">{{ $pendingPayout > 0 ? 'Clears Friday · to your bank' : 'No payout pending' }}</div>
        </a>

        <a href="{{ route('seller.sales') }}" class="snap" wire:navigate>
          <div class="snap-head">
            <div class="snap-title">Sold this week</div>
            <div class="snap-cap">Mon – Sun</div>
          </div>
          <div class="v-row">
            <span class="v">{{ $rs($revenueThisWeek) }}</span>
          </div>
          <div class="sub">{{ $counts['sold'] }} {{ $counts['sold'] === 1 ? 'piece' : 'pieces' }} sold</div>
        </a>
      </div>
    @endunless

    {{-- ── Shop identity strip ──────────────────────────────────────── --}}
    <div class="shop-strip">
      <div class="av">{{ $initial }}</div>
      <div class="meta">
        <div class="name">
          {{ $seller->shop_name }}
          @if ($seller->verified)
            <span class="verified-mini">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Verified
            </span>
          @endif
        </div>
        @if ($seller->city)
          <div class="stats">{{ $seller->city }} · Pakistan</div>
        @endif
      </div>
      <a href="{{ route('seller.settings') }}" class="btn btn-soft btn-sm" style="margin-left:auto" wire:navigate>
        Settings
      </a>
    </div>

    {{-- ── Listings section ──────────────────────────────────────────── --}}
    <div class="section-h">
      <h3>Your <span class="it">rack</span> · <span class="mono">{{ $counts['all'] }}</span></h3>
      <div class="tabs-pill">
        @foreach (['all' => 'All', 'live' => 'Live', 'drafts' => 'Drafts', 'sold' => 'Sold'] as $key => $label)
          <button
            class="{{ $activeTab === $key ? 'on' : '' }}"
            wire:click="$set('activeTab', '{{ $key }}')"
          >
            {{ $label }}
            <span class="mono" style="font-size:11px;opacity:0.7">{{ $counts[$key] }}</span>
          </button>
        @endforeach
      </div>
    </div>

    {{-- ── Empty rack ────────────────────────────────────────────────── --}}
    @if ($isEmpty)
      <div class="dash-empty">
        <div class="dash-empty-art" aria-hidden="true">
          <div class="rack rack-1"></div>
          <div class="rack rack-2"></div>
          <div class="rack rack-3"></div>
        </div>
        <h3>Your <span class="it">rack</span> is empty.</h3>
        <p>List your first piece — it takes about a minute and buyers are already here.</p>
        <a href="{{ route('seller.listings.create') }}" class="btn btn-primary" wire:navigate>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          List your first piece
        </a>
        <ul class="dash-empty-tips">
          <li class="tip">
            <div class="k">~1 min</div>
            <div class="s">to post a piece</div>
          </li>
          <li class="tip">
            <div class="k">{{ 100 - 8 }}%</div>
            <div class="s">of every sale is yours</div>
          </li>
        </ul>
      </div>

    {{-- ── Per-tab empty ─────────────────────────────────────────────── --}}
    @elseif ($listings->isEmpty())
      <div class="tab-empty">
        @if ($activeTab === 'live')
          <h3>Nothing live yet.</h3>
          <p>Post a listing and we'll review it — once approved it goes straight to buyers.</p>
          <a href="{{ route('seller.listings.create') }}" class="btn btn-primary btn-sm" style="margin-top:var(--s-3)" wire:navigate>Add a listing</a>
        @elseif ($activeTab === 'drafts')
          <h3>No drafts.</h3>
          <p>Start a listing and save it as a draft to finish later.</p>
        @elseif ($activeTab === 'sold')
          <h3>Nothing sold yet.</h3>
          <p>Sold items appear here — keep your rack stocked!</p>
        @else
          <h3>Nothing here.</h3>
          <p></p>
        @endif
      </div>

    {{-- ── Listing grid ──────────────────────────────────────────────── --}}
    @else
      <div class="grid2">
        @foreach ($listings as $listing)
          <a href="{{ route('seller.listings.show', $listing) }}" class="tile2" wire:navigate>
            <div class="img">
              @if (!empty($listing->photos))
                <img src="{{ Storage::disk('s3')->temporaryUrl($listing->photos[0], now()->addDay()) }}" alt="{{ $listing->title }}" loading="lazy">
              @else
                <div class="tile-noimg">
                  <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
              @endif
              <div class="badges">
                @if ($listing->status === 'live')
                  <span class="b live">
                    <span class="live-dot" style="width:6px;height:6px;margin-right:3px"></span>Live
                  </span>
                @elseif ($listing->status === 'sold')
                  <span class="b sold">Sold</span>
                @elseif ($listing->status === 'pending_review')
                  <span class="b">Pending</span>
                @else
                  <span class="b">Draft</span>
                @endif
              </div>
            </div>
            <div class="body">
              <div class="title">{{ $listing->title }}</div>
              <div class="row" style="justify-content:space-between;margin-top:2px">
                <span class="price mono">{{ $rs($listing->price_pkr) }}</span>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    @endif

  </div>

  {{-- FAB --}}
  @unless ($isEmpty)
    <a href="{{ route('seller.listings.create') }}" class="fab" aria-label="New listing" wire:navigate>
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New listing
    </a>
  @endunless

</div>

