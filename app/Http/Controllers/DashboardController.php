<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    protected $applicationsDir = '/var/www/html/applications';

    public function index()
    {
        $applications = $this->getApplications();
        
        // Count deployed applications (those that exist in the web root)
        $deployedCount = 0;
        foreach ($applications as $app) {
            $deployPath = '/var/www/html/' . $app['name'];
            if (File::exists($deployPath)) {
                $deployedCount++;
            }
        }

        return view('dashboard', compact('applications', 'deployedCount'));
    }

    private function getApplications()
    {
        $apps = [];
        if (File::exists($this->applicationsDir)) {
            $directories = File::directories($this->applicationsDir);
            foreach ($directories as $dir) {
                $appName = basename($dir);

                // Get composer.json info if it exists
                $composerPath = $dir . '/composer.json';
                $laravelVersion = 'N/A';
                $phpVersion = 'N/A';

                if (File::exists($composerPath)) {
                    try {
                        // Limit file size to prevent timeout on very large files
                        $fileSize = File::size($composerPath);
                        if ($fileSize <= 1024 * 100) { // 100KB limit
                            $composerContent = File::get($composerPath);
                            $composerData = json_decode($composerContent, true);

                            if (json_last_error() === JSON_ERROR_NONE) {
                                $laravelVersion = $composerData['require']['laravel/framework'] ?? 'N/A';
                                $phpVersion = $composerData['require']['php'] ?? 'N/A';
                            }
                        } else {
                            $laravelVersion = 'Large file (>100KB)';
                            $phpVersion = 'Large file (>100KB)';
                        }
                    } catch (\Exception $e) {
                        $laravelVersion = 'Error reading';
                        $phpVersion = 'Error reading';
                    }
                }

                // Calculate port based on position in the list
                // Master app runs on 8000, so deployed apps start from 8001
                $allDirs = File::directories($this->applicationsDir);
                $appIndex = array_search($dir, $allDirs);
                $port = 8000 + $appIndex + 1; // Add 1 to start from 8001 instead of 8000

                // Check if app is running
                $isRunning = $this->isAppRunning($appName, $port);

                // Generate URL for the application
                $host = request()->getHost();
                if ($host === 'localhost' || $host === '127.0.0.1') {
                    $host = 'server.poudelbijaya.com.np';
                }
                $url = "http://{$host}:{$port}";

                $apps[] = [
                    'name' => $appName,
                    'path' => $dir,
                    'created_at' => File::lastModified($dir),
                    'laravel_version' => $laravelVersion,
                    'php_version' => $phpVersion,
                    'port' => $port,
                    'is_running' => $isRunning,
                    'url' => $url
                ];
            }
        }
        return $apps;
    }

    private function isAppRunning($appName, $port)
    {
        // Check if there's a process running on this port
        $command = "lsof -i :{$port} -t 2>/dev/null";
        exec($command, $output, $returnCode);

        return $returnCode === 0 && !empty($output);
    }
}