<?php

namespace App\Livewire\Trays;

use Livewire\Component;

class SessionGroupFilters extends Component
{
    /** @var \Illuminate\Support\Collection|array */
    public $sessionGroupsByProgram;

    public function mount($sessionGroupsByProgram)
    {
        $this->sessionGroupsByProgram = $sessionGroupsByProgram;
    }

    public function render()
    {
        return view('livewire.trays.session-group-filters');
    }
}
