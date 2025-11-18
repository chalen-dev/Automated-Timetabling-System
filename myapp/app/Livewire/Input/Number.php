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

    public function mount($name, $default, $min, $max, $step, $label = '', $value = '')
    {
        $this->name = $name;
        $this->default = $default;
        $this->min = $min;
        $this->max = $max;
        $this->step = $step;

        $this->label = $label;
        $this->value = $value;
    }

    public function render()
    {
        return view('livewire.input.number');
    }
}
