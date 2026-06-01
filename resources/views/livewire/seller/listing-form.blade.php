@php
    $conditions = ['new_with_tags' => 'New with tags', 'excellent' => 'Excellent', 'good' => 'Good', 'fair' => 'Fair'];
    $categories = ['Dresses', 'Shoes', 'Bags', 'Jackets', 'Tops', 'Bottoms', 'Accessories', 'Other'];
    $net = $this->netAmount();
    $rs  = fn(int $n) => 'Rs. ' . number_format($n);
@endphp

<div class="shell">
  <div class="al2 page-enter">

    {{-- Top bar --}}
    <div class="al2-top">
      <a href="{{ route('seller.dashboard') }}" class="btn btn-ghost btn-sm" wire:navigate>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back
      </a>
      <button class="btn btn-soft btn-sm" wire:click="saveDraft" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="saveDraft">Save as draft</span>
        <span wire:loading wire:target="saveDraft">Saving…</span>
      </button>
    </div>

    <span class="eyebrow">{{ $listing?->exists ? 'EDIT PIECE' : 'NEW PIECE' }}</span>
    <h1 class="page-h1" style="margin-bottom:var(--s-6)">
      Snap. <span class="it">Post.</span>
    </h1>

    {{-- ── Photo upload (Alpine) ──────────────────────────────────── --}}
    <div
      x-data="photoUpload({{ json_encode(array_map(fn($p) => ['id' => $p, 'url' => Storage::url($p), 'uploading' => false, 'error' => null], $photos ?? [])) }})"
      x-on:photos-changed.window="$wire.setPhotos($event.detail.photos.map(p => p.url))"
      @dragover.prevent="dragging = true"
      @dragleave.prevent="dragging = false"
      @drop.prevent="onDrop($event)"
    >

      {{-- Empty drop zone --}}
      <template x-if="!hasPhotos">
        <div
          class="al2-photos-empty"
          :class="{ 'drag-over': dragging }"
          @click="$refs.fileInput.click()"
        >
          <div class="glyph">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <h3>Drop <span class="it">photos</span> to begin</h3>
          <p>Drag photos here or tap to choose. Clear photos sell faster — natural light, plain background.</p>
          <span class="btn btn-ghost">Choose photos</span>
        </div>
      </template>

      {{-- Filled photo grid --}}
      <template x-if="hasPhotos">
        <div class="al2-photo-grid"
             x-sort="onSortEnd()"
             x-sort:config="{ animation: 180, ghostClass: 'sort-ghost' }">
          <template x-for="(photo, i) in photos" :key="photo.id">
            <div class="slot photo-slot-enter" :class="{ hero: i === 0 }" :x-sort:item="photo.id">
              <img :src="photo.url" alt="" draggable="false">
              <span x-show="i === 0" class="pin">Cover</span>
              <div class="slot-actions">
                <button type="button" class="slot-rm" @click.stop="remove(photo.id)" title="Remove">
                  <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
              </div>
              <div x-show="photo.uploading" class="slot-overlay">
                <div class="slot-spinner"></div>
              </div>
            </div>
          </template>

          {{-- Add more slot --}}
          <div class="slot add" @click="$refs.fileInput.click()" title="Add more photos">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Add</span>
          </div>
        </div>
      </template>

      <input
        x-ref="fileInput"
        type="file"
        accept="image/*"
        multiple
        style="display:none"
        @change="onPick($event)"
      >
    </div>

    {{-- ── Compose card ────────────────────────────────────────────── --}}
    <div class="compose">

      {{-- Title --}}
      <input
        wire:model.live="title"
        class="title-input"
        type="text"
        placeholder="What is this piece?"
        maxlength="200"
      >
      @error('title')
        <p class="field-error">{{ $message }}</p>
      @enderror

      <div class="row-divider"></div>

      {{-- Price --}}
      <div class="compose-price-row">
        <span class="price-prefix mono">Rs.</span>
        <input
          wire:model.live="price"
          class="price-input"
          type="number"
          placeholder="0"
          min="1"
          inputmode="numeric"
        >
      </div>
      @error('price')
        <p class="field-error">{{ $message }}</p>
      @enderror

      <div class="row-divider"></div>

      {{-- Condition --}}
      <div class="field-inline">
        <span class="k">Condition</span>
        <div class="pill-row2 v">
          @foreach ($conditions as $val => $label)
            <button
              type="button"
              class="pill2 {{ $condition === $val ? 'on' : '' }}"
              wire:click="$set('condition', '{{ $val }}')"
            >{{ $label }}</button>
          @endforeach
        </div>
      </div>
      @error('condition')
        <p class="field-error">{{ $message }}</p>
      @enderror

      <div class="row-divider"></div>

      {{-- Category --}}
      <div class="field-inline">
        <span class="k">Category</span>
        <div class="pill-row2 v">
          @foreach ($categories as $cat)
            <button
              type="button"
              class="pill2 blue {{ $category === $cat ? 'on' : '' }}"
              wire:click="$set('category', '{{ $cat }}')"
            >{{ $cat }}</button>
          @endforeach
        </div>
      </div>

      <div class="row-divider"></div>

      {{-- Description --}}
      <textarea
        wire:model="description"
        class="desc-input"
        placeholder="Describe the piece — fabric, fit, how it's worn, any flaws..."
        rows="3"
      ></textarea>

      {{-- Footer --}}
      <div class="compose-foot">
        <div class="earn-note">
          <span class="k">You earn</span>
          <span class="v">{{ $net > 0 ? $rs($net) : '—' }}</span>
          <span class="sub">net · after {{ $commissionPct }}% fee</span>
        </div>
        <button
          class="btn btn-primary btn-lg"
          wire:click="publish"
          wire:loading.attr="disabled"
          wire:loading.class="opacity-50"
        >
          <span wire:loading.remove wire:target="publish">Post listing</span>
          <span wire:loading wire:target="publish">Posting…</span>
        </button>
      </div>

    </div>

  </div>
</div>

{{-- Extra CSS for the photo grid --}}
