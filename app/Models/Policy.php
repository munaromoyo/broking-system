<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Policy extends Model
{
    use HasFactory;

    protected $table = 'policy_register';

    protected $fillable = [
        'user', 
        'policy_name', 
        'scope_of_cover_policy', 
        'remarks_policy', 
        'class_of_policy'
    ];

    // Disable if your legacy table lacks standard created_at/updated_at columns
    public $timestamps = false;

    /**
     * Get the user that registered this policy.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user');
    }
}