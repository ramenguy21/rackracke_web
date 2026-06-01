{{-- ── Auth — screen 01 ────────────────────────────────────────────────── --}}
<div class="auth2">

  {{-- Decorative background type --}}
  <div class="auth2-art" aria-hidden="true">rackrake</div>

  {{-- Corner accents --}}
  <div class="auth2-corner bl">Pakistan · Karachi · Lahore · Islamabad</div>
  <div class="auth2-corner tr mono" style="font-size:10px;letter-spacing:0.18em">EST. 2026</div>

  {{-- Wordmark --}}
  <div class="auth2-top">
    <div class="brand">
      <span class="brand-mark">rr</span>
      <span>rackrake</span>
    </div>
  </div>

  <div class="auth2-body" style="position:relative;z-index:1">

    <span class="eyebrow">For sellers</span>

    @if ($mode !== 'forgot')
      <h1 class="auth2-headline">
        Sell clothes,<br>
        <span class="it">earn cash.</span>
      </h1>
      <p class="auth2-sub">
        Snap an item. Price it. Post it.<br>
        We do the rest, and you keep&nbsp;92% of every sale.
      </p>
    @else
      <h1 class="auth2-headline">
        Forgot your<br>
        <span class="it">password?</span>
      </h1>
      <p class="auth2-sub">
        @if ($step === 1)
          Enter the email you signed up with — we'll send a reset link.
        @else
          Check your inbox, and your spam just in case.
        @endif
      </p>
    @endif

    {{-- ── OAuth buttons ──────────────────────────────────────────── --}}
    @if ($mode === 'oauth')
      <div class="auth2-buttons fade-in">
        <a href="{{ route('seller.auth.google') }}" class="oauth-btn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          Continue with Google
        </a>
        <button class="oauth-btn alt" wire:click="switchMode('email')">
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Continue with email
        </button>
      </div>
    @endif

    {{-- ── Email flow ──────────────────────────────────────────────── --}}
    @if ($mode === 'email')
      <div class="auth2-email fade-in">
        @if ($step === 1)
          <div class="field">
            <label>Your email</label>
            <input wire:model="email" class="input" type="email" placeholder="you@studio.com" autofocus>
            @error('email') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
          <button class="oauth-btn primary" wire:click="nextStep">
            Continue
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
          <button class="auth-back-link" wire:click="switchMode('oauth')">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Use another method
          </button>
        @else
          <div class="field">
            <label>Password</label>
            <input wire:model="password" class="input" type="password" placeholder="Your password" autofocus>
            @error('password') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
          <button class="oauth-btn primary" wire:click="login" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted)">
            <button wire:click="$set('step', 1)">← Back</button>
            <button wire:click="switchMode('forgot')" style="color:var(--blue);font-weight:600">Forgot password?</button>
          </div>
        @endif
      </div>
    @endif

    {{-- ── Forgot password ─────────────────────────────────────────── --}}
    @if ($mode === 'forgot')
      <div class="auth2-email fade-in">
        @if ($step === 1)
          <div class="field">
            <label>Your email</label>
            <input wire:model="forgotEmail" class="input" type="email" placeholder="you@studio.com" autofocus>
          </div>
          <button
            class="oauth-btn primary"
            wire:click="sendForgotLink"
            @disabled(!str_contains($forgotEmail, '@'))
            style="opacity: {{ str_contains($forgotEmail, '@') ? '1' : '0.45' }}"
          >
            Send reset link
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
          <button class="auth-back-link" wire:click="switchMode('email', 1)">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Back to sign in
          </button>
        @else
          <div class="forgot-sent">
            <div class="ico">
              <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div class="t">Link sent to <strong>{{ $forgotEmail }}</strong></div>
              <div class="s">
                Expires in 30 minutes.
                <button wire:click="$set('step', 1)" style="color:var(--blue);font-weight:600;margin-left:4px">Resend</button>
              </div>
            </div>
          </div>
          <button class="oauth-btn primary" wire:click="switchMode('email', 2)">Back to sign in</button>
        @endif
      </div>
    @endif

    <p class="auth2-meta">
      We make money only when you do.<br>
      By continuing you accept our <a href="#">Seller Terms</a>.
    </p>

    {{-- New seller link --}}
    <p style="text-align:center;font-size:13px;margin-top:var(--s-4)">
      No account yet?
      <a href="{{ route('seller.onboarding') }}" style="color:var(--blue);font-weight:600">Create your shop →</a>
    </p>

  </div>
</div>

