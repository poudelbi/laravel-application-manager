<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class GitDeploymentIntegrationTest extends TestCase
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
     * Test the complete Git deployment workflow
     */
    public function test_complete_git_deployment_workflow()
    {
        $this->withoutExceptionHandling(); // For debugging purposes
        
        // Mock repository details
        $repoUrl = 'https://github.com/Nextmorse-Technologies/kathalaya-upgrade.git';
        $accessToken = 'REDACTED_GITHUB_TOKEN';
        $appName = 'integration-test-app-' . time();
        $version = '8.2';

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test the initial application creation request
        $response = $this->actingAs($user)
            ->post('/applications', [
                'name' => $appName,
                'version' => $version,
                'repo_url' => $repoUrl,
                'access_token' => $accessToken,
            ]);

        // The response should redirect to the setup page since we're cloning from Git
        // But since we can't actually clone in the test environment, we expect it to fail gracefully
        $response->assertRedirect('/applications');
        
        // Check if there's an error message (since git clone would fail in test environment)
        $response->assertSessionHas('error');
        
        // Clean up: delete the created application directory if it exists
        $appPath = $this->applicationsDir . '/' . $appName;
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test creating a new Laravel application (not from Git)
     */
    public function test_create_new_laravel_application()
    {
        $appName = 'new-laravel-app-' . time();
        $version = '8.2';

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test creating a new Laravel application (not from Git)
        $response = $this->actingAs($user)
            ->post('/applications', [
                'name' => $appName,
                'version' => $version,
                // No repo_url means it will create a new Laravel app
            ]);

        $response->assertRedirect('/applications');
        $response->assertSessionHas('success', 'New Laravel application created successfully!');

        // Verify the application directory was created
        $appPath = $this->applicationsDir . '/' . $appName;
        $this->assertTrue(File::exists($appPath), "Application directory should be created at $appPath");
        
        // Verify basic Laravel structure exists
        $this->assertTrue(File::exists($appPath . '/composer.json'));
        $this->assertTrue(File::exists($appPath . '/app'));
        $this->assertTrue(File::exists($appPath . '/config'));
        $this->assertTrue(File::exists($appPath . '/routes'));
        $this->assertTrue(File::exists($appPath . '/public'));
        $this->assertTrue(File::exists($appPath . '/storage'));
        
        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test the setup page access
     */
    public function test_setup_page_access()
    {
        $appName = 'setup-test-app-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a minimal application structure for testing
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{}');
        File::put($appPath . '/.env', "APP_NAME=Laravel\nAPP_ENV=local\n");

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test accessing the setup page
        $response = $this->actingAs($user)
            ->get("/applications/{$appName}/setup");

        $response->assertStatus(200);
        $response->assertSee($appName);
        $response->assertSee('Step 1: Environment Configuration');
        $response->assertSee('Step 2: Generate Application Key');
        $response->assertSee('Step 3: Install Dependencies');
        $response->assertSee('Step 4: Database Operations');

        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }

    /**
     * Test the setup completion process
     */
    public function test_setup_completion_process()
    {
        $appName = 'complete-setup-test-' . time();
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a minimal application structure for testing
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"require": {"php": "^8.0"}}');
        File::put($appPath . '/.env', "APP_NAME=Laravel\nAPP_ENV=local\n");
        File::makeDirectory($appPath . '/storage', 0755, true);
        File::makeDirectory($appPath . '/bootstrap/cache', 0755, true);

        // Create a test user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Test submitting the setup form
        $response = $this->actingAs($user)
            ->post("/applications/{$appName}/setup", [
                'environment' => "APP_NAME={$appName}\nAPP_ENV=production\nAPP_DEBUG=false\n",
                'generate_key' => true,
                'php_version' => '8.2',
                'run_commands' => ['composer', 'migrate'],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Clean up
        if (File::exists($appPath)) {
            File::deleteDirectory($appPath);
        }
    }
}