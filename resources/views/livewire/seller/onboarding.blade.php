<div class="onb2">

  <div class="onb2-top">
    <div class="brand">
      <span class="brand-mark">rr</span>
      <span>rackrake</span>
    </div>
    <div class="onb2-progress">
      @for ($i = 1; $i <= $totalSteps; $i++)
        <div class="seg {{ $i < $step ? 'done' : ($i === $step ? 'active' : '') }}"></div>
      @endfor
    </div>
  </div>

  <div class="onb2-body">

    {{-- Step 1 --}}
    @if ($step === 1)
    <div class="fade-in">
      <h1 class="onb2-q">What's your <span class="it">shop</span> called?</h1>
      <p class="onb2-sub">Pick something memorable — this is your brand on rackrake.</p>
      <input
        wire:model.live="shopName"
        class="onb2-big-input"
        type="text"
        placeholder="Studio Karma"
        maxlength="60"
        autofocus
      >
      @if ($shopName)
        <div class="onb2-handle-hint">
          rackrake.com/{{ $handle }}<span class="check"> · available</span>
        </div>
      @endif
      @error('shopName') <p class="onb2-error">{{ $message }}</p> @enderror
    </div>
    @endif

    {{-- Step 2 --}}
    @if ($step === 2)
    <div class="fade-in">
      <h1 class="onb2-q">Where are you <span class="it">based?</span></h1>
      <p class="onb2-sub">Our riders collect from your city.</p>
      <div class="onb2-city-grid">
        @foreach ([
          ['name' => 'Karachi',   'sub' => 'Sindh · PK'],
          ['name' => 'Lahore',    'sub' => 'Punjab · PK'],
          ['name' => 'Islamabad', 'sub' => 'Federal · PK'],
        ] as $c)
          <button type="button"
            class="onb2-city {{ $city === $c['name'] ? 'on' : '' }}"
            wire:click="$set('city', '{{ $c['name'] }}')"
          >
            <div class="picker">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="name">{{ $c['name'] }}</div>
            <div style="font-size:12px;color:{{ $city === $c['name'] ? 'rgba(255,255,255,0.65)' : 'var(--muted)' }};margin-top:2px">{{ $c['sub'] }}</div>
          </button>
        @endforeach
      </div>
      @error('city') <p class="onb2-error">{{ $message }}</p> @enderror
    </div>
    @endif

    {{-- Step 3 --}}
    @if ($step === 3)
    <div class="fade-in">
      <h1 class="onb2-q">Almost <span class="it">done.</span></h1>
      <p class="onb2-sub">Create your account and start listing.</p>
      <div style="display:flex;flex-direction:column;gap:var(--s-4);margin-top:var(--s-2)">
        <div class="field">
          <label>WhatsApp number</label>
          <input wire:model="phone" class="input" type="tel" placeholder="+92 300 0000000">
          <span style="font-size:12px;color:var(--muted);margin-top:4px;display:block">Staff-only · never shown to buyers.</span>
          @error('phone') <span style="color:#B72A2A;font-size:13px">{{ $message }}</span> @enderror
        </div>
        <div class="field">
          <label>Email <span style="color:var(--muted);font-weight:400">(optional)</span></label>
          <input wire:model="email" class="input" type="email" placeholder="you@example.com">
          @error('email') <span style="color:#B72A2A;font-size:13px">{{ $message }}</span> @enderror
        </div>
        <div class="field">
          <label>Password</label>
          <input wire:model="password" class="input" type="password" placeholder="At least 8 characters">
          @error('password') <span style="color:#B72A2A;font-size:13px">{{ $message }}</span> @enderror
        </div>
      </div>
    </div>
    @endif

    {{-- Navigation --}}
    <div class="onb2-actions">
      @if ($step > 1)
        <button type="button" class="btn btn-ghost"
          wire:click="prevStep"
          wire:loading.attr="disabled"
          wire:target="prevStep">
          <span wire:loading.remove wire:target="prevStep">← Back</span>
          <span wire:loading wire:target="prevStep">…</span>
        </button>
      @else
        <div></div>
      @endif

      <button type="button" class="btn btn-primary"
        wire:click="nextStep"
        wire:loading.attr="disabled"
        wire:target="nextStep"
        wire:loading.class="opacity-60">
        <span wire:loading.remove wire:target="nextStep">
          {{ $step < $totalSteps ? 'Next →' : 'Create my shop' }}
        </span>
        <span wire:loading wire:target="nextStep" style="display:none;align-items:center;gap:8px">
          <svg style="animation:spin .7s linear infinite" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
          {{ $step < $totalSteps ? 'Checking…' : 'Creating shop…' }}
        </span>
      </button>
    </div>

  </div>
</div>

<style>
.onb2-error { color:#B72A2A;font-size:13.5px;margin-top:var(--s-2); }
@keyframes spin { to { transform:rotate(360deg); } }
.opacity-60 { opacity:.6; }
</style>
