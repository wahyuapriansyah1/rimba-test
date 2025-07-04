<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Gate;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view-logs')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $logs = ActivityLog::orderBy('logged_at', 'desc')->get();
        return response()->json($logs);
    }
}
