<?php

namespace App\Livewire\Timetabling\Editor;

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
        return view('livewire.timetabling.editor.courses-tray');
    }
}
