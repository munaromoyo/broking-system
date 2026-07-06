<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    // Point to your specific table name
    protected $table = 'credit_notes';

    // Disable timestamps if the table doesn't have created_at/updated_at
    //public $timestamps = false;

    // Enable timestamps if you want Laravel to manage created_at/updated_at
    public $timestamps = true;

    protected $fillable = [
        'slip_id', 'insurance_policy', 'insured_name', 'basic_premium',
        'premium_refund', 'policy_currency', 'cancelled_by',
        'cancellation_date', 'remarks', 'processed_by'
    ];


    public function slipCancellation() {
    return $this->belongsTo(SlipCancellation::class, 'slip_id', 'slip_id');
    
    }


}
