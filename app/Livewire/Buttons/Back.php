<?php

namespace App\Livewire\Buttons;

use Livewire\Component;

class Back extends Component
{
    public string $route;
    public string $text;

    public function mount
    (
        string $route = 'url()->previous()',
        string $text = 'Back'
    )
    {
        $this->route = $route;
        $this->text = $text;
    }

    public function render()
    {
        return view('livewire.buttons.back');
    }
}
