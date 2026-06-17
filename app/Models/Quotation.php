<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends Model
{
    use HasFactory;

    // 1. Force the model to target your custom table name
    protected $table = 'register_quote';

    /**
     * The attributes that are mass assignable.
     */
    
    // 2. Add 'user' and the calculated fields to the fillable array
    protected $fillable = [
        'user', 'insured', 'nature_of_business', 'principal_address', 
        'policy_start_date', 'policy_expiry_date', 'insurer', 
        'cancellation_clause', 'placing_slip_clause', 'insurance_policy', 
        'scope_of_cover', 'extensions', 'excess_deductible', 
        'property_insured', 'location_of_risk', 'specific_warranties', 
        'specific_conditions', 'policy_currency', 'total_sum_insured', 
        'basic_rate', 'basic_premium', 'discount_rate', 'discount', 
        'premium_levy_rate', 'premium_levy', 'gross_premium', 
        'commission_rate', 'commission_amount', 'insurer_premium', 
        'payment_made', 'payment_method'
    ];

    /**
     * Get the user that created the quotation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
