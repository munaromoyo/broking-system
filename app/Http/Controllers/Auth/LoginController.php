<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{


    //SHOW FORM FUNCTION

    public function showLoginForm()
    {
        // Ensure this points to your actual login blade file
        return view('auth.login'); 
    }

    //LOGIN EXECUTION
    public function login(Request $request)
    {

        // 1. Validation (Trim is handled automatically by Laravel Middleware)
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 2. Attempt login with custom "status" constraint
        // Laravel's Auth::attempt checks the password automatically
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'status' => 'active'], $request->filled('remember'))) {
            
            // 3. Security: Prevent session fixation (Automatic version of session_regenerate_id)
            $request->session()->regenerate();

            // 4. Custom Session values (Optional)
            // Note: You can usually access these via Auth::user()->role or Auth::user()->full_name 
            // without manually setting sessions.
            $user = Auth::user();
            session([
                'userid' => $user->id,
                'role'   => $user->role,
                'name'   => $user->full_name
            ]);

            // return redirect()->intended('/dashboard');
            // return redirect()->route('dashboard');
            // return redirect('/dashboard');
            return redirect()->intended('/dashboard');
        }

        // 5. If login fails, check if it failed because of status or credentials
        $userExists = \App\Models\User::where('email', $credentials['email'])->first();

        if ($userExists && $userExists->status !== 'active') {
            return back()->withErrors([
                'email' => "Your account is {$userExists->status}. Please contact admin.",
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }


    




}