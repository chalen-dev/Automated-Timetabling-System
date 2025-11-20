<?php

namespace App\Livewire\Buttons;

use Livewire\Component;

class Show extends Component
{
    public string $route;
    public $params;

    public function mount
    (
        string $route = '',
        $params = []
    )
    {
        $this->route = $route;
        $this->params = $params;
    }

    public function render()
    {
        return view('livewire.buttons.show');
    }
}
