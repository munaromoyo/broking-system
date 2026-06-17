<?PHP

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    // SHOW FORM FUNCTION
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // USER REGISTRATION EXECUTION 
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'company'    => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:8',
            'role'       => 'required'
        ]);

        // 2. Assign the created user to the $user variable
        $user = User::create([
            'user'        => $request->first_name . ' ' . $request->last_name,
            'user_name'   => $request->email,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name'   => $request->last_name,
            'company'     => $request->company,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'status'      => 'pending', // Explicitly set starting status
        ]);

        // 3. Fire the event INSIDE the function and pass the $user variable
        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Please check your email to verify your account.');
    }
}