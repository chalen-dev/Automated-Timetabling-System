<?php

namespace App\Livewire\Text;

use Livewire\Component;

class Label extends Component
{
    public $name;

    public $label;
    public $is_required;

    public function mount
    (
        $name,
        $label = '',
        $is_required = false,
    ): void
    {
        $this->name = $name;
        $this->label = $label;
        $this->$is_required = $is_required;
    }
    public function render()
    {
        return view('livewire.text.label');
    }
}
