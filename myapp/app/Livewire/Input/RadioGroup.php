<?php

namespace App\Livewire\Input;

use Livewire\Component;

class RadioGroup extends Component
{
    public $label;
    public $name;
    public $options;
    public $value;

    public function mount($label = null, $name, $options = [], $value = null)
    {
        $this->label = $label;
        $this->name = $name;
        $this->options = $options;
        $this->value = $value;

    }
    public function render()
    {
        return view('livewire.input.radio-group');
    }
}
