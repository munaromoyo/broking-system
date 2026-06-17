<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsurerRemittance extends Model
{
    protected $table = 'insurer_remittances';
    // public $timestamps = true; // Laravel handles created_at/updated_at automatically
    // Disable automatic Eloquent timestamps
    public $timestamps = false;

    protected $fillable = [
        'receipt_number', 'invoice_number', 'client_name', 'insurer_name', 
        'policy_name', 'policy_start_date', 'policy_expiry_date',
        'policy_currency', 'total_sum_insured', 'basic_rate',
        'gross_amount_received', 'basic_premium_received', 'premium_levy_received',
        'rib_commission_received', 'insurer_premium_received',
        'amount_remitted', 'remittance_reference', 'remittance_date', 
        'processed_by', 'remittance_status'
    ];

    protected $casts = [
        'policy_start_date' => 'date:Y-m-d',
        'policy_expiry_date' => 'date:Y-m-d',
    ];
}