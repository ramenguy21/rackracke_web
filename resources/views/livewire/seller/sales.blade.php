@php
  $rs = fn(int $n) => 'Rs. ' . number_format($n);
  $statusConfig = [
    'Placed'            => ['label' => 'Awaiting pickup',         'class' => 'awaiting'],
    'CodConfirm'        => ['label' => 'Awaiting confirmation',   'class' => 'awaiting'],
    'Procuring'         => ['label' => 'Awaiting pickup',         'class' => 'awaiting'],
    'OutForDelivery'    => ['label' => 'On the way to buyer',     'class' => 'transit'],
    'Delivered'         => ['label' => 'Delivered',               'class' => 'delivered'],
    'Collected'         => ['label' => 'Delivered',               'class' => 'delivered'],
    'Settled'           => ['label' => 'Settled',                 'class' => 'delivered'],
    'Cancelled'         => ['label' => 'Buyer cancelled',         'class' => 'cancelled'],
    'DeliveryFailed'    => ['label' => 'Delivery failed',         'class' => 'returned'],
    'ReturnedToSeller'  => ['label' => 'Returned · earnings reversed', 'class' => 'returned'],
    'Refunded'          => ['label' => 'Refunded',                'class' => 'cancelled'],
    'ProcurementFailed' => ['label' => 'Procurement failed',      'class' => 'cancelled'],
  ];
@endphp

<div style="min-height:100vh;background:var(--paper);padding-bottom:120px">
  <div class="shell-wide page-enter">

    {{-- ── Greeting ──────────────────────────────────────────────────── --}}
    <div class="dash-hello">
      <div class="hello-left">
        <span class="eyebrow">Sales</span>
        @if ($awaiting->count() > 0)
          <h1>{{ $awaiting->count() }} <span class="it">{{ $awaiting->count() === 1 ? 'piece' : 'pieces' }}</span> waiting.</h1>
          <p class="muted" style="font-size:16px;margin:8px 0 0">Our rider collects from you in {{ $city ?? 'your city' }}.</p>
        @else
          <h1>You're <span class="it">all caught up.</span></h1>
          <p class="muted" style="font-size:16px;margin:8px 0 0">New sales land here the moment a buyer checks out.</p>
        @endif
      </div>
    </div>

    {{-- ── Pinned: Awaiting pickup ───────────────────────────────────── --}}
    <div class="awaiting-pin {{ $awaiting->count() > 0 ? 'has' : '' }}">
      <div class="awaiting-pin-head">
        <div class="apt">
          <span class="apt-dot"></span>
          <h3>Awaiting pickup</h3>
          <span class="apt-n">{{ $awaiting->count() }}</span>
        </div>
        @if ($city)
          <span class="apt-sub">Rider collects from you · {{ $city }}</span>
        @endif
      </div>

      @if ($awaiting->count() > 0)
        <div class="sales-howto">
          <svg width="16" height="16" fill="none" stroke="var(--blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Our rider collects from you in <strong>{{ $city ?? 'your city' }}</strong>.
          We'll ping you before they arrive — have the piece bagged with the order code on it.
          No printing needed.
        </div>
        <div class="orders-list">
          @foreach ($awaiting as $order)
            @include('livewire.seller._sale-row', compact('order', 'rs', 'statusConfig'))
          @endforeach
        </div>
      @else
        <div class="awaiting-pin-clear">
          <div class="ico">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div>
            <strong>Nothing waiting for pickup.</strong>
            <span>New sales land here the moment a buyer checks out.</span>
          </div>
        </div>
      @endif
    </div>

    {{-- ── Everything else ───────────────────────────────────────────── --}}
    <div class="section-h">
      <h3>Everything <span class="it">else</span></h3>
      <div class="tabs-pill">
        @foreach (['cancelled' => 'Cancelled · returned', 'transit' => 'On the way', 'delivered' => 'Delivered'] as $key => $label)
          <button
            class="{{ $activeTab === $key ? 'on' : '' }}"
            wire:click="$set('activeTab', '{{ $key }}')"
          >{{ $label }}</button>
        @endforeach
      </div>
    </div>

    @if ($tabOrders->isEmpty())
      <div class="orders-empty">
        <div class="ico">
          <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3>{{ $activeTab === 'cancelled' ? 'No issues. Clean week.' : 'Nothing in this lane.' }}</h3>
        <p>{{ $activeTab === 'cancelled' ? 'Cancelled and returned orders appear here.' : '' }}</p>
      </div>
    @else
      <div class="orders-list">
        @foreach ($tabOrders as $order)
          @include('livewire.seller._sale-row', compact('order', 'rs', 'statusConfig'))
        @endforeach
      </div>
    @endif

  </div>
</div>
