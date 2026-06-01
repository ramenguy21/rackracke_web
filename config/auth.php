<?php

use App\Models\Admin;
use App\Models\Seller;

return [

    'defaults' => [
        'guard' => 'seller',
        'passwords' => 'sellers',
    ],

    'guards' => [
        // Seller-facing Livewire screens
        'seller' => [
            'driver' => 'session',
            'provider' => 'sellers',
        ],

        // Admin Filament panel
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    'providers' => [
        'sellers' => [
            'driver' => 'eloquent',
            'model' => Seller::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
    ],

    'passwords' => [
        'sellers' => [
            'provider' => 'sellers',
            'table' => 'seller_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
