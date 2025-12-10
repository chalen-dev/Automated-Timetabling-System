<?php

namespace App\Livewire\Input;

use Livewire\Component;

class SingleCheckbox extends Component
{
    public $name;
    public $label;
    public $value;
    public $class;
    public $checkboxStyle;
    public function mount
    (
        $name,
        $label = '',
        $value = 1,
        $class = '',
        $checkboxStyle = ''
    ) : void
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->class = $class;
        $this->checkboxStyle = $checkboxStyle;
    }
    public function render()
    {
        return view('livewire.input.single-checkbox');
    }
}
