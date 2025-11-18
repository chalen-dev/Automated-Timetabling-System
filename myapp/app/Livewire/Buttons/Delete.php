<?php

namespace App\Livewire\Buttons;

use Livewire\Component;
use SweetAlert2\Laravel\Swal;

class Delete extends Component
{

    public $action;
    public $item_name;

    public $params;
    public $btnType;
    public $class;

    public function mount($action, $item_name, $params = [],  $btnType = 'normal', $class = '')
    {
        $this->action = $action;
        $this->item_name = $item_name;

        $this->params = $params;
        $this->btnType = $btnType;
        $this->class = $class;
    }

    public function render()
    {
        return view('livewire.buttons.delete');
    }
}
