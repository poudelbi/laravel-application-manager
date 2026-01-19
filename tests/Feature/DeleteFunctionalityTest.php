<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DeleteFunctionalityTest extends TestCase
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
     * Test that the delete functionality works properly
     */
    public function test_delete_functionality_works()
    {
        $appName = 'test-delete-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json
        $composerJson = '{
            "name": "test/delete-app",
            "description": "A test application for deletion",
            "version": "1.0.0",
            "require": {
                "php": "^8.1",
                "laravel/framework": "^9.0"
            }
        }';
        File::put($appPath . '/composer.json', $composerJson);
        
        // Create public directory
        File::makeDirectory($appPath . '/public', 0755, true);

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

        // Test accessing the applications index page to see the app
        $response = $this->actingAs($user)
            ->get('/applications');

        $response->assertStatus(200);
        $response->assertSee($appName);

        // Test deleting the application using DELETE method
        $deleteResponse = $this->actingAs($user)
            ->delete("/applications/{$appName}");
            
        // Check that it redirects back to applications index
        $deleteResponse->assertRedirect('/applications');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->actingAs($user)->get($deleteResponse->headers->get('Location'));
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
        $deleteResponse = $this->actingAs($user)
            ->delete("/applications/nonexistent-app");
            
        // Check that it redirects back to applications index
        $deleteResponse->assertRedirect('/applications');
        
        // Follow the redirect to see the session message
        $followedResponse = $this->actingAs($user)->get($deleteResponse->headers->get('Location'));
        $followedResponse->assertSessionHas('error', 'Application not found!');
    }
}