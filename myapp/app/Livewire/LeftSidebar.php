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
    public bool $open = false;

    //Mount works only once
    public function mount(){
        $this->open = false;
    }

    //Listen for 'toggleSidebar' from header
    #[On('toggleSidebar')]
    public function toggle(){
        $this->open = !$this->open;
    }

    //Render works every render so put the currentRouteName here
    public function render()
    {
        $this->currentRouteName = Route::currentRouteName();
        if (Str::is('timetables.*.*', $this->currentRouteName)) {
            $this->open = true;
        }
        return view('livewire.left-sidebar');

    }


}
