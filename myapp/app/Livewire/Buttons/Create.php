<?php

namespace App\Livewire\Buttons;

use Livewire\Component;

class Create extends Component
{
    public string $route;
    public bool $submit;
    public string $text;
    public string $class;

    public function mount(
        string $route = '',
        bool $submit = false,
        string $text = 'Create',
        string $class = ''
    ): void
    {
        $this->route = $route;
        $this->submit = $submit;
        $this->text = $text;
        $this->class = $class;
    }
    public function render()
    {
        return view('livewire.buttons.create');
    }
}
