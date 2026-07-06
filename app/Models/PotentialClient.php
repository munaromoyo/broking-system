<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotentialClient extends Model
{
    protected $table = 'potential_client_register';

    // Enable timestamps (or delete this line entirely)
    public $timestamps = true;

    protected $fillable = [
        'user', 
        'client_name',
        'client_type',
        'nature_of_business',
        'physical_address',
        'postal_address',
        'contact_number',
        'email_address',
        'policy_expiry_date',
        'expected_annual_premium',
    ];
    
}
