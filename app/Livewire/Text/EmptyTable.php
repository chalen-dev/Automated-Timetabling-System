<?php

namespace App\Livewire\Text;

use Livewire\Component;

class EmptyTable extends Component
{
    public $message;
    public $class;
    public $textStyle;

    public function mount(
        $message = 'No data found',
        $class = 'text-gray-600',
        $textStyle = 'italic')
    {
        $this->message = $message;
        $this->class = $class;
        $this->textStyle = $textStyle;
    }
    public function render()
    {
        return view('livewire.text.empty-table');
    }
}
