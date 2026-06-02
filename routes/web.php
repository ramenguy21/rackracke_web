<?php

use App\Livewire\Seller;
use App\Livewire\Seller\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

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
    Route::post('/listings/upload-photo', function (Request $request) {
        $request->validate(['photo' => 'required|image|max:10240']);
        $path = $request->file('photo')->store('listings', 's3');
        return response()->json([
            'url'  => Storage::disk('s3')->temporaryUrl($path, now()->addHours(6)),
            'path' => $path,
        ]);
    })->name('listings.upload-photo');
});

// ── Admin CSV exports (protected by admin guard) ────────────────────────────
Route::middleware('auth:admin')->prefix('admin/export')->name('admin.export.')->group(function () {

    Route::get('/listings', function () {
        $listings = \App\Models\Listing::with('seller')->orderBy('created_at', 'desc')->get();
        $filename = 'listings-' . now()->format('Y-m-d') . '.csv';

        return Response::streamDownload(function () use ($listings) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Seller', 'Title', 'Condition', 'Price (PKR)', 'Status', 'Shopify Product ID', 'Collection Handle', 'Submitted']);
            foreach ($listings as $l) {
                fputcsv($out, [
                    $l->id, $l->seller->shop_name, $l->title, $l->condition,
                    $l->price_pkr, $l->status, $l->shopify_product_id ?? '',
                    $l->collection_handle ?? '', $l->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    })->name('listings');

    Route::get('/orders', function () {
        $orders = \App\Models\Order::with(['listing.seller'])->orderBy('created_at', 'desc')->get();
        $filename = 'orders-' . now()->format('Y-m-d') . '.csv';

        return Response::streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Shopify Order ID', 'Item', 'Seller', 'Buyer Contact', 'State', 'Payment', 'Sale Price (PKR)', 'Commission %', 'Seller Net (PKR)', 'Placed']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->id, $o->shopify_order_id ?? '', $o->listing->title,
                    $o->listing->seller->shop_name, $o->buyer_contact ?? '',
                    class_basename((string) $o->state), strtoupper($o->payment_type),
                    $o->sale_price_pkr, $o->take_rate_pct, $o->sellerNet(),
                    $o->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    })->name('orders');

    Route::get('/ledger', function () {
        $entries = \App\Models\LedgerEntry::with(['seller', 'order.listing'])->orderBy('credited_at', 'desc')->get();
        $filename = 'ledger-' . now()->format('Y-m-d') . '.csv';

        return Response::streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Seller', 'Item', 'Amount Owed (PKR)', 'Status', 'Credited', 'Paid Out']);
            foreach ($entries as $e) {
                fputcsv($out, [
                    $e->id, $e->seller->shop_name, $e->order->listing->title,
                    $e->amount_owed_pkr,
                    $e->status === 'paid_out' ? 'Paid out' : 'Owed',
                    $e->credited_at?->format('Y-m-d'),
                    $e->paid_out_at?->format('Y-m-d') ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    })->name('ledger');

});
