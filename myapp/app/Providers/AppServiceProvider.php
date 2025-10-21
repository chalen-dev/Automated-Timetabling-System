<?php

namespace App\Providers;

use App\Models\Timetable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //THIS CODE ALWAYS COMPACTS THE TIMETABLES COLLEC
        View::composer('*', function ($view) {
            $route = request()->route();
            $timetable = null;

            if ($route) {
                $param = $route->parameter('timetable');
                if ($param instanceof Timetable) {
                    $timetable = $param;
                } elseif (is_numeric($param)) {
                    $timetable = Timetable::find($param);
                }
            }

            $view->with('timetable', $timetable);
        });

    }
}
