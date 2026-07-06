<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    // 1. TELL ELOQUENT TO LOOK FOR 'invoice_number' INSTEAD OF 'id'
    protected $primaryKey = 'invoice_number';

    // 2. IF 'invoice_number' IS A STRING/VARCHAR (e.g., "INV-2026-001"), ADD THESE TWO LINES:
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true; 

    // 3. ADD 'status' TO THE FILLABLE ARRAY
    protected $fillable = [
        'invoice_number', // Add this if you ever manually create invoices
        'status',         // <--- CRUCIAL: Allows ->update(['status' => ...]) to work
        'user', 'slip_number', 'client_name', 'principal_address', 
        'policy_start_date', 'policy_expiry_date', 'insurer', 
        'policy_name', 'policy_currency', 'total_sum_insured', 
        'basic_rate', 'basic_premium', 'premium_levy', 'discount_rate', 
        'premium_levy_rate', 'gross_premium', 'commission_rate', 
        'commission_amount', 'insurer_premium'
    ];

    /**
     * Get the placing slip associated with this invoice.
     */
    public function placingSlip()
    {
        return $this->belongsTo(PlacingSlip::class, 'slip_number');
    }
}