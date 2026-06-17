<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlipCancellation extends Model
{
    // Point to your specific table name
    protected $table = 'slip_cancellation';

    // Disable timestamps if the table doesn't have created_at/updated_at
    public $timestamps = false;
    
    // If you need to allow mass assignment later for creating records
    protected $guarded = []; 


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