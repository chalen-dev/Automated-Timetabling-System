<?php

namespace App\Livewire\Input;

use Livewire\Component;

class Text extends Component
{
    public $label;
    public $name;
    public $value;
    public $class;

    public function mount($label, $name, $value = '', $class = '')
    {
        $this->label = $label;
        $this->name = $name;
        $this->value = $value;
        $this->class = $class;
    }
    public function render()
    {
        return view('livewire.input.text');
    }
}
