<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('view-users')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $users = User::all();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('create-user')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,manager,staff',
            'status' => 'boolean',
        ]);
        $data['id'] = Str::uuid();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        return response()->json($user, 201);
    }
}
