<?php

namespace App\Livewire;

use App\Models\Timetable;
use Livewire\Component;

class Header extends Component
{
    public Timetable $timetable;
    public function mount(Timetable $timetable){
        $this->timetable = $timetable;
    }
    public function toggleSidebar(){
        $this->dispatch('toggleSidebar');
    }
    public function render()
    {
        return view('livewire.header');
    }

}
