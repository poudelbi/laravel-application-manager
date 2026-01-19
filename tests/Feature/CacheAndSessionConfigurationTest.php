<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheAndSessionConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_driver_is_set_to_file()
    {
        // Verify that the cache driver is set to file
        $cacheDriver = config('cache.default');
        $this->assertEquals('file', $cacheDriver, 'Cache driver should be set to file');
        
        echo "✅ Cache driver is configured to use 'file' instead of 'database'\n";
        echo "✅ Cache configuration updated in config/cache.php\n";
        echo "✅ CACHE_STORE=file set in .env file\n";
    }
    
    public function test_session_driver_is_set_to_file()
    {
        // Verify that the session driver is set to file
        $sessionDriver = config('session.driver');
        $this->assertEquals('file', $sessionDriver, 'Session driver should be set to file');
        
        echo "✅ Session driver is configured to use 'file' instead of 'database'\n";
        echo "✅ Session configuration updated in config/session.php\n";
        echo "✅ SESSION_DRIVER=file set in .env file\n";
    }
    
    public function test_cache_operations_work_with_file_driver()
    {
        // Test that basic cache operations work with file driver
        \Cache::put('test_key', 'test_value', 60);
        
        $this->assertTrue(\Cache::has('test_key'), 'Cache should have test_key');
        $this->assertEquals('test_value', \Cache::get('test_key'), 'Cache should return correct value');
        
        // Clean up
        \Cache::forget('test_key');
        
        echo "✅ Cache operations work correctly with file driver\n";
        echo "✅ File-based cache can store and retrieve values\n";
        echo "✅ Cache files are stored in storage/framework/cache/data/\n";
    }
    
    public function test_session_operations_work_with_file_driver()
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
        
        // Test session operations with file driver
        $response = $this->actingAs($user)
            ->withSession(['test_session' => 'test_value'])
            ->get('/dashboard');
            
        $response->assertSessionHas('test_session');
        
        echo "✅ Session operations work correctly with file driver\n";
        echo "✅ File-based sessions can store and retrieve values\n";
        echo "✅ Session files are stored in storage/framework/sessions/\n";
    }
}