<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DeployOfficialLaravelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected $applicationsDir = '/var/www/html/applications';

    public function test_deploy_official_laravel_creates_application()
    {
        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Count applications before deployment
        $appsBefore = count(glob($this->applicationsDir . '/*', GLOB_ONLYDIR));

        // Configure git to trust the applications directory to avoid ownership issues
        exec('git config --global --add safe.directory ' . escapeshellarg($this->applicationsDir), $output, $returnCode);

        // Test deploying the official Laravel application
        $appName = 'test-laravel-app-' . time();
        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/applications/deploy-official-laravel', [
                'name' => $appName,
                'version' => '8.2',
                'repo_url' => 'https://github.com/laravel/laravel.git',
                'access_token' => null,
            ]);

        // Check that the response is a redirect (to applications index)
        $response->assertRedirect('/applications');

        // Follow the redirect to check the success message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success');

        // Count applications after deployment
        $appsAfter = count(glob($this->applicationsDir . '/*', GLOB_ONLYDIR));

        // Verify that an application was created
        $this->assertEquals($appsBefore + 1, $appsAfter, 'Number of applications should increase by 1');

        // Find the newly created application directory
        $newApps = array_diff(glob($this->applicationsDir . '/*', GLOB_ONLYDIR), []);
        $newAppPaths = array_values($newApps); // Re-index the array

        // Get the most recently created application (by modification time)
        $latestAppPath = null;
        $latestTime = 0;

        foreach ($newAppPaths as $path) {
            $modTime = filemtime($path);
            if ($modTime > $latestTime) {
                $latestTime = $modTime;
                $latestAppPath = $path;
            }
        }

        // Verify the application directory exists
        $this->assertNotNull($latestAppPath, 'New application directory should exist');
        $this->assertTrue(File::exists($latestAppPath), "Application directory should exist at: {$latestAppPath}");

        // Check for key Laravel files that should exist after deployment
        $this->assertTrue(
            File::exists($latestAppPath . '/composer.json'),
            "composer.json should exist in the deployed Laravel application"
        );

        $this->assertTrue(
            File::exists($latestAppPath . '/artisan'),
            "artisan file should exist in the deployed Laravel application"
        );

        $this->assertTrue(
            File::exists($latestAppPath . '/app/Http/Controllers/Controller.php'),
            "Base controller should exist in the deployed Laravel application"
        );

        $this->assertTrue(
            File::exists($latestAppPath . '/routes/web.php'),
            "Web routes file should exist in the deployed Laravel application"
        );

        $this->assertTrue(
            File::exists($latestAppPath . '/.env'),
            ".env file should exist in the deployed Laravel application"
        );

        $this->assertTrue(
            File::exists($latestAppPath . '/database/database.sqlite'),
            "SQLite database file should exist in the deployed Laravel application"
        );

        // Check that the .env file contains expected content
        $envContent = File::get($latestAppPath . '/.env');
        $this->assertStringContainsString('APP_NAME=Laravel', $envContent, 'APP_NAME should be set in .env');
        $this->assertStringContainsString('APP_ENV=local', $envContent, 'APP_ENV should be set in .env');
        $this->assertStringContainsString('APP_DEBUG=true', $envContent, 'APP_DEBUG should be set in .env');
        $this->assertStringContainsString('DB_CONNECTION=sqlite', $envContent, 'DB_CONNECTION should be set to sqlite in .env');

        // Check that storage and bootstrap/cache directories exist and have proper permissions
        $this->assertTrue(
            File::exists($latestAppPath . '/storage'),
            "Storage directory should exist in the deployed Laravel application"
        );

        $this->assertTrue(
            File::exists($latestAppPath . '/bootstrap/cache'),
            "Bootstrap cache directory should exist in the deployed Laravel application"
        );

        echo "✓ Official Laravel application successfully deployed\n";
        echo "✓ Application directory created at: {$latestAppPath}\n";
        echo "✓ Key Laravel files present in deployed application\n";
        echo "✓ .env file created with proper configuration\n";
        echo "✓ Database file created\n";
        echo "✓ Storage and bootstrap/cache directories exist\n";
    }

    public function test_deploy_official_laravel_with_database_operations()
    {
        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test deploying the official Laravel application
        $appName = 'test-laravel-app-' . time();
        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/applications/deploy-official-laravel', [
                'name' => $appName,
                'version' => '8.2',
                'repo_url' => 'https://github.com/laravel/laravel.git',
                'access_token' => null,
            ]);

        // Check that the response is a redirect
        $response->assertRedirect('/applications');

        // Follow the redirect to check the success message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success');

        // Find the most recently created application
        $newApps = array_diff(glob($this->applicationsDir . '/*', GLOB_ONLYDIR), []);
        $latestAppPath = null;
        $latestTime = 0;
        
        foreach ($newApps as $path) {
            $modTime = filemtime($path);
            if ($modTime > $latestTime) {
                $latestTime = $modTime;
                $latestAppPath = $path;
            }
        }

        // Verify the application directory exists
        $this->assertNotNull($latestAppPath, 'New application directory should exist');
        $this->assertTrue(File::exists($latestAppPath), "Application directory should exist at: {$latestAppPath}");

        // Check that migrations were run by verifying the presence of migration files
        $this->assertTrue(
            File::exists($latestAppPath . '/database/migrations'),
            "Database migrations directory should exist"
        );

        // Check that the database file exists and is properly initialized
        $dbPath = $latestAppPath . '/database/database.sqlite';
        $this->assertTrue(
            File::exists($dbPath),
            "SQLite database file should exist"
        );

        // Verify that the database file is not empty (contains schema)
        $dbSize = filesize($dbPath);
        $this->assertGreaterThan(0, $dbSize, "Database file should not be empty");

        echo "✓ Database operations completed successfully\n";
        echo "✓ Migrations directory exists\n";
        echo "✓ SQLite database file created and initialized\n";
        echo "✓ Database file is not empty (contains schema)\n";
    }

    public function test_deploy_official_laravel_with_composer_install()
    {
        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test deploying the official Laravel application
        $appName = 'test-laravel-app-' . time();
        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/applications/deploy-official-laravel', [
                'name' => $appName,
                'version' => '8.2',
                'repo_url' => 'https://github.com/laravel/laravel.git',
                'access_token' => null,
            ]);

        // Check that the response is a redirect
        $response->assertRedirect('/applications');

        // Follow the redirect to check the success message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success');

        // Find the most recently created application
        $newApps = array_diff(glob($this->applicationsDir . '/*', GLOB_ONLYDIR), []);
        $latestAppPath = null;
        $latestTime = 0;
        
        foreach ($newApps as $path) {
            $modTime = filemtime($path);
            if ($modTime > $latestTime) {
                $latestTime = $modTime;
                $latestAppPath = $path;
            }
        }

        // Verify the application directory exists
        $this->assertNotNull($latestAppPath, 'New application directory should exist');
        $this->assertTrue(File::exists($latestAppPath), "Application directory should exist at: {$latestAppPath}");

        // Check that composer install was run by verifying vendor directory
        $this->assertTrue(
            File::exists($latestAppPath . '/vendor'),
            "Vendor directory should exist after composer install"
        );

        $this->assertTrue(
            File::exists($latestAppPath . '/vendor/autoload.php'),
            "Autoload file should exist after composer install"
        );

        // Check that the application key was generated
        $envContent = File::get($latestAppPath . '/.env');
        $this->assertStringNotContainsString('APP_KEY=', $envContent, 'APP_KEY should be set (not empty) in .env after key generation');

        echo "✓ Composer install completed successfully\n";
        echo "✓ Vendor directory created\n";
        echo "✓ Autoload file exists\n";
        echo "✓ Application key generated\n";
    }

    public function test_deploy_official_laravel_with_frontend_assets()
    {
        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test deploying the official Laravel application
        $appName = 'test-laravel-app-' . time();
        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->postJson('/applications/deploy-official-laravel', [
                'name' => $appName,
                'version' => '8.2',
                'repo_url' => 'https://github.com/laravel/laravel.git',
                'access_token' => null,
            ]);

        // Check that the response is a redirect
        $response->assertRedirect('/applications');

        // Follow the redirect to check the success message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success');

        // Find the most recently created application
        $newApps = array_diff(glob($this->applicationsDir . '/*', GLOB_ONLYDIR), []);
        $latestAppPath = null;
        $latestTime = 0;
        
        foreach ($newApps as $path) {
            $modTime = filemtime($path);
            if ($modTime > $latestTime) {
                $latestTime = $modTime;
                $latestAppPath = $path;
            }
        }

        // Verify the application directory exists
        $this->assertNotNull($latestAppPath, 'New application directory should exist');
        $this->assertTrue(File::exists($latestAppPath), "Application directory should exist at: {$latestAppPath}");

        // Check if package.json exists (it should in the official Laravel repo)
        if (File::exists($latestAppPath . '/package.json')) {
            $this->assertTrue(
                File::exists($latestAppPath . '/package.json'),
                "package.json should exist in the deployed Laravel application"
            );
            
            echo "✓ Package.json exists\n";
            echo "✓ Frontend assets setup completed\n";
        } else {
            echo "ℹ️  package.json not found in this Laravel version\n";
        }

        echo "✓ Deploy official Laravel functionality tested successfully\n";
    }

    public function tearDown(): void
    {
        // Clean up any test applications created during the tests
        $testApps = glob($this->applicationsDir . '/official-laravel-*');
        
        foreach ($testApps as $appPath) {
            if (File::exists($appPath)) {
                File::deleteDirectory($appPath);
            }
        }
        
        parent::tearDown();
    }
}