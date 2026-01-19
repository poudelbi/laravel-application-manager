<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ComprehensiveAppManagerTest extends TestCase
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
     * Test the complete application lifecycle: creation, setup, nginx config, deletion
     */
    public function test_complete_application_lifecycle()
    {
        $appName = 'test-comprehensive-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test creating an application with composer.json and package.json
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json with multiple dependencies
        $composerJson = '{
            "name": "test/comprehensive-app",
            "description": "A comprehensive test application",
            "version": "1.0.0",
            "require": {
                "php": "^8.1",
                "laravel/framework": "^9.0",
                "guzzlehttp/guzzle": "^7.0",
                "monolog/monolog": "^2.0",
                "vlucas/phpdotenv": "^5.0",
                "nesbot/carbon": "^2.0"
            },
            "require-dev": {
                "fakerphp/faker": "^1.9",
                "phpunit/phpunit": "^9.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);
        
        // Create a sample package.json with multiple dependencies
        $packageJson = '{
            "name": "comprehensive-test-app",
            "version": "1.0.0",
            "description": "Test application frontend",
            "scripts": {
                "dev": "vite",
                "build": "vite build"
            },
            "dependencies": {
                "axios": "^1.0.0",
                "lodash": "^4.17.0",
                "moment": "^2.29.0"
            },
            "devDependencies": {
                "vite": "^4.0.0",
                "@vitejs/plugin-vue": "^4.0.0"
            }
        }';
        File::put($appPath . '/package.json', $packageJson);
        
        // Create public directory
        File::makeDirectory($appPath . '/public', 0755, true);

        // Test accessing the applications index page
        $response = $this->actingAs($user)
            ->get('/applications');

        $response->assertStatus(200);
        
        // Check that the application name is displayed
        $response->assertSee($appName);
        
        // Check that composer.json information is displayed
        $response->assertSee('Composer Info:');
        $response->assertSee('A comprehensive test application'); // description
        $response->assertSee('test/comprehensive-app'); // name
        $response->assertSee('^8.1'); // php version
        $response->assertSee('^9.0'); // laravel version
        
        // Check that PHP dependencies are displayed
        $response->assertSee('laravel/framework: ^9.0');
        $response->assertSee('guzzlehttp/guzzle: ^7.0');
        $response->assertSee('monolog/monolog: ^2.0');
        $response->assertSee('vlucas/phpdotenv: ^5.0');
        $response->assertSee('nesbot/carbon: ^2.0');
        
        // Check that package.json information is displayed
        $response->assertSee('Package Info:');
        $response->assertSee('comprehensive-test-app'); // package name
        $response->assertSee('Test application frontend'); // package description
        
        // Check that NPM dependencies are displayed
        $response->assertSee('axios: ^1.0.0');
        $response->assertSee('lodash: ^4.17.0');
        $response->assertSee('moment: ^2.29.0');
        
        // Test nginx configuration endpoint
        $nginxResponse = $this->actingAs($user)
            ->get("/applications/{$appName}/nginx-config");

        $nginxResponse->assertStatus(200);
        
        // Verify the response structure
        $nginxResponse->assertJsonStructure([
            'name',
            'config',
            'path',
            'php_version'
        ]);
        
        // Verify the response content
        $nginxData = $nginxResponse->json();
        $this->assertEquals($appName, $nginxData['name']);
        $this->assertEquals($appPath, $nginxData['path']);
        $this->assertEquals('8.1', $nginxData['php_version']); // Extracted from composer.json
        $this->assertStringContainsString('server {', $nginxData['config']);
        $this->assertStringContainsString($appPath . '/public', $nginxData['config']);
        $this->assertStringContainsString('try_files $uri $uri/ /index.php?$query_string;', $nginxData['config']);
        
        // Test deleting the application
        $deleteResponse = $this->actingAs($user)
            ->delete("/applications/{$appName}");
            
        $deleteResponse->assertRedirect('/applications');
        
        // Verify the application directory was deleted
        $this->assertFalse(File::exists($appPath));

        // Clean up in case test failed before deletion
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test application listing with missing JSON files
     */
    public function test_application_with_missing_json_files()
    {
        $appName = 'test-missing-json-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application without composer.json and package.json
        File::makeDirectory($appPath, 0755, true);
        
        // Create public directory
        File::makeDirectory($appPath . '/public', 0755, true);

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
        
        // Check that appropriate messages are shown for missing files
        $response->assertSee('No composer.json found');
        $response->assertSee('No package.json found');
        $response->assertSee('N/A');
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test nginx configuration for non-existent application
     */
    public function test_nginx_config_for_nonexistent_app()
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

        // Access the nginx config endpoint for non-existent app
        $response = $this->actingAs($user)
            ->get("/applications/nonexistent-app/nginx-config");

        $response->assertStatus(404);
    }

    /**
     * Test application listing with large JSON files
     */
    public function test_application_with_large_json_files()
    {
        $appName = 'test-large-json-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application with large composer.json
        File::makeDirectory($appPath, 0755, true);
        
        // Create a large composer.json (larger than 100KB limit)
        $largeContent = str_repeat('"dummy_dependency": "^1.0.0,', 30000); // This will exceed 100KB
        $largeComposerJson = '{ "name": "test/large-app", "require": { ' . rtrim($largeContent, ',') . ' } }';
        File::put($appPath . '/composer.json', $largeComposerJson);
        
        // Create public directory
        File::makeDirectory($appPath . '/public', 0755, true);

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
        
        // Check that appropriate message is shown for large file
        $response->assertSee('Large file');
        $response->assertSee('File too large to parse');
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }
}