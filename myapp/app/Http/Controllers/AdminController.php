<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function showPending()
    {
        $pendingUsers = User::where('role', 'pending')->get();
        return view('admin.pending_users', compact('pendingUsers'));
    }

    public function approve(User $user)
    {
        $user->role = 'user';
        $user->save();

        return redirect()->back()->with('success', 'User approved.');
    }
}
