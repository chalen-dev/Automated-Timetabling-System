<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    public function showRegisterForm(){
        return view('auth.register');
    }
    public function register(Request $request){

        //Input validation
        $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_]+$/'], //regex is to disallow the use of certain special characters
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[A-Z]/'],
        ]);

        //Create user, query to db
        try {
            User::create([
                'name' => $request->name,
                'email' => trim($request->email),
                'password' => Hash::make($request->password),
                'role' => 'pending', // <-- Add this
            ]);
        }
        catch (\Exception $e) {
            dd($e->getMessage());
        }

        //Redirect to login if successful
        return redirect()->route('login.form')->with('success', 'User registered successfully.');
    }

    public function showLoginForm(){
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => ['required','string'],
            'password' => ['required','string'],
        ]);

        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password])) {
            $user = Auth::user();

            switch ($user->role) {
                case 'pending':
                    Auth::logout();
                    return redirect()->route('login.form')
                        ->withErrors(['login_error' => 'Your account is pending admin approval.']);

                case 'user':
                case 'admin':
                    return redirect()->route('timetables.index'); // shared dashboard
            }
        }

        return redirect()->back()->withErrors([
            'login_error' => 'Credentials do not match our records.',
        ]);
    }


    public function logout(Request $request){
        //Log out user
        Auth::logout();

        // invalidate the session
        $request->session()->invalidate();

        // prevent CSRF attacks
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'User has logged out.');
    }

}
