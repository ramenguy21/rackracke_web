{{-- Onboarding — Alpine drives the visible step instantly; Livewire validates + persists --}}
<div class="onb2"
     x-data="{
       step: $wire.entangle('step'),
       loading: false,
       async next() {
         this.loading = true;
         try { await $wire.nextStep(); } finally { this.loading = false; }
       },
       async prev() {
         this.loading = true;
         try { await $wire.prevStep(); } finally { this.loading = false; }
       }
     }"
>

  <div class="onb2-top">
    <div class="brand">
      <span class="brand-mark">rr</span>
      <span>rackrake</span>
    </div>
    <div class="onb2-progress" :aria-label="`Step ${step} of {{ $totalSteps }}`">
      @for ($i = 1; $i <= $totalSteps; $i++)
        <div class="seg"
             :class="{
               done:   {{ $i }} < step,
               active: {{ $i }} === step
             }"></div>
      @endfor
    </div>
  </div>

  <div class="onb2-body">

    {{-- ── Step 1: Shop name ────────────────────────────────────────── --}}
    <div x-show="step === 1" x-transition:enter="stepIn" style="display:none">
      <h1 class="onb2-q">What's your <span class="it">shop</span> called?</h1>
      <p class="onb2-sub">Pick something memorable — this is your brand on rackrake.</p>

      <input
        wire:model.live="shopName"
        class="onb2-big-input"
        type="text"
        placeholder="Studio Karma"
        maxlength="60"
        @keydown.enter.prevent="next()"
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

    {{-- ── Step 2: City ─────────────────────────────────────────────── --}}
    <div x-show="step === 2" x-transition:enter="stepIn" style="display:none">
      <h1 class="onb2-q">Where are you <span class="it">based?</span></h1>
      <p class="onb2-sub">Our riders collect from your city — choose where you keep your pieces.</p>

      <div class="onb2-city-grid">
        @foreach ([
          ['name' => 'Karachi',   'sub' => 'Sindh · PK'],
          ['name' => 'Lahore',    'sub' => 'Punjab · PK'],
          ['name' => 'Islamabad', 'sub' => 'Federal · PK'],
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

    {{-- ── Step 3: Account ──────────────────────────────────────────── --}}
    <div x-show="step === 3" x-transition:enter="stepIn" style="display:none">
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
          <label>Email <span style="color:var(--muted);font-weight:400;margin-left:4px">(optional)</span></label>
          <input wire:model="email" class="input" type="email" placeholder="you@example.com">
          @error('email') <span class="auth-field-error">{{ $message }}</span> @enderror
        </div>
        <div class="field">
          <label>Password</label>
          <input wire:model="password" class="input" type="password" placeholder="At least 8 characters" @keydown.enter.prevent="next()">
          @error('password') <span class="auth-field-error">{{ $message }}</span> @enderror
        </div>
      </div>
    </div>

    {{-- ── Actions ───────────────────────────────────────────────────── --}}
    <div class="onb2-actions">
      <button
        type="button"
        class="btn btn-ghost"
        x-show="step > 1"
        :disabled="loading"
        @click="prev()"
      >
        <span x-show="!loading">← Back</span>
        <span x-show="loading" style="display:none">…</span>
      </button>
      <div x-show="step === 1"></div>

      <button
        type="button"
        class="btn btn-primary"
        :disabled="loading"
        :class="{ 'opacity-60': loading }"
        @click="next()"
      >
        <template x-if="loading">
          <span style="display:inline-flex;align-items:center;gap:8px">
            <svg style="animation:spin .7s linear infinite" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" opacity=".25"/><path d="M21 12a9 9 0 0 1-9 9"/></svg>
            <template x-if="step < {{ $totalSteps }}">Checking…</template>
            <template x-if="step >= {{ $totalSteps }}">Creating your shop…</template>
          </span>
        </template>
        <template x-if="!loading">
          <span x-text="step < {{ $totalSteps }} ? 'Next →' : 'Create my shop'"></span>
        </template>
      </button>
    </div>

  </div>
</div>

@push('styles')
<style>
@keyframes stepIn { from { opacity:0; transform:translateX(16px); } to { opacity:1; transform:translateX(0); } }
.stepIn { animation: stepIn .25s cubic-bezier(.16,1,.3,1) both; }
.onb2-error   { color:#B72A2A; font-size:13.5px; margin-top:var(--s-2); }
.onb2-tip     { font-size:13px; color:var(--muted); display:flex; align-items:center; gap:6px; margin-top:var(--s-4); }
.onb2-fields  { display:flex; flex-direction:column; gap:var(--s-4); margin-top:var(--s-2); }
.onb2-fields .field-hint { font-size:12px; color:var(--muted); margin-top:4px; display:block; }
.onb2-city .city-sub     { font-size:12px; color:var(--muted); margin-top:2px; }
.onb2-city.on .city-sub  { color:rgba(255,255,255,0.6); }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
@endpush
