<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Only allow admins
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $logs = UserLog::with('user')->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.user-logs', compact('logs')); // Adjust path if needed
    }
}
