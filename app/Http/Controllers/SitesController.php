<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SitesController extends Controller
{
    protected $applicationsDir;
    protected $startingPort = 8000;

    public function __construct()
    {
        // Use application directory from config
        $this->applicationsDir = config('app.applications_dir', '/var/www/html/applications');
    }

    public function index()
    {
        try {
            $applications = $this->getApplications();
            $sites = $this->getSites($applications);

            return view('sites.index', compact('sites', 'applications'));
        } catch (\Exception $e) {
            \Log::error('Error in SitesController@index: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'Error loading sites: ' . $e->getMessage());
        }
    }

    private function getApplications()
    {
        $apps = [];
        try {
            if (File::exists($this->applicationsDir)) {
                $directories = File::directories($this->applicationsDir);
                foreach ($directories as $dir) {
                    $appName = basename($dir);

                    // Get composer.json info if it exists (but don't read the file if it's too large)
                    $composerPath = $dir . '/composer.json';
                    $laravelVersion = 'N/A';
                    $phpVersion = 'N/A';

                    if (File::exists($composerPath)) {
                        try {
                            // Only read file if it's reasonably sized
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
                            // Handle error
                            $laravelVersion = 'Error reading: ' . $e->getMessage();
                            $phpVersion = 'Error reading: ' . $e->getMessage();
                        }
                    }

                    $apps[] = [
                        'name' => $appName,
                        'path' => $dir,
                        'created_at' => File::lastModified($dir),
                        'laravel_version' => $laravelVersion,
                        'php_version' => $phpVersion,
                        'is_running' => $this->isAppRunning($appName),
                        'port' => $this->getAppPort($appName),
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error in SitesController@getApplications: ' . $e->getMessage());
            // Return empty array if there's an error
            return [];
        }
        return $apps;
    }

    private function getSites($applications)
    {
        $sites = [];

        foreach ($applications as $app) {
            $siteInfo = [
                'name' => $app['name'],
                'path' => $app['path'],
                'created_at' => $app['created_at'],
                'laravel_version' => $app['laravel_version'],
                'php_version' => $app['php_version'],
                'is_running' => $app['is_running'],
                'port' => $app['port'] ?? $this->getAppPort($app['name']), // Calculate port if not provided
                'url' => $this->getAppUrl($app['name'], $this->getAppPort($app['name'])),
            ];

            $sites[] = $siteInfo;
        }

        return $sites;
    }

    private function getAppPort($appName)
    {
        // Calculate port based on position in the directory list
        // Master app runs on 8000, so deployed apps start from 8001
        if (!File::exists($this->applicationsDir)) {
            return $this->startingPort + 1; // Start from 8001 if directory doesn't exist
        }

        $directories = File::directories($this->applicationsDir);
        $appIndex = 0;

        foreach ($directories as $index => $dir) {
            if (basename($dir) === $appName) {
                $appIndex = $index;
                break;
            }
        }

        return $this->startingPort + $appIndex + 1; // Add 1 to start from 8001 instead of 8000
    }

    private function getAppUrl($appName, $port)
    {
        // Use the server's host instead of localhost
        $host = request()->getHost();
        if ($host === 'localhost' || $host === '127.0.0.1') {
            // If accessing locally, use the server's actual domain
            $host = 'server.poudelbijaya.com.np';
        }
        return "http://{$host}:{$port}/";
    }

    private function isAppRunning($appName)
    {
        $port = $this->getAppPort($appName);
        // Check if the application is running on its port
        // This is a simplified check - in a real scenario, you'd check if the process is running
        $cacheKey = 'app_running_' . $appName;
        return Cache::has($cacheKey);
    }

    public function start($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('sites.index')->with('error', 'Application not found!');
        }

        $port = $this->getAppPort($name);

        try {
            // Start the Laravel development server for this application using sudo if configured
            $sudoPassword = config('app.sudo_password');

            if ($sudoPassword) {
                // Use sudo to start the server
                $command = "echo {$sudoPassword} | sudo -S -E bash -c 'cd " . escapeshellarg($appPath) . " && php artisan serve --host=0.0.0.0 --port=" . $port . " > /dev/null 2>&1 &'";
            } else {
                // Use regular command without sudo
                $command = "cd " . escapeshellarg($appPath) . " && php artisan serve --host=0.0.0.0 --port=" . $port . " > /dev/null 2>&1 &";
            }

            // Execute the command to start the server
            exec($command, $output, $returnCode);

            if ($returnCode === 0 || $returnCode === 1) { // 1 is returned by background processes
                // Store in cache that the app is running
                Cache::put('app_running_' . $name, true, now()->addHours(24)); // Cache for 24 hours

                return redirect()->route('sites.index')->with('success', "Application '{$name}' started on port {$port}!");
            } else {
                return redirect()->route('sites.index')->with('error', "Failed to start application '{$name}' on port {$port}");
            }
        } catch (\Exception $e) {
            \Log::error('Error starting application: ' . $e->getMessage());
            return redirect()->route('sites.index')->with('error', "Error starting application '{$name}': " . $e->getMessage());
        }
    }

    public function stop($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('sites.index')->with('error', 'Application not found!');
        }

        $port = $this->getAppPort($name);

        try {
            // Kill the process running on this port using sudo if configured
            $sudoPassword = config('app.sudo_password');
            if ($sudoPassword) {
                $command = "echo {$sudoPassword} | sudo -S lsof -i :{$port} -t | xargs sudo -S kill -9 2>/dev/null || true";
            } else {
                $command = "lsof -i :{$port} -t | xargs kill -9 2>/dev/null || true";
            }

            exec($command, $output, $returnCode);

            // Remove from cache
            Cache::forget('app_running_' . $name);

            return redirect()->route('sites.index')->with('success', "Application '{$name}' stopped on port {$port}!");
        } catch (\Exception $e) {
            \Log::error('Error stopping application: ' . $e->getMessage());
            return redirect()->route('sites.index')->with('error', "Error stopping application '{$name}': " . $e->getMessage());
        }
    }

    public function update($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('sites.index')->with('error', 'Application not found!');
        }

        // Check if it's a Git repository
        if (File::exists($appPath . '/.git')) {
            // Pull latest changes using sudo if configured
            $sudoPassword = config('app.sudo_password');
            chdir($appPath);
            $output = [];
            $returnCode = 0;

            if ($sudoPassword) {
                $command = "echo {$sudoPassword} | sudo -S git pull origin HEAD";
                exec($command, $output, $returnCode);
            } else {
                exec('git pull origin HEAD', $output, $returnCode);
            }

            if ($returnCode === 0) {
                // Run composer update if composer.json exists
                if (File::exists($appPath . '/composer.json')) {
                    if ($sudoPassword) {
                        exec("echo {$sudoPassword} | sudo -S composer install", $output, $returnCode);
                    } else {
                        exec('composer install', $output, $returnCode);
                    }
                    if ($returnCode !== 0) {
                        return redirect()->route('sites.index')->with('error', 'Application updated but composer install failed');
                    }
                }

                // Run npm update if package.json exists
                if (File::exists($appPath . '/package.json')) {
                    if ($sudoPassword) {
                        exec("echo {$sudoPassword} | sudo -S npm install", $output, $returnCode);
                    } else {
                        exec('npm install', $output, $returnCode);
                    }
                    if ($returnCode !== 0) {
                        return redirect()->route('sites.index')->with('error', 'Application updated but npm install failed');
                    }
                }

                return redirect()->route('sites.index')->with('success', 'Application updated successfully!');
            } else {
                return redirect()->route('sites.index')->with('error', 'Failed to update application from Git');
            }
        } else {
            return redirect()->route('sites.index')->with('error', 'Application is not a Git repository, cannot update');
        }
    }

    public function createNginxConfig($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('sites.index')->with('error', 'Application not found!');
        }

        try {
            // Generate nginx configuration for the application
            $domainName = $name . '.test'; // You can customize this as needed
            $nginxConfig = $this->generateNginxConfig($name, $appPath, $domainName);

            // Define nginx configuration file paths
            $nginxConfPath = "/etc/nginx/sites-available/{$name}";
            $nginxEnabledPath = "/etc/nginx/sites-enabled/{$name}";

            // Write the nginx configuration to sites-available
            $sudoPassword = config('app.sudo_password');

            if ($sudoPassword) {
                // Use sudo to write the nginx configuration
                $tempFilePath = "/tmp/{$name}_nginx.conf";
                file_put_contents($tempFilePath, $nginxConfig);

                // Move the file to the nginx directory using sudo
                $command = "echo {$sudoPassword} | sudo -S mv " . escapeshellarg($tempFilePath) . " " . escapeshellarg($nginxConfPath);
                exec($command, $output, $returnCode);

                if ($returnCode !== 0) {
                    // If moving the file fails, try writing directly with sudo
                    $escapedConfig = escapeshellarg($nginxConfig);
                    $command = "echo {$sudoPassword} | sudo -S tee " . escapeshellarg($nginxConfPath);
                    $process = proc_open("echo {$sudoPassword} | sudo -S tee " . escapeshellarg($nginxConfPath), [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ], $pipes);

            if (is_resource($process)) {
                fwrite($pipes[0], $nginxConfig);
                fclose($pipes[0]);

                $stdout = stream_get_contents($pipes[1]);
                $stderr = stream_get_contents($pipes[2]);

                fclose($pipes[1]);
                fclose($pipes[2]);

                $returnCode = proc_close($process);
            }
                }
            } else {
                // Write without sudo if no password is configured
                file_put_contents($nginxConfPath, $nginxConfig);
            }

            // Create symlink to enable the site
            if ($sudoPassword) {
                $command = "echo {$sudoPassword} | sudo -S ln -sf " . escapeshellarg($nginxConfPath) . " " . escapeshellarg($nginxEnabledPath);
                exec($command, $output, $returnCode);
            } else {
                if (!file_exists(dirname($nginxEnabledPath))) {
                    mkdir(dirname($nginxEnabledPath), 0755, true);
                }
                symlink($nginxConfPath, $nginxEnabledPath);
            }

            // Reload nginx to apply the new configuration
            if ($sudoPassword) {
                $command = "echo {$sudoPassword} | sudo -S nginx -s reload";
                exec($command, $output, $returnCode);
            } else {
                exec('nginx -s reload 2>/dev/null || true', $output, $returnCode);
            }

            \Log::info("Nginx configuration created for application: {$name}");

            return redirect()->route('sites.index')->with('success', 'Nginx configuration created successfully!');
        } catch (\Exception $e) {
            \Log::error('Error creating nginx configuration: ' . $e->getMessage());
            return redirect()->route('sites.index')->with('error', 'Error creating nginx configuration: ' . $e->getMessage());
        }
    }

    private function generateNginxConfig($appName, $appPath, $domainName)
    {
        $config = "server {\n";
        $config .= "    listen 80;\n";
        $config .= "    server_name {$domainName};\n";
        $config .= "    root {$appPath}/public;\n";
        $config .= "    index index.php index.html index.htm;\n\n";
        $config .= "    charset utf-8;\n\n";
        $config .= "    location / {\n";
        $config .= "        try_files \$uri \$uri/ /index.php?\$query_string;\n";
        $config .= "    }\n\n";
        $config .= "    location ~ \.php\$ {\n";
        $config .= "        include snippets/fastcgi-php.conf;\n";
        $config .= "        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Adjust PHP version as needed\n";
        $config .= "        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;\n";
        $config .= "        include fastcgi_params;\n";
        $config .= "    }\n\n";
        $config .= "    location ~ /\.(?!well-known).* {\n";
        $config .= "        deny all;\n";
        $config .= "    }\n";
        $config .= "}\n";

        return $config;
    }

    public function refresh($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('sites.index')->with('error', 'Application not found!');
        }

        // Clear caches
        chdir($appPath);

        // Clear Laravel caches using sudo if configured
        $sudoPassword = config('app.sudo_password');

        if ($sudoPassword) {
            // Use sudo to run artisan commands
            exec("echo {$sudoPassword} | sudo -S php artisan config:clear", $output, $returnCode);
            exec("echo {$sudoPassword} | sudo -S php artisan cache:clear", $output, $returnCode);
            exec("echo {$sudoPassword} | sudo -S php artisan view:clear", $output, $returnCode);
            exec("echo {$sudoPassword} | sudo -S php artisan route:clear", $output, $returnCode);
        } else {
            // Clear Laravel caches normally
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
        }

        return redirect()->route('sites.index')->with('success', 'Application refreshed successfully!');
    }

    public function destroy($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        try {
            if (!File::exists($appPath)) {
                return redirect()->route('sites.index')->with('error', 'Application not found!');
            }

            // Stop the application if it's running
            if ($this->isAppRunning($name)) {
                $port = $this->getAppPort($name);
                $command = "lsof -i :{$port} -t | xargs kill -9 2>/dev/null || true";
                exec($command, $output, $returnCode);

                // Remove from cache
                Cache::forget('app_running_' . $name);
            }

            // Remove nginx configuration for this application
            $nginxConfPath = "/etc/nginx/sites-available/{$name}";
            $nginxEnabledPath = "/etc/nginx/sites-enabled/{$name}";

            // Try to remove nginx configuration (requires sudo)
            $sudoPassword = config('app.sudo_password');

            if ($sudoPassword && (File::exists($nginxConfPath) || File::exists($nginxEnabledPath))) {
                // Remove from sites-enabled first
                $command = "echo {$sudoPassword} | sudo -S rm -f " . escapeshellarg($nginxEnabledPath);
                exec($command, $output, $returnCode);

                // Remove from sites-available
                $command = "echo {$sudoPassword} | sudo -S rm -f " . escapeshellarg($nginxConfPath);
                exec($command, $output, $returnCode);

                // Reload nginx to apply changes
                $command = "echo {$sudoPassword} | sudo -S nginx -s reload";
                exec($command, $output, $returnCode);

                \Log::info("Nginx configuration removed for application: {$name}");
            } elseif (File::exists($nginxConfPath) || File::exists($nginxEnabledPath)) {
                // If no sudo password is configured, just try to remove the files
                File::delete($nginxConfPath);
                File::delete($nginxEnabledPath);

                // Try to reload nginx if possible
                try {
                    exec('nginx -s reload 2>/dev/null || true', $output, $returnCode);
                } catch (\Exception $e) {
                    \Log::warning("Could not reload nginx: " . $e->getMessage());
                }
            }

            // For the Sites controller, we typically don't delete the application directory
            // The Sites controller is focused on nginx configuration management
            // The application directory should remain for the Applications controller to manage

            // Clear any relevant cache to ensure page updates
            \Illuminate\Support\Facades\Cache::flush();

            return redirect()->route('sites.index')->with('success', 'Nginx configuration removed successfully!');
        } catch (\Exception $e) {
            \Log::error('Error removing nginx configuration: ' . $e->getMessage());
            return redirect()->route('sites.index')->with('error', 'Error removing nginx configuration: ' . $e->getMessage());
        }
    }

}