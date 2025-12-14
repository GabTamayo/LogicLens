<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withTimestamps()
            ->withPivot('enrolled_at');
    }

    public function sendEmailVerificationNotification()
    {
        $this->forceFill(['verification_token' => Str::random(60)])->save();

        parent::sendEmailVerificationNotification();
    }

    public function getEmailForVerification()
    {
        // Include token in hash to invalidate old links
        return $this->email.'|'.$this->verification_token;
    }
}
