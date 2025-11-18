<?php

namespace App\Livewire\Input\Auth;

use Livewire\Component;

class Text extends Component
{
    public $label;
    public $type;
    public $placeholder;
    public $name;

    public $value;

    public function mount($label, $type, $placeholder, $name, $value = ''){
        $this->label = $label;
        $this->type = $type;
        $this->placeholder = $placeholder;
        $this->name = $name;

        $this->value = $value;
    }
    public function render()
    {
        return view('livewire.input.auth.text');
    }
}
