<?php

namespace App\Livewire\Input;

use Livewire\Component;

class RadioGroup extends Component
{
    public $name;

    public $label;
    public $options;
    public $value;

    public function mount($name, $label = null, $options = [], $value = null)
    {
        $this->name = $name;

        $this->label = $label;
        $this->options = $options;
        $this->value = $value;

    }
    public function render()
    {
        return view('livewire.input.radio-group');
    }
}
