<?php

namespace App\Models\Users;

use App\Models\Records\AcademicProgram;
use App\Models\Records\Timetable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'academic_program_id',
        'profile_photo_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function accessibleTimetables()
    {
        return $this->belongsToMany(
            Timetable::class,
            'timetable_user_access'
        )->withTimestamps();
    }


    public function academicProgram()
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    /**
     * Get masked password (always 8 bullets)
     */
    public function getMaskedPasswordAttribute(): string
    {
        return str_repeat('•', 8);
    }

    /**
     * Get masked email
     */
    public function getMaskedEmailAttribute(): string
    {
        $email = $this->email;
        $parts = explode('@', $email);
        $username = $parts[0] ?? '';
        $domain = $parts[1] ?? '';

        $maskedUsername = substr($username, 0, 2) . str_repeat('•', max(strlen($username) - 2, 0));

        $domainParts = explode('.', $domain);
        $maskedDomain = '';
        foreach ($domainParts as $i => $part) {
            if ($i === count($domainParts) - 1) {
                $maskedDomain .= $part;
            } else {
                $maskedDomain .= str_repeat('•', strlen($part)) . '.';
            }
        }

        return $maskedUsername . '@' . $maskedDomain;
    }

    /**
     * Canonical storage key:
     * profiles/{user_id}/{user_id}.{ext}
     */
    public function profilePhotoKey(string $extension): string
    {
        $ext = strtolower(ltrim($extension, '.'));
        if ($ext === '') {
            $ext = 'png';
        }

        return 'profiles/' . $this->id . '/' . $this->id . '.' . $ext;
    }

    /**
     * local => public disk
     * deployed => facultime disk (bucket)
     */
    public function profilePhotoDisk(): string
    {
        return app()->environment('local') ? 'public' : 'facultime';
    }

    /**
     * URL used by Blade: $user->profile_photo_url
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        $path = (string) ($this->profile_photo_path ?? '');
        if ($path !== '') {
            $disk = $this->profilePhotoDisk();

            try {
                if (Storage::disk($disk)->exists($path)) {
                    return Storage::disk($disk)->url($path);
                }
            } catch (Throwable $e) {
                // fall through to placeholder
            }
        }

        return asset('pfp-placeholder.jpg');
    }
}
