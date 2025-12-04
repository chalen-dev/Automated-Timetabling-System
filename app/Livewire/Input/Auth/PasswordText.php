<?php

namespace App\Livewire\Input\Auth;

use Livewire\Component;

class PasswordText extends Component
{
    public $label;
    public $type;
    public $elementId;
    public $placeholder;
    public $name;

    public $value;
    public $toggleId;
    public $isRequired;

    public function mount(
        $label,
        $type,
        $elementId,
        $placeholder,
        $name,
        $value = 'value',
        $toggleId = 'toggleBtn',
        $isRequired = false
    )
    {
        $this->label = $label;
        $this->type = $type;
        $this->elementId = $elementId;
        $this->placeholder = $placeholder;
        $this->name = $name;

        $this->value = $value;
        $this->toggleId = $toggleId;
        $this->isRequired = $isRequired;
    }
    public function render()
    {
        return view('livewire.input.auth.password-text');
    }
}
