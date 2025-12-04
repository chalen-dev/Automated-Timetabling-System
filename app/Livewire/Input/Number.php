<?php

namespace App\Livewire\Input;

use Livewire\Component;

class Number extends Component
{
    public $name;
    public $default;
    public $min;
    public $max;
    public $step;
    public $label;
    public $value;
    public $isRequired;

    public function mount(
        $name = '',
        $default = 0,
        $min = null,
        $max = null,
        $step = 1,
        $label = '',
        $value = '',
        $isRequired = false
    ) : void
    {
        $this->name = $name;
        $this->default = $default;
        $this->min = $min;
        $this->max = $max;
        $this->step = $step;
        $this->label = $label;
        $this->value = $value;
        $this->isRequired = $isRequired;
    }

    public function render()
    {
        return view('livewire.input.number');
    }
}
