<?php

namespace App\Helpers;

use App\Models\Records\Timetable;
use App\Models\Timetabling\CourseSession;
use App\Models\Timetabling\SessionGroup;
use App\Models\Timetabling\TimetableRoom;

class Records
{
    public static function isNotEmpty(Timetable $timetable): bool
    {
        $hasSessionGroups = SessionGroup::where('timetable_id', $timetable->id)->exists();

        $hasCourseSessions = CourseSession::whereHas('sessionGroup', function ($q) use ($timetable) {
            $q->where('timetable_id', $timetable->id);
        })
            ->exists();

        $hasTimetableRooms = TimetableRoom::where('timetable_id', $timetable->id)->exists();

        return $hasSessionGroups && $hasCourseSessions && $hasTimetableRooms;
    }

    public static function isSessionGroupsNotEmpty(Timetable $timetable): bool
    {
        $hasSessionGroups = SessionGroup::where('timetable_id', $timetable->id)->exists();
        if ($hasSessionGroups) return true;
        return false;
    }

    public static function isCourseSessionsNotEmpty(Timetable $timetable): bool
    {
        $hasCourseSessions = CourseSession::whereHas('sessionGroup', function ($q) use ($timetable) {
            $q->where('timetable_id', $timetable->id);
        })
            ->exists();
        if ($hasCourseSessions) return true;
        return false;
    }

    public static function isTimetableRoomsNotEmpty(Timetable $timetable): bool
    {
        $hasTimetableRooms = TimetableRoom::where('timetable_id', $timetable->id)->exists();
        if ($hasTimetableRooms) return true;
        return false;
    }
}
