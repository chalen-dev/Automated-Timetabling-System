<?php

namespace App\Livewire;

use App\Models\Timetable;
use Livewire\Component;

class LeftSidebar extends Component
{
    public bool $open = false;
    public Timetable $timetable;

    public function mount(Timetable $timetable){
        $this->timetable = $timetable;
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
