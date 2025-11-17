<?php

namespace App\Livewire\partials;

use Illuminate\Support\Facades\Route;
use Livewire\Component;


class Headers extends Component
{
    public string $currentRouteName;
    public function render()
    {
        $this->currentRouteName = Route::currentRouteName();
        return view('livewire.partials.headers');
    }

}
