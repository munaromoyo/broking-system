<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Added this
use Illuminate\Database\Eloquent\Model;

class PlacingSlip extends Model
{
    use HasFactory;

    // Explicitly define the table name from your legacy database
    protected $table = 'slip_register';

    // Disable timestamps if your table doesn't use created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'user', 'insured', 'nature_of_business', 'principal_address', 
        'policy_start_date', 'policy_expiry_date', 'insurer', 
        'cancellation_clause', 'placing_slip_clause', 'insurance_policy', 
        'scope_of_cover', 'extensions', 'excess_deductible', 
        'property_insured', 'location_of_risk', 'specific_warranties', 
        'specific_conditions', 'policy_currency', 'total_sum_insured', 
        'basic_rate', 'basic_premium', 'discount_rate', 'discount', 
        'premium_levy_rate', 'premium_levy', 'gross_premium', 
        'commission_rate', 'commission_amount', 'payment_made', 
        'insurer_premium', 'payment_method', 'premium_status', 'status'
    ];

    /**
     * Get the insurances for the placing slip.
     */
    public function insurances()
    {
        // Ensure 'insured' is the foreign key on the 'insurances' table
        return $this->hasMany(Insurance::class, 'insured', 'id');
    }

    // Inside Slip model
    public function invoice() {
        return $this->hasOne(Invoice::class, 'slip_number');
    }


    /**
     * Get the cancellation logs for this placing slip.
     */
    public function cancellations()
    {
        // 'slip_id' is the foreign key on the 'slip_cancellation' table
        return $this->hasMany(SlipCancellation::class, 'slip_id');
    }

    /**
     * Get the invoices associated with this placing slip.
     */
    public function invoices()
    {
        // 'slip_number' is the foreign key on the 'invoices' table
        return $this->hasMany(Invoice::class, 'slip_number');
    }

    protected $primaryKey = 'id'; // Replace with your actual column name
}