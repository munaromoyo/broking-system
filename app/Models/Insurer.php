<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'contact_number',
        'email_address'
    ];

    // Set to false if you don't have created_at/updated_at columns
    public $timestamps = false;

    /**
     * Get the user that registered this insurer.
     */
    public function creator(): BelongsTo
    {
        // Explicitly mapping the 'user' column protects against magic method type errors
        return $this->belongsTo(User::class, 'user');
    }
}