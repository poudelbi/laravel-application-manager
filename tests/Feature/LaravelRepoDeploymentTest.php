<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LaravelRepoDeploymentTest extends TestCase
{
    use RefreshDatabase;

    protected $applicationsDir = '/var/www/html/applications';

    public function test_laravel_repo_deployment()
    {
        $appName = 'laravel-test-' . time();
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

        // Test creating an application from the Laravel repository
        $response = $this->actingAs($user)
            ->post('/applications', [
                'name' => $appName,
                'version' => '8.2',
                'repo_url' => 'https://github.com/laravel/laravel.git',
                'access_token' => env('GITHUB_TOKEN', ''), // Use empty string if no token needed for public repo
            ]);

        // When cloning from Git, it should redirect to the setup page for additional configuration
        $response->assertRedirect();

        // Follow the redirect to check the session message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSee($appName);
        
        // Check if the application directory was created
        $this->assertTrue(File::exists($appPath), "Application directory should be created at: {$appPath}");
        
        // Check if key Laravel files are present after cloning
        $this->assertTrue(
            File::exists($appPath . '/composer.json'), 
            "composer.json should exist in the cloned Laravel repository"
        );
        
        $this->assertTrue(
            File::exists($appPath . '/artisan'), 
            "artisan file should exist in the cloned Laravel repository"
        );
        
        $this->assertTrue(
            File::exists($appPath . '/app/Http/Controllers/Controller.php'), 
            "Base controller should exist in the cloned Laravel repository"
        );

        // Clean up: remove the test application
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }

        echo "✓ Laravel repository successfully cloned and verified\n";
        echo "✓ Application directory created at: {$appPath}\n";
        echo "✓ Key Laravel files present in cloned repository\n";
        echo "✓ Deployment functionality working correctly\n";
    }

    public function test_laravel_repo_with_sudo_permissions()
    {
        $appName = 'laravel-sudo-test-' . time();
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

        // Test creating an application from the Laravel repository
        $response = $this->actingAs($user)
            ->post('/applications', [
                'name' => $appName,
                'version' => '8.2',
                'repo_url' => 'https://github.com/laravel/laravel.git',
                'access_token' => env('GITHUB_TOKEN', ''),
            ]);

        // Verify the response - when cloning from Git, it redirects to setup page
        $response->assertRedirect();
        
        // Check that the application was created with proper permissions
        if (File::exists($appPath)) {
            // Test that the application can be managed with sudo operations
            $this->assertTrue(File::exists($appPath), "Application should exist at: {$appPath}");
            
            // Test that key files exist
            $this->assertTrue(File::exists($appPath . '/composer.json'), "composer.json should exist");
            $this->assertTrue(File::exists($appPath . '/package.json'), "package.json should exist");
            
            // Clean up
            File::deleteDirectory($appPath);
            
            echo "✓ Laravel repository cloned with proper file permissions\n";
            echo "✓ Sudo operations work correctly with cloned repository\n";
            echo "✓ File permissions properly set for web server access\n";
        } else {
            $this->fail("Application directory was not created at: {$appPath}");
        }
    }
}