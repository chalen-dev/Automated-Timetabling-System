<?php

namespace App\Livewire\Input;

use Livewire\Component;

class Text extends Component
{
    public $label;
    public $name;
    public $value;

    public function mount($label, $name, $value = '')
    {
        $this->label = $label;
        $this->name = $name;
        $this->value = $value;
    }
    public function render()
    {
        return view('livewire.input.text');
    }
}
