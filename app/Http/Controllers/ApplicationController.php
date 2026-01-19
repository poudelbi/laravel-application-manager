<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    protected $applicationsDir = '/var/www/html/applications';

    public function index()
    {
        // Clear any potential cache before retrieving applications
        $applications = $this->getApplications();

        // Enhance applications with composer.json and package.json info
        foreach ($applications as &$app) {
            $appPath = $app['path'];

            // Get composer.json info if it exists
            $composerPath = $appPath . '/composer.json';
            if (File::exists($composerPath)) {
                try {
                    // Limit file size to prevent timeout on very large files
                    $fileSize = File::size($composerPath);
                    if ($fileSize > 1024 * 100) { // 100KB limit
                        $app['composer_info'] = [
                            'name' => 'Large file',
                            'description' => 'File too large to parse (' . $fileSize . ' bytes)',
                            'version' => 'N/A',
                            'php_version' => 'N/A',
                            'laravel_version' => 'N/A',
                            'keywords' => [],
                            'license' => 'N/A',
                            'authors' => [],
                            'require' => [],
                            'require_dev' => [],
                        ];
                    } else {
                        $composerContent = File::get($composerPath);
                        $composerData = json_decode($composerContent, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            $app['composer_info'] = [
                                'name' => $composerData['name'] ?? 'N/A',
                                'description' => $composerData['description'] ?? 'No description',
                                'version' => $composerData['version'] ?? 'N/A',
                                'php_version' => $composerData['require']['php'] ?? 'N/A',
                                'laravel_version' => $composerData['require']['laravel/framework'] ?? 'N/A',
                                'keywords' => $composerData['keywords'] ?? [],
                                'license' => $composerData['license'] ?? 'N/A',
                                'authors' => $composerData['authors'] ?? [],
                                'require' => $composerData['require'] ?? [],
                                'require_dev' => $composerData['require-dev'] ?? [],
                            ];
                        } else {
                            $app['composer_info'] = [
                                'name' => 'N/A',
                                'description' => 'Invalid JSON format: ' . json_last_error_msg(),
                                'version' => 'N/A',
                                'php_version' => 'N/A',
                                'laravel_version' => 'N/A',
                                'keywords' => [],
                                'license' => 'N/A',
                                'authors' => [],
                                'require' => [],
                                'require_dev' => [],
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $app['composer_info'] = [
                        'name' => 'N/A',
                        'description' => 'Error reading file: ' . $e->getMessage(),
                        'version' => 'N/A',
                        'php_version' => 'N/A',
                        'laravel_version' => 'N/A',
                        'keywords' => [],
                        'license' => 'N/A',
                        'authors' => [],
                        'require' => [],
                        'require_dev' => [],
                    ];
                }
            } else {
                $app['composer_info'] = [
                    'name' => 'N/A',
                    'description' => 'No composer.json found at: ' . $composerPath,
                    'version' => 'N/A',
                    'php_version' => 'N/A',
                    'laravel_version' => 'N/A',
                    'keywords' => [],
                    'license' => 'N/A',
                    'authors' => [],
                    'require' => [],
                    'require_dev' => [],
                ];
            }

            // Get package.json info if it exists
            $packagePath = $appPath . '/package.json';
            if (File::exists($packagePath)) {
                try {
                    // Limit file size to prevent timeout on very large files
                    $fileSize = File::size($packagePath);
                    if ($fileSize > 1024 * 100) { // 100KB limit
                        $app['package_info'] = [
                            'name' => 'Large file',
                            'version' => 'N/A',
                            'description' => 'File too large to parse (' . $fileSize . ' bytes)',
                            'scripts' => [],
                            'keywords' => [],
                            'author' => 'N/A',
                            'license' => 'N/A',
                            'dependencies' => [],
                            'dev_dependencies' => [],
                        ];
                    } else {
                        $packageContent = File::get($packagePath);
                        $packageData = json_decode($packageContent, true);

                        if (json_last_error() === JSON_ERROR_NONE) {
                            $app['package_info'] = [
                                'name' => $packageData['name'] ?? 'N/A',
                                'version' => $packageData['version'] ?? 'N/A',
                                'description' => $packageData['description'] ?? 'No description',
                                'scripts' => $packageData['scripts'] ?? [],
                                'keywords' => $packageData['keywords'] ?? [],
                                'author' => $packageData['author'] ?? 'N/A',
                                'license' => $packageData['license'] ?? 'N/A',
                                'dependencies' => $packageData['dependencies'] ?? [],
                                'dev_dependencies' => $packageData['devDependencies'] ?? [],
                            ];
                        } else {
                            $app['package_info'] = [
                                'name' => 'N/A',
                                'version' => 'N/A',
                                'description' => 'Invalid JSON format: ' . json_last_error_msg(),
                                'scripts' => [],
                                'keywords' => [],
                                'author' => 'N/A',
                                'license' => 'N/A',
                                'dependencies' => [],
                                'dev_dependencies' => [],
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $app['package_info'] = [
                        'name' => 'N/A',
                        'version' => 'N/A',
                        'description' => 'Error reading file: ' . $e->getMessage(),
                        'scripts' => [],
                        'keywords' => [],
                        'author' => 'N/A',
                        'license' => 'N/A',
                        'dependencies' => [],
                        'dev_dependencies' => [],
                    ];
                }
            } else {
                $app['package_info'] = [
                    'name' => 'N/A',
                    'version' => 'N/A',
                    'description' => 'No package.json found at: ' . $packagePath,
                    'scripts' => [],
                    'keywords' => [],
                    'author' => 'N/A',
                    'license' => 'N/A',
                    'dependencies' => [],
                    'dev_dependencies' => [],
                ];
            }
        }

        return view('applications.index', compact('applications'));
    }

    public function create()
    {
        return view('applications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'required|in:8.0,8.1,8.2,8.3,8.4,9.0,9.1,10.0',
            'repo_url' => 'nullable|url',
            'access_token' => 'nullable|string|max:255'
        ]);

        $appName = $request->input('name');
        $version = $request->input('version');
        $repoUrl = $request->input('repo_url');
        $accessToken = $request->input('access_token');

        // Create application directory
        $appPath = $this->applicationsDir . '/' . $appName;

        if (File::exists($appPath)) {
            return redirect()->route('applications.index')->with('error', 'Application already exists!');
        }

        // Determine if we're creating from Git repo or generating a new Laravel app
        \Log::info("Attempting to create application: " . $appName);
        \Log::info("Repository URL: " . ($repoUrl ?: 'none'));
        \Log::info("Access token present: " . ($accessToken ? 'yes' : 'no'));

        if (!empty($repoUrl)) {
            // Clone from Git repository
            \Log::info("Starting git clone process...");
            $result = $this->cloneFromGit($appPath, $repoUrl, $accessToken);
            \Log::info("Git clone result: " . ($result ? 'success' : 'failed'));
        } else {
            // Create new Laravel application
            \Log::info("Creating new Laravel application...");
            $result = $this->createLaravelApp($appPath, $version);
            \Log::info("Laravel app creation result: " . ($result ? 'success' : 'failed'));
        }

        if ($result) {
            if (!empty($repoUrl)) {
                // If cloning from Git, redirect to setup page for additional configuration
                return redirect()->route('applications.setup', ['name' => $appName])->with('success', 'Application cloned from Git repository successfully!');
            } else {
                // If creating new Laravel app, go back to index
                return redirect()->route('applications.index')->with('success', 'New Laravel application created successfully!');
            }
        } else {
            \Log::error("Application creation failed for: " . $appName);
            return redirect()->route('applications.index')->with('error', 'Failed to create application!');
        }
    }

    public function setup($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('applications.index')->with('error', 'Application not found!');
        }

        return view('applications.setup', compact('name'));
    }

    public function storeSetup(Request $request, $name)
    {
        $request->validate([
            'environment' => 'nullable|string',
            'generate_key' => 'nullable|boolean',
            'php_version' => 'required|in:8.0,8.1,8.2,8.3,8.4',
            'run_commands' => 'array'
        ]);

        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('applications.index')->with('error', 'Application not found!');
        }

        // Update the .env file if provided
        if ($request->filled('environment')) {
            $envContent = $request->input('environment');
            File::put("$appPath/.env", $envContent);
        }

        // Generate application key if requested
        if ($request->has('generate_key') && $request->boolean('generate_key')) {
            chdir($appPath);
            exec('php artisan key:generate', $output, $returnCode);
            if ($returnCode !== 0) {
                \Log::error("Failed to generate application key: " . implode("\n", $output));
                return redirect()->back()->with('error', 'Failed to generate application key');
            }
        }

        // Run selected setup commands
        $commands = $request->input('run_commands', []);

        foreach ($commands as $command) {
            switch ($command) {
                case 'composer':
                    // Run composer install in the application directory
                    chdir($appPath);
                    exec('composer install', $output, $returnCode);
                    if ($returnCode !== 0) {
                        \Log::error("Failed to run composer install: " . implode("\n", $output));
                        return redirect()->back()->with('error', 'Failed to run composer install');
                    }
                    break;
                case 'migrate':
                    // Run migrations
                    chdir($appPath);
                    exec('php artisan migrate --force', $output, $returnCode);
                    if ($returnCode !== 0) {
                        \Log::error("Failed to run migrations: " . implode("\n", $output));
                        return redirect()->back()->with('error', 'Failed to run migrations');
                    }
                    break;
                case 'seed':
                    // Run database seeders
                    chdir($appPath);
                    exec('php artisan db:seed --force', $output, $returnCode);
                    if ($returnCode !== 0) {
                        \Log::error("Failed to run database seeders: " . implode("\n", $output));
                        return redirect()->back()->with('error', 'Failed to run database seeders');
                    }
                    break;
                case 'npm_install':
                    // Run npm install
                    chdir($appPath);
                    exec('npm install', $output, $returnCode);
                    if ($returnCode !== 0) {
                        \Log::error("Failed to run npm install: " . implode("\n", $output));
                        return redirect()->back()->with('error', 'Failed to run npm install');
                    }
                    break;
                case 'npm_build':
                    // Run npm run build
                    chdir($appPath);
                    exec('npm run build', $output, $returnCode);
                    if ($returnCode !== 0) {
                        \Log::error("Failed to run npm run build: " . implode("\n", $output));
                        return redirect()->back()->with('error', 'Failed to run npm run build');
                    }
                    break;
                case 'storage_link':
                    // Create storage symlink
                    chdir($appPath);
                    exec('php artisan storage:link', $output, $returnCode);
                    if ($returnCode !== 0) {
                        \Log::error("Failed to create storage symlink: " . implode("\n", $output));
                        return redirect()->back()->with('error', 'Failed to create storage symlink');
                    }
                    break;
            }
        }

        // Set proper permissions for storage and bootstrap/cache directories
        // Note: This requires proper server configuration for the web server to have necessary privileges
        $storagePath = $appPath . '/storage';
        $bootstrapCachePath = $appPath . '/bootstrap/cache';

        if (File::exists($storagePath)) {
            // Try to set permissions using the web server's user context
            exec("chgrp -R www-data " . escapeshellarg($storagePath) . " 2>&1", $output, $returnCode);
            if ($returnCode === 0) {
                exec("chmod -R ug+rwx " . escapeshellarg($storagePath) . " 2>&1", $output, $returnCode);
            }
            if ($returnCode !== 0) {
                \Log::warning("Could not set permissions for storage directory (may require manual setup): " . implode("\n", $output));
            }
        }

        if (File::exists($bootstrapCachePath)) {
            // Try to set permissions using the web server's user context
            exec("chgrp -R www-data " . escapeshellarg($bootstrapCachePath) . " 2>&1", $output, $returnCode);
            if ($returnCode === 0) {
                exec("chmod -R ug+rwx " . escapeshellarg($bootstrapCachePath) . " 2>&1", $output, $returnCode);
            }
            if ($returnCode !== 0) {
                \Log::warning("Could not set permissions for bootstrap/cache directory (may require manual setup): " . implode("\n", $output));
            }
        }

        return redirect()->route('applications.index')->with('success', 'Application setup completed successfully!');
    }

    public function destroy($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        try {
            if (File::exists($appPath)) {
                // Try to delete using sudo if configured (same approach as SitesController)
                $sudoPassword = config('app.sudo_password');

                if ($sudoPassword) {
                    // Use sudo to delete the directory
                    $command = "echo {$sudoPassword} | sudo -S rm -rf " . escapeshellarg($appPath);
                    exec($command, $output, $returnCode);

                    if ($returnCode !== 0) {
                        // If sudo fails, try regular deletion
                        File::deleteDirectory($appPath);
                    }
                } else {
                    // Use regular deletion
                    File::deleteDirectory($appPath);
                }

                // Clear any relevant cache to ensure page updates
                \Illuminate\Support\Facades\Cache::flush();

                return redirect()->route('applications.index')->with('success', 'Application deleted successfully!');
            } else {
                return redirect()->route('applications.index')->with('error', 'Application not found!');
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting application: ' . $e->getMessage());
            return redirect()->route('applications.index')->with('error', 'Error deleting application: ' . $e->getMessage());
        }
    }

    public function nginxConfig($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            abort(404, 'Application not found');
        }

        // Get application details for nginx config
        $composerPath = $appPath . '/composer.json';
        $packageName = 'laravel-app';
        $phpVersion = '8.2'; // Default

        if (File::exists($composerPath)) {
            try {
                $composerContent = File::get($composerPath);
                $composerData = json_decode($composerContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $packageName = $composerData['name'] ?? $name;
                    $req = $composerData['require'] ?? [];
                    if (isset($req['php'])) {
                        $phpReq = $req['php'];
                        // Extract version from requirements like "^8.1", ">=8.1", etc.
                        if (preg_match('/(\d+\.\d+)/', $phpReq, $matches)) {
                            $phpVersion = $matches[1];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Use defaults if there's an error reading composer.json
            }
        }

        // Generate nginx configuration
        $nginxConfig = $this->generateNginxConfig($name, $appPath, $packageName, $phpVersion);

        return response()->json([
            'name' => $name,
            'config' => $nginxConfig,
            'path' => $appPath,
            'php_version' => $phpVersion
        ]);
    }

    private function generateNginxConfig($appName, $appPath, $packageName, $phpVersion)
    {
        // Determine PHP-FPM socket based on PHP version
        $phpFpmSocket = "/run/php/php{$phpVersion}-fpm.sock";
        if (!File::exists($phpFpmSocket)) {
            // Fallback to common locations
            $versionsToTry = ['8.2', '8.1', '8.0', '7.4'];
            foreach ($versionsToTry as $ver) {
                $testSocket = "/run/php/php{$ver}-fpm.sock";
                if (File::exists($testSocket)) {
                    $phpFpmSocket = $testSocket;
                    $phpVersion = $ver;
                    break;
                }
            }
        }

        $domainName = str_replace(['_', ' '], '-', strtolower($appName)) . '.example.com';

        $config = "# Nginx configuration for {$packageName}\n";
        $config .= "# Generated on " . date('Y-m-d H:i:s') . "\n\n";
        $config .= "server {\n";
        $config .= "    listen 80;\n";
        $config .= "    server_name {$domainName};\n\n";
        $config .= "    root {$appPath}/public;\n";
        $config .= "    index index.php index.html index.htm;\n\n";
        $config .= "    # Handle Laravel routes\n";
        $config .= "    location / {\n";
        $config .= "        try_files \$uri \$uri/ /index.php?\$query_string;\n";
        $config .= "    }\n\n";
        $config .= "    # PHP processing\n";
        $config .= "    location ~ \.php\$ {\n";
        $config .= "        include snippets/fastcgi-php.conf;\n";
        $config .= "        fastcgi_pass unix:{$phpFpmSocket};\n";
        $config .= "        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;\n";
        $config .= "        include fastcgi_params;\n";
        $config .= "    }\n\n";
        $config .= "    # Deny access to sensitive files\n";
        $config .= "    location ~ /\.ht {\n";
        $config .= "        deny all;\n";
        $config .= "    }\n\n";
        $config .= "    # Deny access to Laravel sensitive directories\n";
        $config .= "    location ~ /(\\.env|\\.git|artisan|storage|bootstrap/cache|database) {\n";
        $config .= "        deny all;\n";
        $config .= "    }\n\n";
        $config .= "    # Optimize static file delivery\n";
        $config .= "    location ~* \\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {\n";
        $config .= "        expires 1y;\n";
        $config .= "        add_header Cache-Control \"public, immutable\";\n";
        $config .= "    }\n";
        $config .= "}\n\n";
        $config .= "# For SSL (uncomment and configure as needed)\n";
        $config .= "# server {\n";
        $config .= "#     listen 443 ssl http2;\n";
        $config .= "#     server_name {$domainName};\n";
        $config .= "#     root {$appPath}/public;\n";
        $config .= "#     index index.php index.html index.htm;\n\n";
        $config .= "#     ssl_certificate /path/to/certificate.crt;\n";
        $config .= "#     ssl_certificate_key /path/to/private.key;\n";
        $config .= "#     # ... rest of SSL config similar to above\n";
        $config .= "# }\n";

        return $config;
    }

    public function deploy($name)
    {
        $appPath = $this->applicationsDir . '/' . $name;

        if (!File::exists($appPath)) {
            return redirect()->route('applications.index')->with('error', 'Application not found!');
        }

        // Copy application to public directory for deployment
        $deployPath = '/var/www/html/' . $name;

        if (File::exists($deployPath)) {
            File::deleteDirectory($deployPath);
        }

        File::copyDirectory($appPath, $deployPath);

        // Set proper permissions
        $this->setPermissions($deployPath);

        return redirect()->route('applications.index')->with('success', 'Application deployed successfully!');
    }

    public function deployOfficialLaravel(Request $request)
    {
        // Get the application name from the request, default to timestamp-based name
        $appName = $request->input('name', 'official-laravel-' . time());
        $version = $request->input('version', '8.2');
        $repoUrl = $request->input('repo_url', 'https://github.com/laravel/laravel.git');
        $accessToken = $request->input('access_token');

        // Validate the application name to prevent directory traversal
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_-]+$/',
            'version' => 'required|in:8.0,8.1,8.2,8.3,8.4,9.0,9.1,10.0',
            'repo_url' => 'nullable|url',
            'access_token' => 'nullable|string|max:255'
        ]);

        $appPath = $this->applicationsDir . '/' . $appName;

        // Create the application directory using sudo if configured
        $sudoPassword = config('app.sudo_password');

        if ($sudoPassword) {
            $command = "echo " . escapeshellarg($sudoPassword) . " | sudo -S mkdir -p " . escapeshellarg($appPath);
            exec($command . " 2>&1", $output, $returnCode);

            if ($returnCode !== 0) {
                return redirect()->route('applications.index')->with('error', 'Failed to create application directory with sudo');
            }
        } else {
            // Create directory normally if no sudo password is configured
            File::makeDirectory($appPath, 0755, true);
        }

        // Clone the Laravel repository (official or custom)
        $sudoPassword = config('app.sudo_password');

        // Prepare the git command based on whether an access token is provided
        if ($accessToken) {
            // For private repositories, use the token in the URL
            $parsedUrl = parse_url($repoUrl);
            if (!$parsedUrl) {
                return redirect()->route('applications.index')->with('error', 'Invalid repository URL provided');
            }

            $secureRepoUrl = $parsedUrl['scheme'] . '://' . $accessToken . '@' . $parsedUrl['host'];

            if (isset($parsedUrl['port'])) {
                $secureRepoUrl .= ':' . $parsedUrl['port'];
            }

            $secureRepoUrl .= $parsedUrl['path'];

            if (isset($parsedUrl['query'])) {
                $secureRepoUrl .= '?' . $parsedUrl['query'];
            }

            if (isset($parsedUrl['fragment'])) {
                $secureRepoUrl .= '#' . $parsedUrl['fragment'];
            }

            if ($sudoPassword) {
                $command = "echo " . escapeshellarg($sudoPassword) . " | sudo -S git clone " . escapeshellarg($secureRepoUrl) . " " . escapeshellarg($appPath);
                exec($command . " 2>&1", $output, $returnCode);
            } else {
                $command = "git clone " . escapeshellarg($secureRepoUrl) . " " . escapeshellarg($appPath);
                exec($command . " 2>&1", $output, $returnCode);
            }
        } else {
            // For public repositories (including official Laravel)
            if ($sudoPassword) {
                $command = "echo " . escapeshellarg($sudoPassword) . " | sudo -S git clone " . escapeshellarg($repoUrl) . " " . escapeshellarg($appPath);
                exec($command . " 2>&1", $output, $returnCode);
            } else {
                $command = "git clone " . escapeshellarg($repoUrl) . " " . escapeshellarg($appPath);
                exec($command . " 2>&1", $output, $returnCode);
            }
        }

        if ($returnCode !== 0) {
            return redirect()->route('applications.index')->with('error', 'Failed to clone official Laravel repository');
        }

        // Set proper permissions for Laravel directories
        if ($sudoPassword) {
            // Change ownership of the entire application
            $command = "echo " . escapeshellarg($sudoPassword) . " | sudo -S chown -R www-data:www-data " . escapeshellarg($appPath);
            exec($command, $output, $returnCode);

            // Set specific permissions for Laravel directories that need write access
            $storagePath = $appPath . '/storage';
            $bootstrapCachePath = $appPath . '/bootstrap/cache';

            // Ensure storage and bootstrap/cache directories exist
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }
            if (!File::exists($bootstrapCachePath)) {
                File::makeDirectory($bootstrapCachePath, 0755, true);
            }

            // Set proper permissions for Laravel directories
            $command = "echo " . escapeshellarg($sudoPassword) . " | sudo -S chmod -R 775 " . escapeshellarg($storagePath);
            exec($command, $output, $returnCode);

            $command = "echo " . escapeshellarg($sudoPassword) . " | sudo -S chmod -R 775 " . escapeshellarg($bootstrapCachePath);
            exec($command, $output, $returnCode);
        } else {
            // Set permissions normally if no sudo password is configured
            $storagePath = $appPath . '/storage';
            $bootstrapCachePath = $appPath . '/bootstrap/cache';

            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }
            if (!File::exists($bootstrapCachePath)) {
                File::makeDirectory($bootstrapCachePath, 0755, true);
            }

            // Set proper permissions for Laravel directories
            chmod($storagePath, 0775);
            chmod($bootstrapCachePath, 0775);
        }

        // Change to the application directory
        chdir($appPath);

        // Create a basic .env file
        $envContent = "APP_NAME=Laravel\n";
        $envContent .= "APP_ENV=local\n";
        $envContent .= "APP_KEY=\n";
        $envContent .= "APP_DEBUG=true\n";
        $envContent .= "APP_URL=http://localhost\n\n";
        $envContent .= "DB_CONNECTION=sqlite\n";
        $envContent .= "DB_DATABASE=" . escapeshellarg($appPath . '/database/database.sqlite') . "\n";

        File::put($appPath . '/.env', $envContent);

        // Run composer install first
        exec('composer install', $output, $returnCode);

        if ($returnCode !== 0) {
            return redirect()->route('applications.index')->with('error', 'Failed to install composer dependencies');
        }

        // Generate the app key after composer install
        exec('php artisan key:generate', $output, $returnCode);

        if ($returnCode !== 0) {
            return redirect()->route('applications.index')->with('error', 'Failed to generate application key');
        }

        // Create the database file if using SQLite
        $dbPath = $appPath . '/database/database.sqlite';
        if (!File::exists($dbPath)) {
            File::put($dbPath, '');
        }

        // Run migrations
        exec('php artisan migrate --force', $output, $returnCode);

        if ($returnCode !== 0) {
            return redirect()->route('applications.index')->with('error', 'Failed to run migrations');
        }

        // Run database seeders
        exec('php artisan db:seed --force', $output, $returnCode);

        if ($returnCode !== 0) {
            return redirect()->route('applications.index')->with('error', 'Failed to run database seeders');
        }

        // Install and build frontend assets if package.json exists
        if (File::exists($appPath . '/package.json')) {
            \Log::info("Building frontend assets for application: {$appName}");

            // Run npm install first
            exec('npm install 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                \Log::warning("npm install failed for application {$appName}, continuing deployment: " . implode("\n", $output));
                // Continue deployment even if npm install fails, as it might be due to platform-specific issues
            } else {
                \Log::info("npm install completed successfully for application: {$appName}");

                // Run npm run build if build script exists in package.json
                $packageJsonPath = $appPath . '/package.json';
                if (File::exists($packageJsonPath)) {
                    $packageJson = json_decode(File::get($packageJsonPath), true);
                    if (isset($packageJson['scripts']['build'])) {
                        exec('npm run build 2>&1', $output, $returnCode);

                        if ($returnCode !== 0) {
                            \Log::warning("npm run build failed for application {$appName}: " . implode("\n", $output));
                        } else {
                            \Log::info("npm run build completed successfully for application: {$appName}");
                        }
                    } else {
                        \Log::info("No build script found in package.json for application: {$appName}");
                    }
                }
            }
        } else {
            \Log::info("No package.json found for application: {$appName}, skipping frontend asset compilation");
        }

        return redirect()->route('applications.index')->with('success', "Laravel application '{$appName}' deployed with test migration, seeding, and frontend asset compilation!");
    }

    public function getApplications()
    {
        $apps = [];
        if (File::exists($this->applicationsDir)) {
            $directories = File::directories($this->applicationsDir);
            foreach ($directories as $dir) {
                $appName = basename($dir);
                $apps[] = [
                    'name' => $appName,
                    'path' => $dir,
                    'created_at' => File::lastModified($dir)
                ];
            }
        }
        return $apps;
    }

    private function createLaravelApp($path, $version)
    {
        try {
            // Create a basic Laravel app structure
            File::makeDirectory($path, 0755, true);
            
            // Create basic files
            File::makeDirectory("$path/app", 0755, true);
            File::makeDirectory("$path/config", 0755, true);
            File::makeDirectory("$path/database", 0755, true);
            File::makeDirectory("$path/resources", 0755, true);
            File::makeDirectory("$path/routes", 0755, true);
            File::makeDirectory("$path/public", 0755, true);
            File::makeDirectory("$path/storage", 0755, true);
            
            // Create basic composer.json
            $composerJson = json_encode([
                'name' => 'laravel/application',
                'type' => 'project',
                'require' => [
                    'php' => '^8.0',
                    'laravel/framework' => '^10.0',
                    'laravel/tinker' => '^2.8'
                ],
                'autoload' => [
                    'psr-4' => [
                        'App\\' => 'app/',
                        'Database\\Factories\\' => 'database/factories/',
                        'Database\\Seeders\\' => 'database/seeders/'
                    ]
                ]
            ], JSON_PRETTY_PRINT);
            
            File::put("$path/composer.json", $composerJson);
            
            // Create basic .env file
            File::put("$path/.env", "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n");
            
            // Create basic app/Http/Controllers/HomeController.php
            File::makeDirectory("$path/app/Http/Controllers", 0755, true);
            File::put("$path/app/Http/Controllers/HomeController.php", "<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return '<h1>Welcome to ' . basename($path) . '</h1>';
    }
}");

            // Create basic routes/web.php
            File::put("$path/routes/web.php", "<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);
");

            // Create basic public/index.php
            File::put("$path/public/index.php", "<?php

require_once '../vendor/autoload.php';

\$app = require_once '../bootstrap/app.php';

\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);

\$response = \$kernel->handle(
    \$request = Illuminate\Http\Request::capture()
);

\$response->send();

\$kernel->terminate(\$request, \$response);
");

            $this->setPermissions($path);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function cloneFromGit($path, $repoUrl, $accessToken = null)
    {
        try {
            // Create the directory
            File::makeDirectory($path, 0755, true);

            // Prepare the git command
            if ($accessToken) {
                // For private repositories, use the token in the URL
                $parsedUrl = parse_url($repoUrl);
                if (!$parsedUrl) {
                    \Log::error("Invalid repository URL: " . $repoUrl);
                    return false;
                }

                $secureRepoUrl = $parsedUrl['scheme'] . '://' . $accessToken . '@' . $parsedUrl['host'];

                if (isset($parsedUrl['port'])) {
                    $secureRepoUrl .= ':' . $parsedUrl['port'];
                }

                $secureRepoUrl .= $parsedUrl['path'];

                if (isset($parsedUrl['query'])) {
                    $secureRepoUrl .= '?' . $parsedUrl['query'];
                }

                if (isset($parsedUrl['fragment'])) {
                    $secureRepoUrl .= '#' . $parsedUrl['fragment'];
                }

                $command = "git clone " . escapeshellarg($secureRepoUrl) . " " . escapeshellarg($path);
            } else {
                // For public repositories
                $command = "git clone " . escapeshellarg($repoUrl) . " " . escapeshellarg($path);
            }

            // Execute the git clone command
            $output = [];
            $errors = [];
            $returnCode = 0;

            // Capture both stdout and stderr
            $fullCommand = $command . ' 2>&1';
            exec($fullCommand, $output, $returnCode);

            \Log::info("Git command executed: " . $command);
            \Log::info("Return code: " . $returnCode);
            \Log::info("Output: " . implode("\n", $output));

            if ($returnCode !== 0) {
                // Clean up the directory if clone failed
                File::deleteDirectory($path);
                \Log::error("Git clone failed with return code: " . $returnCode . ", output: " . implode("\n", $output));
                return false;
            }

            // Set proper permissions after cloning
            $this->setPermissions($path);

            return true;
        } catch (\Exception $e) {
            // Clean up the directory if anything went wrong
            if (File::exists($path)) {
                File::deleteDirectory($path);
            }
            \Log::error("Exception during git clone: " . $e->getMessage());
            return false;
        }
    }

    private function setPermissions($path)
    {
        // Set proper permissions for Laravel application
        $dirs = [
            "$path/storage",
            "$path/bootstrap/cache"
        ];

        foreach ($dirs as $dir) {
            if (File::exists($dir)) {
                File::chmod($dir, 0775);
            }
        }
    }
}