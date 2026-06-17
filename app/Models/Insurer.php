<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurer extends Model
{
    use HasFactory;

    // Mapping to your legacy table
    protected $table = 'insurer_register';

    protected $fillable = [
        'user', 
        'insurer_name', 
        'physical_address', 
        'postal_address', 
        'insurer_type',
        'contact_number', // Added
        'email_address'   // Added
    ];

    // Set to false if you don't have created_at/updated_at columns
    public $timestamps = false;

    
}
