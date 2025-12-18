<?php

namespace App\Livewire\Partials;

use App\Models\Records\Timetable;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class LeftSidebars extends Component
{
    public string $currentRouteName;

    /** @var Timetable|null */
    public ?Timetable $activeTimetable = null;

    public function mount()
    {
        $this->currentRouteName = Route::currentRouteName();

        $route = request()->route();
        if ($route) {
            $param = $route->parameter('timetable');

            if ($param instanceof Timetable) {
                $this->activeTimetable = $param;
            } elseif (is_numeric($param)) {
                $this->activeTimetable = Timetable::find($param);
            }
        }
    }

    public function render()
    {
        return view('livewire.partials.left-sidebars');
    }
}
