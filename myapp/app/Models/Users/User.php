<?php

namespace App\Models\Users;

use App\Models\Records\Timetable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
        $username = $parts[0];
        $domain = $parts[1];

        // Mask username: first 2 chars visible
        $maskedUsername = substr($username, 0, 2) . str_repeat('•', max(strlen($username) - 2, 0));

        // Mask domain: keep only last label visible
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

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }
}
