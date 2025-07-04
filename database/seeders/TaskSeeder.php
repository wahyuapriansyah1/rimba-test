<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        Task::create([
            'id' => Str::uuid(),
            'title' => 'Sample Task 1',
            'description' => 'Task for admin',
            'assigned_to' => null,
            'status' => 'pending',
            'due_date' => now()->addDays(3),
            'created_by' => null,
        ]);
    }
}
