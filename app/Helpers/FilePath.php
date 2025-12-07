<?php

namespace App\Helpers;

class FilePath
{
    /**
     * Get the Python executable path for both Windows (local)
     * and Linux (Laravel Cloud).
     */
    public static function getPythonPath(): string
    {
        $venvBase = base_path('venv');

        if (PHP_OS_FAMILY === 'Windows') {
            // venv\Scripts\python.exe
            $python = $venvBase . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
        } else {
            // venv/bin/python or venv/bin/python3 on Linux
            $python = $venvBase . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';

            if (!file_exists($python)) {
                $alt = $venvBase . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python3';
                if (file_exists($alt)) {
                    $python = $alt;
                }
            }
        }

        return $python;
    }

}
