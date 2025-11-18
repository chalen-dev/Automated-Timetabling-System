<?php

if (!function_exists('friendly_action')) {
    function friendly_action($action, $modelType)
    {
        $output = match ($action) {
            'login' => 'logged in',
            'logout' => 'logged out',
            'update_profile' => 'updated their profile',
            'create' => 'visited ' . $modelType . ' creation page.',
            'store' => 'created new ' . $modelType,
            'index' => 'visited ' . $modelType . ' list.',
            'show' => 'visited ' . $modelType . ' details.',
            'edit' => 'visited ' . $modelType . ' edit page.',
            'delete' => 'deleted ' . $modelType . '.',
            'update' => 'updated ' . $modelType . '.',
            default => $action,
        };
        return $output;
    }
}
