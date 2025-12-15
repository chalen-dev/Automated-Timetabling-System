<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        return view('users.verify-email');
    }

    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('timetables.index');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent.');
    }

    public function verify(Request $request, string $id, string $hash)
    {
        $user = $request->user();

        // Safety: ensure link matches current logged-in user
        if ((string) $user->getKey() !== (string) $id) {
            abort(403);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('timetables.index')->with('success', 'Email already verified.');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect()->route('timetables.index')->with('success', 'Email verified successfully.');
    }
}
