<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\users\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function showPending()
    {
        $pendingUsers = User::whereIn('role', ['pending', 'authorized'])->get();
        return view('admin.pending_users', compact('pendingUsers'));
    }

    public function approve(User $user)
    {
        $user->role = 'user';
        $user->save();

        return redirect()->back()->with('success', 'User approved.');
    }
    public function toggleAuthorize($id)
    {
        $user = User::findOrFail($id);

        // Toggle between authorized and not authorized
        if ($user->role === 'authorized') {
            $user->role = 'pending';
        } else {
            $user->role = 'authorized';
        }

        $user->save();

        return redirect()->back()->with('success', "{$user->name} is now {$user->role}.");
    }

    public function declineUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', "User {$user->name} has been declined and removed.");
    }


}
