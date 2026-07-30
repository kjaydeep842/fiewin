<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('wallet')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function toggleStatus(User $user)
    {
        $user->status = ($user->status === 'active') ? 'blocked' : 'active';
        $user->save();

        return back()->with('success', "User status updated to {$user->status}");
    }
}
