<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function create()
    {
        return view('users.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = (string) $request->input('email');

        // Always respond the same to prevent email enumeration
        $successMsg = 'If that email exists in our system, a password reset link has been sent.';

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->with('success', $successMsg);
        }

        // Generate reset token using Laravel password broker
        $token = Password::broker()->createToken($user);

        $resetUrl = url(route('password.reset', ['token' => $token], false))
            . '?email=' . urlencode($user->email);

        try {
            Mail::to($user->email)->send(new ForgotPasswordMail($resetUrl));
        } catch (\Throwable $e) {
            return back()->withErrors([
                'email' => 'Failed to send reset email. Please try again later.',
            ]);
        }

        return back()->with('success', $successMsg);
    }

    public function edit(string $token, Request $request)
    {
        $email = (string) $request->query('email', '');

        return view('users.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function update(Request $request)
    {
        // Keep email required, but it will come from a hidden input now
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->password = bcrypt($request->password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login.form')->with('success', 'Password reset successful. You can now login.')
            : back()->withErrors(['email' => __($status)]);
    }
}
