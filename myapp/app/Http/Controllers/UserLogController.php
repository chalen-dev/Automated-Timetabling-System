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

        // Paginate first, then group logs by date
        $logs = UserLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Group the logs on this page by date
        $groupedLogs = $logs->getCollection()->groupBy(function ($log) {
            return $log->created_at->format('Y-m-d');
        });

        // Replace paginator collection with grouped data
        $logs->setCollection(collect($groupedLogs));

        return view('admin.user-logs', compact('logs'));
    }

}
