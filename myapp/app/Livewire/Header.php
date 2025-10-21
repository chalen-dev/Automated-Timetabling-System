<?php

namespace App\Livewire;

use App\Models\Timetable;
use Illuminate\Support\Facades\Route;
use Livewire\Component;


class Header extends Component
{
    public string $currentRouteName;

    public function toggleSidebar(){
        $this->dispatch('toggleSidebar');
    }
    public function render()
    {
        $this->currentRouteName = Route::currentRouteName();
        return view('livewire.header');
    }

}
