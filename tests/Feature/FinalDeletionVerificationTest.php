<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FinalDeletionVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected $applicationsDir = '/var/www/html/applications';

    public function test_application_deletion_functionality()
    {
        // Create a test application
        $appName = 'final-test-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create the application directory and a composer.json file
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/final-app","version":"1.0.0"}');

        // Verify the application was created
        $this->assertTrue(File::exists($appPath));
        $this->assertTrue(File::exists($appPath . '/composer.json'));

        // Create a user for authentication
        $user = \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Test the deletion using the controller
        $response = $this->actingAs($user)
                         ->delete("/applications/{$appName}");

        // Check that the response is a redirect
        $response->assertRedirect('/applications');

        // Verify the application directory was deleted
        $this->assertFalse(File::exists($appPath));
    }

    public function test_sudo_deletion_functionality()
    {
        // Create a test application
        $appName = 'sudo-test-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create the application directory and a composer.json file
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/sudo-app","version":"1.0.0"}');

        // Verify the application was created
        $this->assertTrue(File::exists($appPath));
        $this->assertTrue(File::exists($appPath . '/composer.json'));

        // Create a user for authentication
        $user = \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Test the deletion using the sites controller
        $response = $this->actingAs($user)
                         ->delete("/sites/{$appName}");

        // Check that the response is a redirect
        $response->assertRedirect('/sites');

        // Verify the application directory was deleted
        $this->assertFalse(File::exists($appPath));
    }
}