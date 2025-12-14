<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function showRegisterForm(){
        $academicPrograms = AcademicProgram::all();
        return view('users.register',[
            'academicPrograms' => $academicPrograms
        ]);
    }

    public function register(Request $request)
    {
        // Basic validation for everything except password
        $request->validate([
            'name' => ['required', 'string', 'max:20', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'first_name' => ['required', 'string', 'max:50', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'last_name'  => ['required', 'string', 'max:50', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'email' => [
                'required',
                'string',
                'email',
                'unique:users',
                'regex:/^[a-z]\.[a-z]+\.([0-9]{6})\.tc@umindanao\.edu\.ph$/',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'academic_program_id' => ['required', 'exists:academic_programs,id'],
        ]);

        $password = $request->password;
        $passwordErrors = [];

        // Custom password format checks
        if (!preg_match('/[A-Z]/', $password)) {
            $passwordErrors[] = 'Password must contain at least one uppercase letter.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $passwordErrors[] = 'Password must contain at least one number.';
        }
        if (!preg_match('/[!@#$%^&*()\-_+=]/', $password)) {
            $passwordErrors[] = 'Password must contain at least one special character (!@#$%^&*()-_+=).';
        }

        if (!empty($passwordErrors)) {
            return redirect()->back()->withErrors(['password' => $passwordErrors])->withInput();
        }

        // Create user
        try {
            User::create([
                'name' => $request->name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => trim($request->email),
                'password' => Hash::make($password),
                'role' => 'pending',
                'academic_program_id' => $request->academic_program_id,
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['register_error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('login.form')->with('success', 'User registered successfully.');
    }


    public function showLoginForm(){
        return view('users.login');
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'User has logged out.');
    }
}
