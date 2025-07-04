<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_task()
    {
        $this->withoutMiddleware([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\LogRequest::class,
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'status' => true]);
        $this->actingAs($admin, 'sanctum');
        $response = $this->postJson('/api/tasks', [
            'title' => 'Task 1',
            'description' => 'desc',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);
        $response->assertStatus(201)->assertJsonFragment(['title' => 'Task 1']);
    }

    public function test_manager_can_assign_to_staff_only()
    {
        $this->withoutMiddleware([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\LogRequest::class,
        ]);
        $manager = User::factory()->create(['role' => 'manager', 'status' => true]);
        $staff = User::factory()->create(['role' => 'staff', 'status' => true]);
        $staff->refresh();
        $this->actingAs($manager, 'sanctum');
        $response = $this->postJson('/api/tasks', [
            'title' => 'Task 2',
            'description' => 'desc',
            'assigned_to' => $staff->id,
            'due_date' => now()->addDays(2)->toDateString(),
        ]);
        $response->assertStatus(201);
        $this->assertIsString($staff->id);
        $this->assertTrue(strlen($staff->id) > 10);
    }

    public function test_manager_cannot_assign_to_manager()
    {
        $this->withoutMiddleware([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\CheckUserStatus::class,
            \App\Http\Middleware\LogRequest::class,
        ]);
        $manager = User::factory()->create(['role' => 'manager', 'status' => true]);
        $otherManager = User::factory()->create(['role' => 'manager', 'status' => true]);
        $otherManager->refresh();
        $this->actingAs($manager, 'sanctum');
        $response = $this->postJson('/api/tasks', [
            'title' => 'Task 3',
            'description' => 'desc',
            'assigned_to' => $otherManager->id,
            'due_date' => now()->addDays(2)->toDateString(),
        ]);
        $response->dump();
        $response->assertStatus(403);
        $this->assertIsString($otherManager->id);
        $this->assertTrue(strlen($otherManager->id) > 10);
    }
}
