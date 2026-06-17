<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
    'user',
    'first_name',
    'middle_name',
    'last_name',
    'gender',
    'date_of_birth',
    'place_of_birth',
    'position',
    'department',
    'nationality',
    'id_number',
    'user_name',
    'company',
    'company_tpin',
    'profile',
    'class_of_business',
    'branch_name',
    'branch_manager',
    'mobile_number',
    'tel_number',
    'email',
    'password',
    'province',
    'postal_address',
    'physical_address',
    'current_country',
    'city',
    'status',
    'authtoken',
    'my_pin',
    'role',
    'created_at',
    'updated_at',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */

    protected $hidden = [
    'password',
    'remember_token',
    'authtoken',
    'my_pin',
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
            'date_of_birth' => 'date',
            'reset_expiry' => 'datetime',
        ];
    }


// Inside app/Models/User.php
public function getFullNameAttribute()
{
    return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
}





}
