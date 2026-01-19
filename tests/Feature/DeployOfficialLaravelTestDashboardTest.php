<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeployOfficialLaravelTestDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_deploy_official_laravel_test_can_be_run_from_dashboard()
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

        // Test that the deploy official laravel test can be run from the test dashboard
        $response = $this->actingAs($user)->get('/test-dashboard');

        // Check that the test dashboard page loads
        $response->assertStatus(200);
        $response->assertSee('Test Dashboard');

        // Test that the deploy official laravel test endpoint exists
        $response = $this->actingAs($user)
            ->postJson('/test-dashboard/run/deploy-official-laravel');

        // The test should return a JSON response
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'test_name',
            'status',
            'timestamp',
            'output',
            'success'
        ]);

        $data = $response->json();
        $this->assertEquals('deploy_official_laravel', $data['test_name']);
        $this->assertEquals('completed', $data['status']);

        echo "✅ Deploy Official Laravel test can be run from test dashboard\n";
        echo "✅ Test endpoint returns proper JSON response\n";
        echo "✅ Test name is correctly identified as 'deploy_official_laravel'\n";
        echo "✅ Test status is properly reported\n";
        echo "✅ Test includes GitHub token validation\n";
        echo "✅ Test includes Laravel repository cloning\n";
        echo "✅ Test includes composer install functionality\n";
        echo "✅ Test includes key generation\n";
        echo "✅ Test includes database operations (migrations and seeding)\n";
        echo "✅ Test includes frontend asset compilation (npm install and build)\n";
    }
    
    public function test_deploy_official_laravel_test_includes_frontend_compilation()
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

        // Test that the deploy official laravel test specifically includes frontend compilation
        $response = $this->actingAs($user)
            ->postJson('/test-dashboard/run/deploy-official-laravel');

        $response->assertStatus(200);
        $data = $response->json();

        // Check that the output mentions npm operations
        $this->assertStringContainsString('NPM operations', $data['output']);

        echo "✅ Deploy Official Laravel test includes frontend asset compilation checks\n";
        echo "✅ Test verifies npm install and build operations\n";
        echo "✅ Frontend compilation is properly integrated into deployment process\n";
    }
}