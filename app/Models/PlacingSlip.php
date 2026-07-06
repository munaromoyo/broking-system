<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlacingSlip extends Model
{
    use HasFactory;

    // Explicitly define the table name from your legacy database
    protected $table = 'slip_register';

    protected $primaryKey = 'id';

    // Keep enabled if your table uses standard created_at/updated_at columns
    public $timestamps = true;

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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'policy_start_date' => 'date',
            'policy_expiry_date' => 'date',
        ];
    }

    /**
     * Get the user that registered this placing slip.
     */
    public function creator(): BelongsTo
    {
        // Explicitly mapping 'user' prevents runtime lookup type crashes
        return $this->belongsTo(User::class, 'user');
    }

    /**
     * Get the insurances for the placing slip.
     */
    public function insurances(): HasMany
    {
        return $this->hasMany(Insurance::class, 'insured', 'id');
    }

    /**
     * Get the cancellation logs for this placing slip.
     */
    public function cancellations(): HasMany
    {
        return $this->hasMany(SlipCancellation::class, 'slip_id');
    }

    /**
     * Get the invoices associated with this placing slip.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'slip_number');
    }
}