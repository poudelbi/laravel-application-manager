<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\File;
use Tests\TestCase;
use App\Http\Controllers\ApplicationController;

class DeleteTest extends TestCase
{
    protected $applicationsDir = '/var/www/html/applications';

    /**
     * Test the destroy method directly
     */
    public function test_destroy_method_works()
    {
        $appName = 'test-direct-delete';
        $appPath = $this->applicationsDir . '/' . $appName;

        // Create a test application
        File::makeDirectory($appPath, 0755, true);
        File::put($appPath . '/composer.json', '{"name":"test/app"}');

        // Verify the application exists
        $this->assertTrue(File::exists($appPath));

        // Create controller instance
        $controller = new ApplicationController();

        // Call the destroy method directly
        $response = $controller->destroy($appName);

        // Check that the response is a redirect
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        // Verify the application directory was deleted
        $this->assertFalse(File::exists($appPath));
    }
}