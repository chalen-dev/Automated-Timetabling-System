<?php

namespace App\Livewire\Input;

use Livewire\Component;

class RadioGroup extends Component
{
    public $name;

    public $label;
    public $options;
    public $value;
    public $isRequired;

    public function mount(
        $name,
        $label = null,
        $options = [],
        $value = null,
        $isRequired = false
    )
    {
        $this->name = $name;

        $this->label = $label;
        $this->options = $options;
        $this->value = $value;
        $this->isRequired = $isRequired;
    }
    public function render()
    {
        return view('livewire.input.radio-group');
    }
}
