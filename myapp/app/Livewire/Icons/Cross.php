<?php

namespace App\Livewire\Icons;

use Livewire\Component;

class Cross extends Component
{
    public $class;

    public function mount($class = 'text-red-600')
    {
        $this->class = $class;
    }
    public function render()
    {
        return view('livewire.icons.cross');
    }
}
