@php
  $rs         = fn(int $n) => 'Rs. ' . number_format($n);
  $hasMethod  = !empty($seller->payout_method);
@endphp

<div style="min-height:100vh;background:var(--paper);padding-bottom:120px">
  <div class="shell-wide page-enter">

    {{-- ── Greeting ──────────────────────────────────────────────────── --}}
    <div class="dash-hello">
      <div class="hello-left">
        <span class="eyebrow">Wallet{{ $hasMethod ? ' · ' . $seller->payout_method : '' }}</span>
        @if (!$hasMethod)
          <h1>Add a <span class="it">payout method</span></h1>
          <p class="muted" style="font-size:16px;margin:8px 0 0">so we can send you what you've earned.</p>
        @elseif ($pendingTotal > 0)
          <h1>{{ $rs($pendingTotal) }} <span class="it">incoming.</span></h1>
          <p class="muted" style="font-size:16px;margin:8px 0 0">Clears Friday to your bank.</p>
        @else
          <h1>Rs. 0 <span class="it">incoming.</span></h1>
          <p class="muted" style="font-size:16px;margin:8px 0 0">No payout pending right now.</p>
        @endif
      </div>
    </div>

    {{-- ── Add method CTA ────────────────────────────────────────────── --}}
    @if (!$hasMethod)
      <div class="wallet-add-cta">
        <div class="meta">
          <strong>Where should your earnings go?</strong>
          <span>Add a bank account and we'll deposit your payouts every Friday.</span>
        </div>
        <a href="{{ route('seller.settings') }}" class="btn btn-primary" wire:navigate>Add bank account</a>
      </div>
    @endif

    {{-- ── Snapshot ──────────────────────────────────────────────────── --}}
    @if ($hasMethod)
      <div class="snapshot snapshot-2" style="margin-bottom:var(--s-5)">
        <div class="snap snap-feature">
          <div class="snap-head">
            <div class="snap-title">Pending payout</div>
            <div class="snap-cap">auto-deposited Fri 9am</div>
          </div>
          <div class="v-row">
            <span class="v">{{ $rs($pendingTotal) }}</span>
            @if ($pendingTotal > 0)
              <span class="delta">↗ {{ $owedEntries->count() }} orders</span>
            @endif
          </div>
          <div class="sub">{{ $seller->payout_method }}</div>
        </div>
        <div class="snap">
          <div class="snap-head">
            <div class="snap-title">Lifetime paid out</div>
            <div class="snap-cap">total since you joined</div>
          </div>
          <div class="v-row">
            <span class="v">{{ $rs($lifetimePaidOut) }}</span>
          </div>
          <div class="sub">{{ $paidOutEntries->count() }} {{ $paidOutEntries->count() === 1 ? 'payout' : 'payouts' }}</div>
        </div>
      </div>
    @endif

    {{-- ── Payout history ────────────────────────────────────────────── --}}
    @if ($owedEntries->count() > 0 || $paidOutEntries->count() > 0)
      <div class="section-h">
        <h3>Payout <span class="it">history</span></h3>
        @if ($hasMethod)
          <a href="{{ route('seller.settings') }}" class="btn btn-soft btn-sm" wire:navigate>Change method</a>
        @endif
      </div>
      <div class="orders-list">
        @foreach ($owedEntries as $entry)
          <div class="payout-row">
            <span class="status-pill pending"><span class="d"></span> Pending</span>
            <div class="meta">
              <div class="title">{{ $entry->credited_at?->format('M j, Y') ?? '—' }}</div>
              <div class="sub">{{ $entry->order->listing->title ?? '—' }}</div>
            </div>
            <div class="amount">
              <span class="v">{{ $rs($entry->amount_owed_pkr) }}</span>
              <span class="net">net amount</span>
            </div>
            <div class="actions"></div>
          </div>
        @endforeach
        @foreach ($paidOutEntries as $entry)
          <div class="payout-row">
            <span class="status-pill paid"><span class="d"></span> Paid</span>
            <div class="meta">
              <div class="title">{{ $entry->paid_out_at?->format('M j, Y') ?? '—' }}</div>
              <div class="sub">{{ $entry->order->listing->title ?? '—' }}</div>
            </div>
            <div class="amount">
              <span class="v">{{ $rs($entry->amount_owed_pkr) }}</span>
              <span class="net">net amount</span>
            </div>
            <div class="actions"></div>
          </div>
        @endforeach
      </div>
    @endif

    <p class="wallet-note">We make money only when you do. Payouts are batched weekly every Friday.</p>

  </div>
</div>
