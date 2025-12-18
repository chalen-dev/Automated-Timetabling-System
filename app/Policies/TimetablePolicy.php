<?php

namespace App\Policies;

use App\Models\Records\Timetable;
use App\Models\Users\User;

class TimetablePolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null; // fall back to specific ability checks
    }
    /**
     * View a timetable
     */
    public function view(User $user, Timetable $timetable): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($timetable->user_id === $user->id) {
            return true;
        }

        if ($timetable->visibility === 'public') {
            return true;
        }

        if ($timetable->visibility === 'restricted') {

            if (
                $timetable->allowedUsers()
                    ->where('users.id', $user->id)
                    ->exists()
            ) {
                return true;
            }

            if (
                $user->academic_program_id &&
                $timetable->allowedPrograms()
                    ->where('academic_programs.id', $user->academic_program_id)
                    ->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update (edit) a timetable
     * OWNER ONLY
     */
    public function update(User $user, Timetable $timetable): bool
    {
        return $timetable->user_id === $user->id;
    }

    /**
     * Delete a timetable
     * OWNER ONLY
     */
    public function delete(User $user, Timetable $timetable): bool
    {
        return $timetable->user_id === $user->id;
    }

    /**
     * Copy a timetable
     * Anyone who can VIEW can COPY
     */
    public function copy(User $user, Timetable $timetable): bool
    {
        return $this->view($user, $timetable);
    }
    public function manageAccess(User $user, Timetable $timetable): bool
    {
        // ONLY OWNER can change visibility & access
        return $timetable->user_id === $user->id;
    }

    public function editRecords(User $user, Timetable $timetable): bool
    {
        // Admin always allowed
        if ($user->role === 'admin') {
            return true;
        }

        // Owner always allowed
        if ($timetable->user_id === $user->id) {
            return true;
        }

        // Non-owner allowed only if timetable permits it
        return (bool) $timetable->allow_non_owner_record_edit;
    }
    public function editTimetable(User $user, Timetable $timetable): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($timetable->user_id === $user->id) {
            return true;
        }

        return (bool) $timetable->allow_non_owner_timetable_edit;
    }
}
