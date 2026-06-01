<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? config('app.name') }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,300..800&family=Instrument+Serif:ital@1&family=JetBrains+Mono:wght@400;500;600;700&family=League+Spartan:wght@700;800&display=swap" rel="stylesheet">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')
  @livewireStyles
  <style>
    .rr-progress-bar { position:fixed;top:0;left:0;z-index:9999;height:2px;background:var(--blue);transition:width .3s ease;width:0;pointer-events:none; }
    .rr-progress-bar[style*="width: 100"] { transition:width .1s ease,opacity .4s ease .1s;opacity:0; }
  </style>
</head>
<body>
  <div class="rr-progress-bar" wire:loading.style="width:70%" wire:loading.delay.shortest></div>

  <x-seller.top-bar :active="$active ?? 'Rack'" />

  <main>
    {{ $slot }}
  </main>

  <x-seller.mobile-tab-bar :active="$active ?? 'Rack'" />

  @livewireScripts
</body>
</html>
