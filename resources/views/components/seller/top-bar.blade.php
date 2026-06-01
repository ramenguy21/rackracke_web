@props(['active' => 'Rack'])

@php
    $tabs = [
        ['id' => 'Rack',    'route' => 'seller.dashboard'],
        ['id' => 'Sales',   'route' => 'seller.sales'],
        ['id' => 'Reviews', 'route' => 'seller.reviews'],
        ['id' => 'Wallet',  'route' => 'seller.wallet'],
    ];
    $seller = auth('seller')->user();
    $initial = $seller ? strtoupper(substr($seller->shop_name, 0, 1)) : 'S';
@endphp

<header class="bar">
    <div class="shell-wide">
        <div class="bar-inner">
            <a href="{{ route('seller.dashboard') }}" class="brand" aria-label="Home">
                <span class="brand-mark">rr</span>
                <span>rackrake</span>
            </a>

            <nav class="bar-tabs" aria-label="Main">
                @foreach ($tabs as $tab)
                    <a href="{{ route($tab['route']) }}"
                       class="{{ $active === $tab['id'] ? 'active' : '' }}"
                       role="button">
                        {{ $tab['id'] }}
                    </a>
                @endforeach
            </nav>

            <div class="bar-right">
                <a href="{{ route('seller.settings') }}" class="avatar" title="Account & settings">
                    {{ $initial }}
                </a>
            </div>
        </div>
    </div>
</header>
