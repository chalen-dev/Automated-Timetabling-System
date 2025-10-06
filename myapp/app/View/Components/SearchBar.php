<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SearchBar extends Component
{
    public $action;
    public $name;
    public $placeholder;
    public $buttonText;

    public function __construct($action, $name = 'search', $placeholder = 'Search...', $buttonText = 'Search')
    {
        $this->action = $action;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->buttonText = $buttonText;
    }

    public function render()
    {
        return view('components.search-bar');
    }
}
