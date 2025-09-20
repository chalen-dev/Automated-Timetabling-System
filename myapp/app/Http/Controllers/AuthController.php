<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{

    public function showRegisterForm(){
        return view('auth.register');
    }
    public function register(Request $request){

        $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_]+$/'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

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

        return redirect()->route('login.form');
    }

    public function showLoginForm(){
        return view('auth.login');
    }

    public function login(Request $request){
        $request->validate([
            'login' => ['required', 'string', 'login'],
            'password' => ['required', 'string'],
        ]);

        /*
        //Determine if email or username
        $fieldType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        //Attempt to login
        if (Auth::attempt([$fieldType => $request->login, 'password' => $request->password])) {
            //if login successful, redirect here
            return redirect()->intended('/dashboard');
        }
        //if login failed, redirect back with error message
        return redirect()->back()->withErrors([
            'login' => 'Credentials provided do not match our records.',
        ]);

        */
    }


}
