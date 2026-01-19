<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GitDeploymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we're in testing environment
        config(['app.env' => 'testing']);
    }

    /**
     * Test that we can deploy an application from a Git repository
     */
    public function test_can_deploy_from_git_repository()
    {
        // Skip this test if running in CI or if git is not available
        if (!File::exists('/usr/bin/git')) {
            $this->markTestSkipped('Git is not installed.');
        }

        // Mock repository details
        $repoUrl = 'https://github.com/Nextmorse-Technologies/kathalaya-upgrade.git';
        $accessToken = 'REDACTED_GITHUB_TOKEN';
        $appName = 'test-kathalaya-app-' . time();
        $version = '8.2';

        // Simulate the application creation process
        $response = $this->actingAs($this->getUser())
            ->post('/applications', [
                'name' => $appName,
                'version' => $version,
                'repo_url' => $repoUrl,
                'access_token' => $accessToken,
            ]);

        // Check that the request was successful
        $response->assertRedirect('/applications');
        
        // Check session for success or error message
        $response->assertSessionHas('success', 'Application cloned from Git repository successfully!');
        
        // Verify the application directory was created
        $appPath = '/var/www/html/applications/' . $appName;
        $this->assertTrue(File::exists($appPath), "Application directory should be created at $appPath");
        
        // Verify the repository was cloned by checking for common Laravel files
        $this->assertTrue(
            File::exists($appPath . '/composer.json') || File::exists($appPath . '/.git'),
            "Repository should be cloned to $appPath"
        );
        
        // Clean up: delete the created application directory
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that we can navigate to the setup page after Git deployment
     */
    public function test_can_access_setup_page_after_git_deployment()
    {
        // This test assumes that a git repository has been successfully cloned
        // For testing purposes, we'll create a minimal directory structure
        $appName = 'test-setup-app-' . time();
        $appPath = '/var/www/html/applications/' . $appName;
        
        // Create a minimal application structure for testing
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{}');
        File::makeDirectory($appPath . '/storage', 0755, true);
        File::makeDirectory($appPath . '/bootstrap/cache', 0755, true);
        
        // Test accessing the setup page
        $response = $this->actingAs($this->getUser())
            ->get("/applications/{$appName}/setup");
        
        $response->assertStatus(200);
        $response->assertSee($appName);
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test that we can complete the setup process
     */
    public function test_can_complete_setup_process()
    {
        // Create a minimal application structure for testing
        $appName = 'test-complete-setup-' . time();
        $appPath = '/var/www/html/applications/' . $appName;
        
        // Create a minimal application structure for testing
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"require": {"php": "^8.0"}}');
        File::put($appPath . '/.env', "APP_NAME=Laravel\nAPP_ENV=local\n");
        File::makeDirectory($appPath . '/storage', 0755, true);
        File::makeDirectory($appPath . '/bootstrap/cache', 0755, true);
        
        // Test submitting the setup form
        $response = $this->actingAs($this->getUser())
            ->post("/applications/{$appName}/setup", [
                'environment' => "APP_NAME={$appName}\nAPP_ENV=production\nAPP_DEBUG=false\n",
                'generate_key' => true,
                'php_version' => '8.2',
                'run_commands' => ['composer', 'migrate'],
            ]);
        
        $response->assertRedirect('/applications');
        $response->assertSessionHas('success');
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Helper method to get an authenticated user for testing
     */
    private function getUser()
    {
        // Create or retrieve a test user
        return \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}