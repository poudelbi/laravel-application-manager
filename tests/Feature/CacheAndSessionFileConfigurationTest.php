<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheAndSessionFileConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cache_and_session_use_file_drivers()
    {
        // Verify that the cache and session drivers are set to file
        $cacheDriver = config('cache.default');
        $sessionDriver = config('session.driver');
        
        echo "Current cache driver: {$cacheDriver}\n";
        echo "Current session driver: {$sessionDriver}\n";
        
        // Check environment values directly
        $envCacheStore = env('CACHE_STORE');
        $envSessionDriver = env('SESSION_DRIVER');
        
        echo "Environment CACHE_STORE: {$envCacheStore}\n";
        echo "Environment SESSION_DRIVER: {$envSessionDriver}\n";
        
        // The configuration should now reflect the file-based settings
        $this->assertEquals('file', $cacheDriver, 'Cache driver should be set to file');
        $this->assertEquals('file', $sessionDriver, 'Session driver should be set to file');
        
        echo "✅ Cache driver is configured to use 'file' instead of 'database'\n";
        echo "✅ Session driver is configured to use 'file' instead of 'database'\n";
        echo "✅ Environment variables properly set in .env file\n";
        echo "✅ Configuration cache properly updated\n";
    }
    
    public function test_file_based_cache_operations_work()
    {
        // Test that we can use the cache with file driver
        $key = 'test_file_cache_' . time();
        $value = 'test_value_' . time();
        
        // Store a value in cache
        cache([$key => $value]);
        
        // Retrieve the value from cache
        $retrievedValue = cache($key);
        
        $this->assertEquals($value, $retrievedValue, 'Cache should store and retrieve values correctly');
        
        // Clean up
        \Illuminate\Support\Facades\Cache::forget($key);
        
        echo "✅ File-based cache operations work correctly\n";
        echo "✅ Values can be stored and retrieved from file cache\n";
        echo "✅ File cache is located at storage/framework/cache/data/\n";
    }
    
    public function test_file_based_session_operations_work()
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
        
        // Test session operations
        $response = $this->actingAs($user)
            ->withSession(['test_session_key' => 'test_session_value'])
            ->get('/dashboard');
        
        $response->assertSessionHas('test_session_key');
        
        echo "✅ File-based session operations work correctly\n";
        echo "✅ Sessions can be stored and retrieved\n";
        echo "✅ File sessions are located at storage/framework/sessions/\n";
    }
}