<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }
    
    public function updateRole(Request $request, User $user) // Added the $ here
    {
        // Validate the input to ensure the role is allowed
        $request->validate([
            'new_role' => 'required|string|in:Admin,Accountant,Director,Regulator,Auditor,Staff,Agent'
        ]);

        $user->update(['role' => $request->new_role]);

        return back()->with('success', 'Role updated successfully!');
    }


    public function toggleStatus(User $user)
    {
        $newStatus = ($user->status === 'Active') ? 'Inactive' : 'Active';
        $user->update(['status' => $newStatus]);
        return back()->with('success', "User $newStatus successfully!");
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'User deleted successfully!');
    }
}