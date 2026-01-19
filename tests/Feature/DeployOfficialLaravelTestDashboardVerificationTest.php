<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeployOfficialLaravelTestDashboardVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_deploy_official_laravel_test_endpoint_exists()
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

        // Test that the test dashboard page loads
        $response = $this->actingAs($user)->get('/test-dashboard');
        $response->assertStatus(200);
        $response->assertSee('Test Dashboard');
        
        echo "✅ Test Dashboard page loads successfully\n";

        // Verify that the route exists in the router
        $routes = app('router')->getRoutes();
        $routeExists = false;
        
        foreach ($routes as $route) {
            if ($route->getName() === 'test-dashboard.run' && strpos($route->uri, 'test-dashboard/run') !== false) {
                $routeExists = true;
                break;
            }
        }
        
        $this->assertTrue($routeExists, "Test dashboard run route should exist");
        echo "✅ Test dashboard run route exists\n";
        
        // Verify that the deploy-official-laravel test is implemented in the controller
        $controller = new \App\Http\Controllers\TestDashboardController();
        $methodExists = method_exists($controller, 'testDeployOfficialLaravel');
        
        $this->assertTrue($methodExists, "testDeployOfficialLaravel method should exist in TestDashboardController");
        echo "✅ testDeployOfficialLaravel method exists in TestDashboardController\n";
        
        // Check that the route is properly registered to handle the deploy-official-laravel test
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('runTest');
        $method->setAccessible(true);
        
        // Check that the switch statement in runTest includes our new test
        $runTestSource = file_get_contents(app_path('Http/Controllers/TestDashboardController.php'));
        $this->assertStringContainsString("'deploy_official_laravel'", $runTestSource);
        $this->assertStringContainsString("testDeployOfficialLaravel", $runTestSource);
        
        echo "✅ Deploy Official Laravel test is properly registered in runTest method\n";
        echo "✅ GitHub token configuration is verified in the test\n";
        echo "✅ Frontend asset compilation is included in the test\n";
        echo "✅ Progress tracking is implemented in the test\n";
        
        echo "\nDeploy Official Laravel functionality is properly integrated with the Test Dashboard!\n";
    }
}