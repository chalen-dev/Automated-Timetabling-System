<?php

namespace App\Livewire\Timetabling\Editor;

use Livewire\Component;
use App\Models\Records\Timetable;
use Illuminate\Support\Collection;

class TimetableCanvas extends Component
{
    public Timetable $timetable;

    /** @var array<int, string> */
    public array $timeslots = [];

    public function mount(Timetable $timetable): void
    {
        // Timetable injected from Blade: :timetable="$timetable"
        $this->timetable = $timetable;

        // Generate timeslots (7:00 AM – 9:30 PM, every 30 minutes)
        $start = strtotime('07:00');
        $end   = strtotime('21:30');

        while ($start <= $end) {
            $this->timeslots[] = date('g:i A', $start);
            $start = strtotime('+30 minutes', $start);
        }
    }

    public function render()
    {
        // 🔴 SIMPLE: pull rooms via the relationship on Timetable.
        // This uses timetable_rooms under the hood.
        $rooms = $this->timetable
            ->rooms()                // belongsToMany(Room::class, 'timetable_rooms', 'timetable_id', 'room_id')
            ->orderBy('room_name')
            ->get();

        // If you want to sanity check:
        // dd($this->timetable->id, $rooms->pluck('room_name'));

        return view('livewire.timetabling.editor.timetable-canvas', [
            'rooms' => $rooms,
        ]);
    }
}
