<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicle_register'; // Your specific table name

    protected $fillable = [
        'user', 'slip_number', 'insurer_name', 'client_name', 'reg_number', 
        'vehicle_make', 'chassis_number', 'engine_number', 'policy_start_date', 
        'policy_expiry_date', 'policy_type', 'sum_insured', 'policy_currency', 'total_premium'
    ];

    public $timestamps = false; // Disable if you don't have created_at/updated_at
}