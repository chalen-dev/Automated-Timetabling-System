<?php

namespace App\Livewire\Input;

use Livewire\Component;

class TextArea extends Component
{
    public $label;
    public $name;
    public $value;

    public $default;
    public $rows;
    public $isRequired;

    public function mount(
        $label,
        $name,
        $default = '',
        $value = '',
        $rows = 4,
        $isRequired = false
    )
    {
        $this->label = $label;
        $this->name = $name;
        $this->default = $default;

        $this->value = $value;
        $this->rows = $rows;
        $this->isRequired = $isRequired;
    }
    public function render()
    {
        return view('livewire.input.text-area');
    }
}
