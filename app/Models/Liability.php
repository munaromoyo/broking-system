<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liability extends Model
{
    protected $table = 'liabilities';

    protected $fillable = [
        'description', 
        'total_amount', 
        'balance_owed', 
        'liability_type', 
        'due_date'
    ];

    protected $casts = [
        'due_date'     => 'date',
        'total_amount' => 'decimal:2',
        'balance_owed' => 'decimal:2',
    ];

    /**
     * Scope to quickly fetch only long-term liabilities
     */
    public function scopeLongTerm($query)
    {
        return $query->where('liability_type', 'Long-term');
    }

    
}
