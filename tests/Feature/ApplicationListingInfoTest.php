<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ApplicationListingInfoTest extends TestCase
{
    use RefreshDatabase;

    protected $applicationsDir = '/var/www/html/applications';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we're in testing environment
        config(['app.env' => 'testing']);
    }

    /**
     * Test that composer.json and package.json information is displayed in application listing
     */
    public function test_application_listing_shows_composer_and_package_info()
    {
        $appName = 'test-app-info-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application with composer.json and package.json
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json
        $composerJson = '{
            "name": "test/test-app",
            "description": "A test Laravel application",
            "version": "1.0.0",
            "require": {
                "php": "^8.0",
                "laravel/framework": "^9.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);
        
        // Create a sample package.json
        $packageJson = '{
            "name": "test-app",
            "version": "1.0.0",
            "description": "Test application frontend",
            "scripts": {
                "dev": "vite",
                "build": "vite build"
            }
        }';
        File::put($appPath . '/package.json', $packageJson);
        
        // Create a dummy .git file to simulate a git repository
        File::put($appPath . '/.git', '');

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Access the applications index page
        $response = $this->actingAs($user)
            ->get('/applications');

        $response->assertStatus(200);
        
        // Check that the application name is displayed
        $response->assertSee($appName);
        
        // Check that composer.json information is displayed
        $response->assertSee('Composer Info:');
        $response->assertSee('test/test-app'); // composer name
        $response->assertSee('A test Laravel application'); // composer description
        $response->assertSee('1.0.0'); // composer version
        $response->assertSee('^8.0'); // php version
        $response->assertSee('^9.0'); // laravel version
        
        // Check that package.json information is displayed
        $response->assertSee('Package Info:');
        $response->assertSee('test-app'); // package name
        $response->assertSee('Test application frontend'); // package description
        $response->assertSee('dev, build'); // package scripts
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that missing composer.json and package.json are handled gracefully
     */
    public function test_missing_json_files_handled_gracefully()
    {
        $appName = 'test-app-no-json-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application without composer.json and package.json
        File::makeDirectory($appPath, 0755, true);
        
        // Create a dummy .git file to simulate a git repository
        File::put($appPath . '/.git', '');

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Access the applications index page
        $response = $this->actingAs($user)
            ->get('/applications');

        $response->assertStatus(200);
        
        // Check that the application name is displayed
        $response->assertSee($appName);
        
        // Check that default values are shown for missing files
        $response->assertSee('No composer.json found');
        $response->assertSee('No package.json found');
        $response->assertSee('N/A');
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that application listing works with mixed JSON file presence
     */
    public function test_mixed_json_file_presence()
    {
        $appName = 'test-app-mixed-json-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application with only composer.json
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json
        $composerJson = '{
            "name": "mixed/test-app",
            "description": "A test application with only composer.json",
            "version": "2.1.0",
            "require": {
                "php": "^8.1",
                "laravel/framework": "^10.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);
        
        // Create a dummy .git file to simulate a git repository
        File::put($appPath . '/.git', '');

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Access the applications index page
        $response = $this->actingAs($user)
            ->get('/applications');

        $response->assertStatus(200);
        
        // Check that the application name is displayed
        $response->assertSee($appName);
        
        // Check that composer.json information is displayed
        $response->assertSee('mixed/test-app');
        $response->assertSee('A test application with only composer.json');
        $response->assertSee('2.1.0');
        $response->assertSee('^8.1');
        $response->assertSee('^10.0');
        
        // Check that default values are shown for missing package.json
        $response->assertSee('No package.json found');
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }
}