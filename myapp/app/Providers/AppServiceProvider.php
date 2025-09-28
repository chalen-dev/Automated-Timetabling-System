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
        //THIS CODE ALWAYS COMPACTS THE TIMETABLES COLLECTION TO THE VIEW, SINCE MY TIMETABLES INDEX PAGE IS ACTS AS THE LANDING PAGE/DASHBOARD
        View::composer('records.timetables.index', function ($view) {
            $view->with('timetables', Timetable::all());
        });
    }
}
