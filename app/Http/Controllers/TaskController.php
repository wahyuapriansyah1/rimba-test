<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role === 'admin') {
            $tasks = Task::all();
        } elseif ($user->role === 'manager') {
            $tasks = Task::where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->get();
        } else { // staff
            $tasks = Task::where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id)
                ->get();
        }
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'in:pending,in_progress,done',
            'due_date' => 'required|date',
        ]);
        // Business logic: manager hanya bisa assign ke staff
        if ($user->role === 'manager' && !empty($data['assigned_to'])) {
            $assignedUser = User::find($data['assigned_to']);
            if (!$assignedUser || $assignedUser->role !== 'staff') {
                return response()->json(['message' => 'Manager hanya bisa assign ke staff'], 403);
            }
        }
        // staff hanya bisa assign ke diri sendiri
        if ($user->role === 'staff' && $data['assigned_to'] && $data['assigned_to'] !== $user->id) {
            return response()->json(['message' => 'Staff hanya bisa assign ke diri sendiri'], 403);
        }
        $data['id'] = Str::uuid();
        $data['created_by'] = $user->id;
        $task = Task::create($data);
        return response()->json($task, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $task = Task::findOrFail($id);
        // Only admin, creator, or assigned user can update
        if (!($user->role === 'admin' || $task->created_by === $user->id || $task->assigned_to === $user->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $data = $request->validate([
            'title' => 'sometimes|required',
            'description' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'in:pending,in_progress,done',
            'due_date' => 'sometimes|required|date',
        ]);
        // Business logic: manager hanya bisa assign ke staff
        if ($user->role === 'manager' && isset($data['assigned_to'])) {
            $assignedUser = User::find($data['assigned_to']);
            if (!$assignedUser || $assignedUser->role !== 'staff') {
                return response()->json(['message' => 'Manager hanya bisa assign ke staff'], 403);
            }
        }
        // staff hanya bisa assign ke diri sendiri
        if ($user->role === 'staff' && isset($data['assigned_to']) && $data['assigned_to'] !== $user->id) {
            return response()->json(['message' => 'Staff hanya bisa assign ke diri sendiri'], 403);
        }
        $task->update($data);
        return response()->json($task);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $task = Task::findOrFail($id);
        // Only admin or creator can delete
        if (!($user->role === 'admin' || $task->created_by === $user->id)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}
