<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    // Map to your specific table name
    protected $table = 'receipts';

    // FIX 1: Explicitly define your custom primary key column name
    protected $primaryKey = 'receipt_number';

    // Tell Laravel if your primary key is NOT an auto-incrementing integer (e.g., if it's a string or manual string text layout)
    public $incrementing = false; 
    protected $keyType = 'string'; // Change to 'int' if your receipt numbers are plain integers

    // Enable timestamps since you added created_at/updated_at to MySQL
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'receipt_number', // Ensure this is here if you set increments to false
        'invoice_number',
        'client_name',
        'insurer',
        'policy_name',
        'policy_start_date',
        'policy_expiry_date',
        'policy_currency',
        'total_sum_insured',
        'basic_rate',
        'basic_premium',
        'premium_levy_rate',
        'premium_levy',
        'gross_premium',
        'commission_rate',
        'commission_amount',
        'insurer_premium',
        'user',
        'description',
        'payment_method',
        'payment_ref',
        'reference_no',
        'gross_amount_received',
        'basic_premium_received',
        'premium_levy_received',
        'rib_commission_received',
        'insurer_premium_received',
        'receipt_date',
        'allocation_status',
        'bank_transaction_id',
        'bank_reference',
        'allocated_at',
        
        // FIX 2: Explicitly whitelist the status parameter so Laravel is allowed to modify it!
        'remittance_status', 
        'status',
        'cancelled_by',
        'cancelled_at',
        'cancellation_remarks',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_number', 'invoice_number');
    }
}