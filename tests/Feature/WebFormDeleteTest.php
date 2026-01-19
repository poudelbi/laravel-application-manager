<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WebFormDeleteTest extends TestCase
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
     * Test that the delete functionality works through the web form
     */
    public function test_delete_functionality_through_web_form()
    {
        $appName = 'test-web-delete-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        
        // Create a sample composer.json
        $composerJson = '{
            "name": "test/web-delete-app",
            "description": "A test application for web form deletion",
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

        // Test deleting the application using the web form (POST request with DELETE method spoofing)
        $deleteResponse = $this->actingAs($user)
            ->call('DELETE', "/applications/{$appName}", [
                '_token' => csrf_token(),
            ]);
            
        // Check that it redirects back to applications index
        $deleteResponse->assertRedirect(route('applications.index'));
        
        // Follow the redirect to see the session message
        $followedResponse = $this->followRedirects($deleteResponse);
        $followedResponse->assertSessionHas('success', 'Application deleted successfully!');

        // Verify the application directory was deleted
        $this->assertFalse(File::exists($appPath));
    }
}