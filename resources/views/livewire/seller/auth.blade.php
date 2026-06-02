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
      <div class="auth2-buttons fade-in" x-data="{ googleNote: false }">
        <button class="oauth-btn" style="opacity:0.55;cursor:not-allowed;position:relative" @click="googleNote = true" @click.outside="googleNote = false" type="button">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          Continue with Google
          <span x-show="googleNote" x-transition style="display:none;position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);background:var(--ink);color:#fff;font-size:12px;padding:5px 10px;border-radius:8px;white-space:nowrap;font-weight:500">Coming soon</span>
        </button>
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
          <div wire:key="email-step" class="field">
            <label>Your email</label>
            <input wire:model="email" class="input" type="email" autocomplete="username" placeholder="you@studio.com" autofocus>
            @error('email') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
          <button class="oauth-btn primary" wire:click="nextStep" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="nextStep">Continue</span>
            <span wire:loading wire:target="nextStep">Checking…</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
          <button class="auth-back-link" wire:click="switchMode('oauth')">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Use another method
          </button>
        @else
          <div wire:key="password-step" class="field">
            <label>Password</label>
            <input wire:model="password" class="input" type="password" autocomplete="current-password" placeholder="Your password" autofocus>
            @error('password') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
          <button class="oauth-btn primary" wire:click="login" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in…</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </button>
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--muted)">
            <button wire:click="goBack">← Back</button>
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
            <input wire:model.live="forgotEmail" class="input" type="email" autocomplete="username" placeholder="you@studio.com" autofocus>
            @error('forgotEmail') <span class="auth-field-error">{{ $message }}</span> @enderror
          </div>
          <button
            class="oauth-btn primary"
            wire:click="sendForgotLink"
            wire:loading.attr="disabled"
          >
            <span wire:loading.remove wire:target="sendForgotLink">Send reset link</span>
            <span wire:loading wire:target="sendForgotLink">Sending…</span>
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

    <p class="auth2-meta" x-data="{ termsOpen: false }">
      We make money only when you do.<br>
      By continuing you accept our
      <button @click="termsOpen = true" style="color:var(--blue);font-weight:600;text-decoration:underline;font-size:inherit">Seller Terms</button>.

      {{-- Terms modal --}}
      <template x-if="termsOpen">
        <div class="sheet-backdrop" @click="termsOpen = false" style="z-index:300">
          <div class="sheet" @click.stop style="max-height:80vh;overflow-y:auto">
            <div class="sheet-handle"></div>
            <h3>Seller <span class="it">Terms</span></h3>
            <div style="font-size:14px;color:var(--ink);line-height:1.65;margin-top:var(--s-4)">
              <p style="margin-bottom:var(--s-3)">Full terms coming soon. By selling on rackrake you agree to the following:</p>
              <ul style="padding-left:var(--s-5);display:flex;flex-direction:column;gap:var(--s-2)">
                <li>You are the rightful owner of all items you list.</li>
                <li>Items must be accurately described and photographed.</li>
                <li>rackrake charges an 8% platform fee per completed sale.</li>
                <li>Payouts are processed by the rackrake team after each sale is settled.</li>
                <li>rackrake reserves the right to remove listings that violate our guidelines.</li>
              </ul>
              <p style="margin-top:var(--s-4);color:var(--muted);font-size:13px">Full legal terms will be published at rackrake.shop/terms. For any questions contact rackrakeapp@gmail.com</p>
            </div>
            <button class="btn btn-primary" style="width:100%;margin-top:var(--s-5)" @click="termsOpen = false">Got it</button>
          </div>
        </div>
      </template>
    </p>

    {{-- New seller CTA --}}
    @if ($mode === 'oauth')
      <div style="margin-top:var(--s-5);padding-top:var(--s-5);border-top:1px solid var(--line)">
        <p style="text-align:center;font-size:13px;color:var(--muted);margin-bottom:var(--s-3)">New to rackrake?</p>
        <a href="{{ route('seller.onboarding') }}"
           class="oauth-btn"
           style="background:var(--yellow);color:var(--ink);border-color:transparent;font-weight:700"
           wire:navigate>
          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
          Create your shop — it's free
        </a>
      </div>
    @else
      <p style="text-align:center;font-size:13px;margin-top:var(--s-4)">
        No account? <a href="{{ route('seller.onboarding') }}" style="color:var(--blue);font-weight:600" wire:navigate>Create your shop →</a>
      </p>
    @endif

  </div>
</div>

