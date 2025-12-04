<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    public function index(){
        if (!auth()->check()) {
            return redirect()->route('login.form');
        }

        $user = auth()->user();

        if ($user->role === 'pending') {
            Auth::logout();
            return redirect()->route('login.form')
                ->withErrors(['login_error' => 'Your account is pending admin approval.']);
        }

        // Admins and normal users go to the same dashboard
        return redirect()->route('timetables.index');
    }
}
