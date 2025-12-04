<?php

namespace App\Livewire\Icons;

use Livewire\Component;

class Lock extends Component
{
    public $class;

    public function mount($class = 'text-gray-600')
    {
        $this->class = $class;
    }
    public function render()
    {
        return view('livewire.icons.lock');
    }
}
