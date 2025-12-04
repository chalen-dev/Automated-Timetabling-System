<?php

namespace App\Helpers;

use App\Models\Users\UserLog;

class Logger
{
    public static function log($action, $model_type, $details = null)
    {
        if(auth()->check()) {

            if (is_object($details) || is_array($details))
                $details = json_encode($details);


            UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => $model_type,
                'details' => $details,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
