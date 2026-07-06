<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlipCancellation extends Model
{
    protected $table = 'slip_cancellation';

    // Disabling timestamps is correct since you are manually providing 
    // cancellation_date and cancelled_at in your controller.
    public $timestamps = true;

    // Mass assignment: Using $fillable is safer than $guarded in production
    protected $fillable = [
        'slip_id', 'insurance_policy', 'insured_name', 'basic_premium', 
        'premium_refund', 'policy_currency', 'cancelled_by', 
        'cancellation_date', 'cancellation_date_from', 
        'cancellation_date_to', 'remarks'
    ];

    /**
     * Get the placing slip that owns this cancellation log.
     */
    public function placingSlip()
    {
        return $this->belongsTo(PlacingSlip::class, 'slip_id');
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class, 'slip_id', 'slip_id');
    }
}