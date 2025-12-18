<?php

namespace App\Models\Records;

use App\Models\Timetabling\SessionGroup;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $fillable = [
        'timetable_name',
        'semester',
        'academic_year',
        'timetable_description',
        'user_id',
        'visibility',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function allowedUsers()
    {
        return $this->belongsToMany(
            User::class,
            'timetable_user_access'
        )->withTimestamps();
    }

    public function allowedPrograms()
    {
        return $this->belongsToMany(
            AcademicProgram::class,
            'timetable_academic_program'
        )->withTimestamps();
    }

    public function sessionGroups(){
        return $this->hasMany(SessionGroup::class);
    }

    public function professors()
    {
        return $this->belongsToMany(
            Professor::class,
            'timetable_professors',
            'timetable_id',
            'professor_id'
        );
    }

    public function rooms(){
        return $this->belongsToMany(
            Room::class,
            'timetable_rooms',
            'timetable_id',
            'room_id'
        );
    }

    public function isVisibleTo(\App\Models\Users\User $user): bool
    {
        // Owner always sees
        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->visibility === 'public') {
            return true;
        }

        if ($this->visibility === 'restricted') {
            // Direct user access
            if ($this->allowedUsers()->where('users.id', $user->id)->exists()) {
                return true;
            }

            // Academic program access
            if (
                $user->academic_program_id &&
                $this->allowedPrograms()
                    ->where('academic_programs.id', $user->academic_program_id)
                    ->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {

            $q->where('user_id', $user->id)
                ->orWhere('visibility', 'public')
                ->orWhere(function ($q) use ($user) {
                    $q->where('visibility', 'restricted')
                        ->whereHas('allowedUsers', fn ($q) =>
                        $q->where('users.id', $user->id)
                        );
                })
                ->orWhere(function ($q) use ($user) {
                    if ($user->academic_program_id) {
                        $q->where('visibility', 'restricted')
                            ->whereHas('allowedPrograms', fn ($q) =>
                            $q->where('academic_programs.id', $user->academic_program_id)
                            );
                    }
                });

        });
    }
}
