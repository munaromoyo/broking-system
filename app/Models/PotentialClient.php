<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotentialClient extends Model
{
    // Point to your specific table name
    protected $table = 'potential_client_register';

    // Disable timestamps if the table doesn't have created_at/updated_at
    public $timestamps = false;

    
}
