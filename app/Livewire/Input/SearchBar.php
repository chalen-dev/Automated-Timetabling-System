<?php

namespace App\Livewire\Input;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class SearchBar extends Component
{
    public $action;

    public $name;
    public $placeholder;
    public $buttonText;

    public function mount(
        $action,
        $name = 'search',
        $placeholder = 'Search...',
        $buttonText = 'Search'
    ): void
    {
        $this->action = $action;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->buttonText = $buttonText;
    }
    public function render(): View
    {
        return view('livewire.input.search-bar');
    }
}
