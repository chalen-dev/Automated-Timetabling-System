<?php

namespace App\Livewire\Icons;

use Livewire\Component;

class Check extends Component
{
    public $class;

    public function mount($class = 'text-green-600')
    {
        $this->class = $class;
    }
    public function render()
    {
        return view('livewire.icons.check');
    }
}
