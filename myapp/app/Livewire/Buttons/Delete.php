<?php

namespace App\Livewire\Buttons;

use Livewire\Component;
use SweetAlert2\Laravel\Swal;

class Delete extends Component
{
    public $action;
    public $params;
    public $item_name;
    public $btnType;
    public $class;

    public function mount($action, $params = [], $item_name, $btnType = 'normal', $class = '')
    {
        $this->action = $action;
        $this->params = $params;
        $this->itemName = $item_name;
        $this->btnType = $btnType;
        $this->class = $class;
    }

    public function render()
    {
        return view('livewire.buttons.delete');
    }
}
