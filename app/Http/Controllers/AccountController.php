<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class AccountController extends Controller
{
    public function edit()
    {
        // Laravel already has the user data in Auth::user()
        return view('account.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {

   
        $user = Auth::user();

        // Validation
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $user->id,
            'passwd'     => 'nullable|min:6|confirmed', // 'confirmed' looks for 'passwd_confirmation'
        ]);

        // Update basic info
        $user->update($request->except(['passwd', 'cpasswd']));

        // Handle Password separately if provided
        if ($request->filled('passwd')) {
            $user->password = Hash::make($request->passwd);
            $user->save();
        }

        return back()->with('success', 'Account updated successfully!');
    }
}