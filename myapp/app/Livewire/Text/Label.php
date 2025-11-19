<?php

namespace App\Livewire\Text;

use Livewire\Component;

class Label extends Component
{
    public $label;
    public $is_required;

    public function mount
    (
        $label = '',
        $is_required = false,
    ): void
    {
        $this->label = $label;
        $this->$is_required = $is_required;
    }
    public function render()
    {
        return view('livewire.text.label');
    }
}
