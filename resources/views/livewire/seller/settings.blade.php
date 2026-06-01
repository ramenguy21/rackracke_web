<div style="min-height:100vh;background:var(--paper);padding-bottom:var(--s-9)">
  <div class="shell page-enter">

    <div style="padding-top:var(--s-7)">
      <span class="eyebrow">Settings</span>
      <h1 class="page-h1" style="margin-bottom:var(--s-6)">Your <span class="it">shop.</span></h1>
    </div>

    {{-- ── Shop profile ──────────────────────────────────────────────── --}}
    <div class="settings-card">
      <h4>Shop profile</h4>
      <div class="field-stack">

        <label>
          <span>Shop name</span>
          <input wire:model="shopName" class="input" type="text" maxlength="60">
          @error('shopName') <span style="color:#B72A2A;font-size:13px">{{ $message }}</span> @enderror
        </label>

        {{-- Handle: read-only --}}
        <div class="ro-field">
          <span class="ro-label">Handle</span>
          <div class="ro-value">
            <span class="ro-text mono">rackrake.com/@{{ \Illuminate\Support\Str::slug($seller->shop_name, '') }}</span>
            <span class="ro-lock">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Set by rackrake
            </span>
          </div>
          <span style="font-size:12px;color:var(--muted);margin-top:4px;display:block">
            Locked once your shop goes live — message support to change it.
          </span>
        </div>

        <label>
          <span>Bio</span>
          <textarea wire:model="bio" class="textarea" rows="3" placeholder="Tell buyers about your style…"></textarea>
        </label>

        {{-- City: read-only --}}
        <div class="ro-field">
          <span class="ro-label">Ships from</span>
          <div class="ro-value">
            <span class="ro-text">{{ $seller->city ?? 'Not set' }}</span>
            <span class="ro-lock">
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Set by rackrake
            </span>
          </div>
          <span style="font-size:12px;color:var(--muted);margin-top:4px;display:block">
            Tied to your rider zone — contact support if you've moved cities.
          </span>
        </div>

        <label>
          <span>WhatsApp <span style="color:var(--muted);font-weight:400">· private, staff-only</span></span>
          <input wire:model="phone" class="input" type="tel" placeholder="+92 300 0000000">
          @error('phone') <span style="color:#B72A2A;font-size:13px">{{ $message }}</span> @enderror
        </label>

        {{-- Verified status --}}
        <div class="verified-readout">
          <span class="ro-label">Verification</span>
          <span class="vr-pill {{ $seller->verified ? 'on' : '' }}">
            @if ($seller->verified)
              <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              Verified by rackrake
            @else
              Not verified yet
            @endif
          </span>
          <span class="vr-note">Verification is managed by the rackrake team.</span>
        </div>

      </div>
    </div>

    {{-- ── Payout ────────────────────────────────────────────────────── --}}
    <div class="settings-card">
      <h4>Payout</h4>
      @if ($seller->payout_method)
        <div class="payout-method">
          <div class="bank-mark">HBL</div>
          <div class="meta">
            <div class="title">{{ $seller->payout_method }}</div>
            <div class="sub">Auto-deposited every Friday</div>
          </div>
          <a href="#" class="btn btn-soft btn-sm">Change</a>
        </div>
      @else
        <p class="settings-note">No payout method added yet. Add one so we can send you what you've earned.</p>
        <a href="#" class="btn btn-ghost btn-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Add bank account
        </a>
      @endif
      <p style="font-size:13px;color:var(--muted);margin-top:var(--s-3)">
        Platform fee · set by rackrake — <strong>8%</strong>
      </p>
    </div>

    {{-- ── Help ──────────────────────────────────────────────────────── --}}
    <div class="settings-card">
      <h4>Help</h4>
      <div class="help-options">
        <a href="mailto:rackrakeapp@gmail.com" class="help-card">
          <div class="ico" style="background:var(--yellow);color:var(--ink)">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div>
            <div class="t">Email support</div>
            <div class="s">rackrakeapp@gmail.com</div>
          </div>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>
        </a>
      </div>
    </div>

    {{-- ── Danger ────────────────────────────────────────────────────── --}}
    <div class="settings-card danger" x-data="{ showClose: false }">
      <h4>Account</h4>
      <p class="settings-note">Closing your account is permanent. Any pending payouts will still clear to your bank.</p>
      <button class="btn btn-ghost btn-sm" style="color:#B72A2A;border-color:rgba(183,42,42,0.3)" @click="showClose = true">
        Close account
      </button>

      <template x-if="showClose">
        <div class="sheet-backdrop" @click="showClose = false">
          <div class="sheet confirm-sheet" @click.stop>
            <div class="sheet-handle"></div>
            <h3>Close your <span class="it">account?</span></h3>
            <p>This is permanent and cannot be undone. Your listings will be taken down. Pending payouts will still reach your bank.</p>
            <div class="row" style="gap:var(--s-3);margin-top:var(--s-5)">
              <button class="btn btn-soft" style="flex:1" @click="showClose = false">Cancel</button>
              <button class="btn btn-ghost" style="flex:1;color:#B72A2A;border-color:rgba(183,42,42,0.3);background:rgba(183,42,42,0.06)">
                Close account
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>

    {{-- ── Footer ────────────────────────────────────────────────────── --}}
    <div class="settings-foot">
      <button wire:click="logout" class="btn btn-ghost btn-sm" style="color:var(--muted);margin-right:auto">
        Sign out
      </button>
      <a href="{{ route('seller.dashboard') }}" class="btn btn-soft">Cancel</a>
      <button wire:click="save" class="btn btn-primary" wire:loading.attr="disabled">
        @if ($saved)
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Saved
        @else
          <span wire:loading.remove wire:target="save">Save changes</span>
          <span wire:loading wire:target="save">Saving…</span>
        @endif
      </button>
    </div>

  </div>
</div>
