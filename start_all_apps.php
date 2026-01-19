#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Support\Facades\File;

// Create Laravel application instance
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

// Get all applications
$applicationsDir = '/var/www/html/applications';
$applications = File::directories($applicationsDir);

// Sort applications by name to ensure consistent port assignment
sort($applications);

echo "Starting all Laravel applications on their assigned ports...\n";

foreach ($applications as $index => $appPath) {
    $appName = basename($appPath);
    $port = 8001 + $index; // Start from port 8001
    
    echo "Starting application: {$appName} on port: {$port}\n";
    
    // Check if there's already a process running on this port
    $command = "lsof -i :{$port} -t";
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && !empty($output)) {
        echo "  - Port {$port} is already in use, skipping...\n";
        continue;
    }
    
    // Start the application on its assigned port
    $startCommand = "cd " . escapeshellarg($appPath) . " && php artisan serve --host=0.0.0.0 --port={$port} > /dev/null 2>&1 &";
    exec($startCommand, $output, $returnCode);
    
    if ($returnCode === 0 || $returnCode === 1) {
        echo "  - Successfully started on port {$port}\n";
    } else {
        echo "  - Failed to start on port {$port}\n";
    }
}

echo "\nAll applications have been started on their assigned ports.\n";
echo "They are now accessible publicly at:\n";
foreach ($applications as $index => $appPath) {
    $appName = basename($appPath);
    $port = 8001 + $index;
    echo "  - http://server.poudelbijaya.com.np:{$port}/ ({$appName})\n";
}