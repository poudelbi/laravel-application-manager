<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

// Temporary route to check configuration
Route::get('/debug-config', function() {
    return [
        'cache_driver' => config('cache.default'),
        'session_driver' => config('session.driver'),
        'cache_config' => config('cache.stores.file'),
        'env_cache_store' => env('CACHE_STORE'),
        'env_session_driver' => env('SESSION_DRIVER'),
    ];
});