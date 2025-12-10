<?php

namespace App\Livewire\Timetabling;

use App\Models\Records\Timetable;
use Livewire\Component;

class TimetableCanvas extends Component
{
    public Timetable $timetable;

    /** @var array<int, string> */
    public array $timeslots = [];

    /**
     * Rooms passed from Blade (optional). If not provided, we will fetch ordered rooms from DB.
     * No type so Livewire can accept array/collection.
     *
     * Example Blade usage: <livewire:timetabling.editor.timetable-canvas :timetable="$timetable" :rooms="$rooms" />
     */
    public $rooms;

    /**
     * Mount the component.
     *
     * NOTE: $rooms argument is optional and will be provided when the Blade includes :rooms="$rooms".
     * Livewire will pass that value here so we can use the controller-authoritative ordering.
     */
    public function mount(Timetable $timetable, $rooms = null): void
    {
        // Timetable injected from Blade: :timetable="$timetable"
        $this->timetable = $timetable;

        // If rooms were passed from Blade (controller), normalize and store them.
        if ($rooms !== null) {
            // Normalize arrays to collection for consistent usage in view
            $this->rooms = collect($rooms);
        } else {
            $this->rooms = null; // will be loaded in render() fallback
        }

        // Generate timeslots (7:00 AM – 9:30 PM, every 30 minutes)
        $start = strtotime('07:00');
        $end   = strtotime('21:00');

        while ($start <= $end) {
            $this->timeslots[] = date('g:i A', $start);
            $start = strtotime('+30 minutes', $start);
        }
    }

    public function render()
    {
        // If rooms were not provided in mount(), query DB with desired ordering:
        if ($this->rooms === null) {
            // Use timetable->rooms() relation (belongsToMany) and enforce ordering:
            $this->rooms = $this->timetable
                ->rooms() // belongsToMany(Room::class, 'timetable_rooms', 'timetable_id', 'room_id')
                ->orderByRaw("
                    CASE
                        WHEN LOWER(room_type) = 'comlab' THEN 0
                        WHEN LOWER(room_type) = 'lecture' THEN 1
                        ELSE 2
                    END
                ")
                ->orderBy('room_name', 'asc')
                ->get();
        }

        return view('livewire.timetabling.timetable-canvas', [
            'rooms' => $this->rooms,
            'timeslots' => $this->timeslots,
            'timetable' => $this->timetable,
        ]);
    }
}
