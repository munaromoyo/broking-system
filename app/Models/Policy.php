<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    // Directs Laravel to your legacy table name
    protected $table = 'policy_register';

    protected $fillable = [
        'user', 
        'policy_name', 
        'scope_of_cover_policy', 
        'remarks_policy', 
        'class_of_policy'
    ];

    // Disable if your table lacks created_at/updated_at
    public $timestamps = false;
}