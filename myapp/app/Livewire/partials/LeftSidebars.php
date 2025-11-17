<?php

namespace App\Livewire\partials;

use Illuminate\Support\Facades\Route;
use Livewire\Component;

class LeftSidebars extends Component
{
    public string $currentRouteName;

    //Render works every render so put the currentRouteName here
    public function render()
    {
        $this->currentRouteName = Route::currentRouteName();
        return view('livewire.partials.left-sidebars');
    }


}
