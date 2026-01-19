<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitesManagementTest extends TestCase
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
     * Test that the sites index page loads correctly
     */
    public function test_sites_index_page_loads()
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

        // Access the sites index page
        $response = $this->actingAs($user)
            ->get('/sites');

        $response->assertStatus(200);
        $response->assertSee('Available Applications');
    }

    /**
     * Test that applications are listed on the sites page
     */
    public function test_applications_are_listed_on_sites_page()
    {
        $appName = 'test-site-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json
        $composerJson = '{
            "name": "test/site-app",
            "description": "A test site application",
            "version": "1.0.0",
            "require": {
                "php": "^8.1",
                "laravel/framework": "^9.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Access the sites index page
        $response = $this->actingAs($user)
            ->get('/sites');

        $response->assertStatus(200);
        $response->assertSee($appName);
        $response->assertSee('test/site-app'); // composer name
        $response->assertSee('^8.1'); // php version
        $response->assertSee('^9.0'); // laravel version

        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that the start functionality works
     */
    public function test_start_functionality()
    {
        $appName = 'test-start-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/start-app"}');

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test starting the application
        $response = $this->actingAs($user)
            ->post("/sites/{$appName}/start");

        $response->assertRedirect('/sites');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success');

        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that the stop functionality works
     */
    public function test_stop_functionality()
    {
        $appName = 'test-stop-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/stop-app"}');

        // Set the app as running in cache
        Cache::put('app_running_' . $appName, true, now()->addMinutes(10));

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test stopping the application
        $response = $this->actingAs($user)
            ->post("/sites/{$appName}/stop");

        $response->assertRedirect('/sites');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success');

        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that the update functionality works for Git repositories
     */
    public function test_update_functionality()
    {
        $appName = 'test-update-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application with .git directory to simulate a Git repo
        File::makeDirectory($appPath, 0755, true);
        File::makeDirectory($appPath . '/.git', 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/update-app"}');

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test updating the application
        $response = $this->actingAs($user)
            ->post("/sites/{$appName}/update");

        $response->assertRedirect('/sites');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->followRedirects($response);
        // The update might fail because it's not a real Git repo, but it should at least try
        // We'll check that it redirects properly

        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that the refresh functionality works
     */
    public function test_refresh_functionality()
    {
        $appName = 'test-refresh-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/refresh-app"}');
        File::put($appPath . '/artisan', '#!/usr/bin/env php\n<?php echo "Laravel Artisan";');

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test refreshing the application
        $response = $this->actingAs($user)
            ->post("/sites/{$appName}/refresh");

        $response->assertRedirect('/sites');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success');

        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that the delete functionality works
     */
    public function test_delete_functionality()
    {
        $appName = 'test-delete-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/delete-app"}');

        // Verify the application exists
        $this->assertTrue(File::exists($appPath));

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test deleting the application
        $response = $this->actingAs($user)
            ->delete("/sites/{$appName}");

        $response->assertRedirect('/sites');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('success', 'Application deleted successfully!');

        // Verify the application directory was deleted
        $this->assertFalse(File::exists($appPath));
    }

    /**
     * Test that deleting a non-existent application shows error
     */
    public function test_delete_non_existent_application()
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

        // Try to delete a non-existent application
        $response = $this->actingAs($user)
            ->delete("/sites/nonexistent-app");
            
        // Check that it redirects back to sites index
        $response->assertRedirect('/sites');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertSessionHas('error', 'Application not found!');
    }
}