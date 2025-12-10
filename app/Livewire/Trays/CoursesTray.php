<?php

namespace App\Livewire\Trays;

use App\Models\Timetabling\SessionGroup;
use Livewire\Component;

class CoursesTray extends Component
{

    public $sessionGroups;

    public function mount()
    {
        $this->sessionGroups = SessionGroup::all();
    }

    public function render()
    {
        return view('livewire.trays.courses-tray');
    }
}
