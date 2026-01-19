<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NginxConfigTest extends TestCase
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
     * Test that nginx configuration endpoint returns proper response
     */
    public function test_nginx_config_endpoint_returns_configuration()
    {
        $appName = 'test-nginx-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application with composer.json
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json
        $composerJson = '{
            "name": "test/test-nginx-app",
            "description": "A test Laravel application for nginx config",
            "version": "1.0.0",
            "require": {
                "php": "^8.1",
                "laravel/framework": "^9.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);
        
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

        // Access the nginx config endpoint
        $response = $this->actingAs($user)
            ->get("/applications/{$appName}/nginx-config");

        $response->assertStatus(200);
        
        // Verify the response structure
        $response->assertJsonStructure([
            'name',
            'config',
            'path',
            'php_version'
        ]);
        
        // Verify the response content
        $responseData = $response->json();
        $this->assertEquals($appName, $responseData['name']);
        $this->assertEquals($appPath, $responseData['path']);
        $this->assertEquals('8.1', $responseData['php_version']); // Extracted from composer.json
        $this->assertStringContainsString('server {', $responseData['config']);
        $this->assertStringContainsString($appPath . '/public', $responseData['config']);
        $this->assertStringContainsString('try_files $uri $uri/ /index.php?$query_string;', $responseData['config']);
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test nginx configuration with default PHP version when not specified
     */
    public function test_nginx_config_with_default_php_version()
    {
        $appName = 'test-nginx-default-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application with composer.json that doesn't specify PHP version
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json without PHP version
        $composerJson = '{
            "name": "test/default-php-app",
            "description": "A test app without specific PHP version",
            "version": "1.0.0",
            "require": {
                "laravel/framework": "^10.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);
        
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

        // Access the nginx config endpoint
        $response = $this->actingAs($user)
            ->get("/applications/{$appName}/nginx-config");

        $response->assertStatus(200);
        
        // Verify the response structure
        $response->assertJsonStructure([
            'name',
            'config',
            'path',
            'php_version'
        ]);
        
        // Verify the response content
        $responseData = $response->json();
        $this->assertEquals($appName, $responseData['name']);
        $this->assertEquals($appPath, $responseData['path']);
        // Should default to 8.2 when not specified in composer.json
        $this->assertEquals('8.2', $responseData['php_version']);
        $this->assertStringContainsString('server {', $responseData['config']);
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test nginx configuration endpoint for non-existent application
     */
    public function test_nginx_config_endpoint_for_nonexistent_app()
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
     * Test that nginx configuration contains expected directives
     */
    public function test_nginx_config_contains_expected_directives()
    {
        $appName = 'test-nginx-directives-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json
        $composerJson = '{
            "name": "test/directives-app",
            "require": {
                "php": "^8.0",
                "laravel/framework": "^9.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);
        
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

        // Access the nginx config endpoint
        $response = $this->actingAs($user)
            ->get("/applications/{$appName}/nginx-config");

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $config = $responseData['config'];
        
        // Verify that the configuration contains expected directives
        $this->assertStringContainsString('listen 80;', $config);
        $this->assertStringContainsString('root ' . $appPath . '/public;', $config);
        $this->assertStringContainsString('index index.php index.html index.htm;', $config);
        $this->assertStringContainsString('try_files $uri $uri/ /index.php?$query_string;', $config);
        $this->assertStringContainsString('location ~ \.php$ {', $config);
        $this->assertStringContainsString('deny all;', $config); // For sensitive files
        $this->assertStringContainsString('expires 1y;', $config); // For static files
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }
}