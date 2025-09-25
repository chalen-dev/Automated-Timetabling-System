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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        //Create user, query to db
        try {
            User::create([
                'name' => $request->name,
                'email' => trim($request->email),
                'password' => Hash::make($request->password),
            ]);
        }
        catch (\Exception $e) {
            dd($e->getMessage());
        }

        //Redirect to login if successful
        return redirect()->route('login.form');
    }

    public function showLoginForm(){
        return view('auth.login');
    }

    public function login(Request $request){

        //Input validation
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        //Determines if inputted username or email
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        //Attempt to login. This code automatically gives the user Auth when successful
        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password])) {
            //if login successful, redirect here
            return redirect()->intended('/Dashboard');
        }
        //if login failed, redirect back with error message
        return redirect()->back()->withErrors([
            'login' => 'Credentials provided do not match our records.',
        ]);
    }

    public function logout(Request $request){
        //Log out user
        Auth::logout();

        // invalidate the session
        $request->session()->invalidate();

        // prevent CSRF attacks
        $request->session()->regenerateToken();

        return redirect()->route('default');
    }

}
