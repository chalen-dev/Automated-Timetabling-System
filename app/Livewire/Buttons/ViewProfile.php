<?php

namespace App\Livewire\Buttons;

use Livewire\Component;

class ViewProfile extends Component
{
    public $user;

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function render()
    {
        return view('livewire.buttons.view-profile');
    }
}
