<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_logs()
    {
        $this->withoutMiddleware([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\LogRequest::class,
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $this->actingAs($admin, 'sanctum');
        $response = $this->getJson('/api/logs');
        $response->assertStatus(200);
    }

    public function test_manager_cannot_view_logs()
    {
        $this->withoutMiddleware([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\LogRequest::class,
        ]);
        $manager = User::factory()->create(['role' => 'manager', 'status' => true]);
        $this->actingAs($manager, 'sanctum');
        $response = $this->getJson('/api/logs');
        $response->assertStatus(403);
    }
}
