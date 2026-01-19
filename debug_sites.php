<?php

// Test script to debug the SitesController
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\File;

// Check if the applications directory exists
$applicationsDir = '/var/www/html/applications';
echo "Applications directory exists: " . (File::exists($applicationsDir) ? 'YES' : 'NO') . "\n";
echo "Applications directory path: $applicationsDir\n";

if (File::exists($applicationsDir)) {
    $directories = File::directories($applicationsDir);
    echo "Number of application directories found: " . count($directories) . "\n";
    
    foreach ($directories as $dir) {
        $appName = basename($dir);
        echo "- Application: $appName (Path: $dir)\n";
        
        // Check if composer.json exists
        $composerPath = $dir . '/composer.json';
        $hasComposer = File::exists($composerPath);
        echo "  - Has composer.json: " . ($hasComposer ? 'YES' : 'NO') . "\n";
        
        // Check if it's running (by checking if there's a process on its assigned port)
        $allDirs = File::directories($applicationsDir);
        $appIndex = array_search($dir, $allDirs);
        $port = 8000 + $appIndex;
        echo "  - Assigned port: $port\n";
        
        // Check if port is in use
        $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
        if ($fp) {
            echo "  - Port $port is in use (application may be running)\n";
            fclose($fp);
        } else {
            echo "  - Port $port is not in use\n";
        }
    }
} else {
    echo "Applications directory does not exist!\n";
}