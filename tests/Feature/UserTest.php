<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user()
    {
        $this->withoutMiddleware([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\LogRequest::class,
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $this->actingAs($admin, 'sanctum');
        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password',
            'role' => 'staff',
            'status' => true,
        ]);
        $response->dump();
        $response->assertStatus(201)->assertJsonFragment(['email' => 'testuser@example.com']);
    }

    public function test_manager_cannot_create_user()
    {
        $this->withoutMiddleware([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\LogRequest::class,
        ]);
        $manager = User::factory()->create(['role' => 'manager', 'status' => true]);
        $this->actingAs($manager, 'sanctum');
        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'testuser2@example.com',
            'password' => 'password',
            'role' => 'staff',
            'status' => true,
        ]);
        $response->assertStatus(403);
    }
}
