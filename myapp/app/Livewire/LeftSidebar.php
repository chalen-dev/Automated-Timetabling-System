<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class LeftSidebar extends Component
{
    public bool $open = false;

    public function mount(){
        $this->open = false;
    }

    //Listen for 'toggleSidebar' from header
    #[On('toggleSidebar')]
    public function toggle(){
        $this->open = !$this->open;
    }

    public function render()
    {
        return view('livewire.left-sidebar');
    }


}
