<?php

// Simple PHP script to check the environment variables directly
require_once __DIR__.'/vendor/autoload.php';

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

$app->bind(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->bind(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check the raw environment values
echo "Raw ENV values:\n";
echo "CACHE_STORE: " . ($_ENV['CACHE_STORE'] ?? getenv('CACHE_STORE') ?? 'NOT SET') . "\n";
echo "SESSION_DRIVER: " . ($_ENV['SESSION_DRIVER'] ?? getenv('SESSION_DRIVER') ?? 'NOT SET') . "\n";

// Check the Laravel config values
echo "\nLaravel config values:\n";
echo "cache.default: " . config('cache.default') . "\n";
echo "session.driver: " . config('session.driver') . "\n";

// Check the env() helper function values
echo "\nenv() helper values:\n";
echo "env('CACHE_STORE'): " . env('CACHE_STORE') . "\n";
echo "env('SESSION_DRIVER'): " . env('SESSION_DRIVER') . "\n";