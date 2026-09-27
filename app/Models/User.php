<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @array<int, string>
     */
    protected $fillable = [
        'department_id',
        'name',
        'email',
        'password',
        'phone_number',
        'employee_id',
        'position',
        'photo_path',
        'join_date',
        'is_active',
        'registration_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @array<int, string>
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
            'is_active' => 'boolean',
            'join_date' => 'date',
        ];
    }
}
