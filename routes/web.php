<?php

use App\Livewire\Seller;
use App\Livewire\Seller\Login;
use Illuminate\Support\Facades\Route;

// Root → dashboard if logged in, login if not
Route::get('/', fn () => auth('seller')->check()
    ? redirect()->route('seller.dashboard')
    : redirect()->route('seller.auth')
);

// ── Seller auth (guest-only) ────────────────────────────────────────────────
Route::middleware('guest:seller')->prefix('seller')->name('seller.')->group(function () {
    Route::get('/login',      Login::class)->name('auth');
    Route::get('/onboarding', Seller\Onboarding::class)->name('onboarding');

    // Social auth placeholder
    Route::get('/auth/google',          fn () => redirect()->route('seller.auth'))->name('auth.google');
    Route::get('/auth/google/callback', fn () => redirect()->route('seller.auth'))->name('auth.google.callback');
});

// ── Seller portal (authenticated) ──────────────────────────────────────────
Route::middleware('auth:seller')->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard',           Seller\Dashboard::class)->name('dashboard');
    Route::get('/sales',               Seller\Sales::class)->name('sales');
    Route::get('/reviews',             Seller\Reviews::class)->name('reviews');
    Route::get('/wallet',              Seller\Wallet::class)->name('wallet');
    Route::get('/settings',            Seller\Settings::class)->name('settings');
    Route::get('/listings/new',        Seller\ListingForm::class)->name('listings.create');
    Route::get('/listings/{listing}',  Seller\ListingDetail::class)->name('listings.show');
    Route::get('/listings/{listing}/edit', Seller\ListingForm::class)->name('listings.edit');
});
