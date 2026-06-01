{{-- ── Onboarding — screen 02 ──────────────────────────────────────────── --}}
<div class="onb2">

  {{-- Top chrome --}}
  <div class="onb2-top">
    <div class="brand">
      <span class="brand-mark">rr</span>
      <span>rackrake</span>
    </div>
    <div class="onb2-progress" aria-label="Step {{ $step }} of {{ $totalSteps }}">
      @for ($i = 1; $i <= $totalSteps; $i++)
        <div class="seg {{ $i < $step ? 'done' : ($i === $step ? 'active' : '') }}"></div>
      @endfor
    </div>
  </div>

  <div class="onb2-body">

    {{-- ── Step 1: Shop name ────────────────────────────────────────── --}}
    @if ($step === 1)
      <div class="step-content">
        <h1 class="onb2-q">What's your <span class="it">shop</span> called?</h1>
        <p class="onb2-sub">Pick something memorable — this is your brand on rackrake.</p>

        <input
          wire:model.live="shopName"
          class="onb2-big-input"
          type="text"
          placeholder="Studio Karma"
          autofocus
          maxlength="60"
        >

        @if ($shopName)
          <div class="onb2-handle-hint">
            rackrake.com/{{ $handle }}
            <span class="check">· available</span>
          </div>
        @endif

        @error('shopName')
          <p class="onb2-error">{{ $message }}</p>
        @enderror

        <p class="onb2-tip">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Tip: shop names with your own name or city sell well on rackrake.
        </p>
      </div>
    @endif

    {{-- ── Step 2: City ─────────────────────────────────────────────── --}}
    @if ($step === 2)
      <div class="step-content">
        <h1 class="onb2-q">Where are you <span class="it">based?</span></h1>
        <p class="onb2-sub">Our riders collect from your city — choose where you keep your pieces.</p>

        <div class="onb2-city-grid">
          @foreach ([
            ['name' => 'Karachi',    'sub' => 'Sindh · PK'],
            ['name' => 'Lahore',     'sub' => 'Punjab · PK'],
            ['name' => 'Islamabad',  'sub' => 'Federal · PK'],
          ] as $c)
            <button
              type="button"
              class="onb2-city {{ $city === $c['name'] ? 'on' : '' }}"
              wire:click="$set('city', '{{ $c['name'] }}')"
            >
              <div class="picker">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div class="name">{{ $c['name'] }}</div>
              <div class="city-sub">{{ $c['sub'] }}</div>
            </button>
          @endforeach
        </div>

        @error('city')
          <p class="onb2-error">{{ $message }}</p>
        @enderror
      </div>
    @endif

    {{-- ── Step 3: Account ──────────────────────────────────────────── --}}
    @if ($step === 3)
      <div class="step-content">
        <h1 class="onb2-q">Almost <span class="it">done.</span></h1>
        <p class="onb2-sub">Create your account and your shop goes live the moment we approve your first listing.</p>

        <div class="onb2-fields">
          <div class="field">
            <label>WhatsApp number</label>
            <input wire:model="phone" class="input" type="tel" placeholder="+92 300 0000000">
            <span class="field-hint">Staff-only · never shown to buyers.</span>
            @error('phone') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
          <div class="field">
            <label>
              Email
              <span style="color:var(--muted);font-weight:400;margin-left:4px">(optional)</span>
            </label>
            <input wire:model="email" class="input" type="email" placeholder="you@example.com">
            @error('email') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
          <div class="field">
            <label>Password</label>
            <input wire:model="password" class="input" type="password" placeholder="At least 8 characters">
            @error('password') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
        </div>
      </div>
    @endif

    {{-- Actions --}}
    <div class="onb2-actions">
      @if ($step > 1)
        <button type="button" class="btn btn-ghost" wire:click="prevStep">← Back</button>
      @else
        <div></div>
      @endif

      <button
        type="button"
        class="btn btn-primary"
        wire:click="nextStep"
        wire:loading.attr="disabled"
        @if ($step === 1 && !$shopName) disabled @endif
        @if ($step === 2 && !$city) disabled @endif
      >
        <span wire:loading.remove wire:target="nextStep">
          {{ $step < $totalSteps ? 'Next →' : 'Create my shop' }}
        </span>
        <span wire:loading wire:target="nextStep">
          {{ $step < $totalSteps ? 'Checking…' : 'Creating…' }}
        </span>
      </button>
    </div>

  </div>
</div>

