<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ActivityLog;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        ActivityLog::create([
            'id' => Str::uuid(),
            'user_id' => null,
            'action' => 'init',
            'description' => 'Initial log',
            'logged_at' => now(),
        ]);
    }
}
