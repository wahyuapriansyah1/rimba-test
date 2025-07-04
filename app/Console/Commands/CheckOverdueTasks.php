<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Support\Str;

class CheckOverdueTasks extends Command
{
    protected $signature = 'tasks:check-overdue';
    protected $description = 'Cek task overdue dan log ke activity_logs';

    public function handle()
    {
        $now = now()->toDateString();
        $overdueTasks = Task::where('status', '!=', 'done')
            ->whereDate('due_date', '<', $now)
            ->get();
        foreach ($overdueTasks as $task) {
            ActivityLog::create([
                'id' => Str::uuid(),
                'user_id' => $task->assigned_to,
                'action' => 'task_overdue',
                'description' => 'Task overdue: ' . $task->id . ' via scheduler',
                'logged_at' => now(),
            ]);
        }
        $this->info('Overdue tasks checked and logged.');
    }
}
