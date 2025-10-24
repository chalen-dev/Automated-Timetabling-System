<?php

namespace App\Livewire;

use App\Models\Timetable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;

class LeftSidebar extends Component
{
    public string $currentRouteName;

    //Render works every render so put the currentRouteName here
    public function render()
    {
        $this->currentRouteName = Route::currentRouteName();
        return view('livewire.left-sidebar');
    }


}
