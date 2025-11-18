<?php

namespace App\Http\Controllers\Records;

use App\Http\Controllers\Controller;
use App\Models\Records\UserLog;

class UserLogController extends Controller
{
    public function index()
    {
        $user = auth()->user();

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

        return view('records.user-logs', compact('logs'));
    }

}
