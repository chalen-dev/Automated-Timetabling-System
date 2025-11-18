<?php

namespace App\Livewire\Input;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Select extends Component
{
    public $name;

    public $label;
    public $value;
    public $options;
    public $default;
    public $disabled;
    public $class;
    public $isRequired;

    public function mount(
        $name,
        $label = '',
        $value = null,
        $options = [],
        $default = null,
        $disabled = false,
        $class = '',
        $isRequired = false
    ): void
    {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;
        $this->options = $options;
        $this->default = $default;
        $this->disabled = $disabled;
        $this->class = $class;
        $this->isRequired = $isRequired;
    }

    public function render(): View
    {
        return view('livewire.input.select');
    }
}
