<?php

namespace App\Helpers;

class FormatHelper
{
    public static function logDetails(string $action, string $modelType = '')
    {
        $actions = [
            'login' => 'logged in',
            'logout' => 'logged out',
            'update_profile' => 'Updated their profile',
            'create' => "Visited {$modelType} creation page.",
            'store' => "Created new {$modelType}",
            'index' => "Visited {$modelType} list.",
            'show' => "Visited {$modelType} details.",
            'edit' => "Visited {$modelType} edit page.",
            'delete' => "Deleted {$modelType}.",
            'update' => "Updated {$modelType}.",
            'timetable_edit' => 'Visited timetabling editing window.',
            'update_academic_term' => "Updated academic term for {$modelType}.",
        ];

        // Return the friendly message, or the action itself if not defined
        return $actions[$action] ?? $action;
    }

}
