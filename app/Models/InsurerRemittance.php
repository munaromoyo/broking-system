<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsurerRemittance extends Model
{
    protected $table = 'insurer_remittances';

    /**
     * Disable automatic Eloquent timestamps.
     * Set to true if your table uses created_at and updated_at columns.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'receipt_number', 
        'invoice_number', 
        'client_name', 
        'insurer_name', 
        'policy_name', 
        'policy_start_date', 
        'policy_expiry_date',
        'policy_currency', 
        'total_sum_insured', 
        'basic_rate',
        'gross_amount_received', 
        'basic_premium_received', 
        'premium_levy_received',
        'rib_commission_received', 
        'insurer_premium_received',
        'amount_remitted', 
        'remittance_reference', 
        'remittance_date', 
        'processed_by', 
        'remittance_status'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Dates
            'policy_start_date'        => 'date',
            'policy_expiry_date'       => 'date',
            'remittance_date'          => 'date',

            // Financial & Currency Metrics (Ensures high precision for ZRA/PIA compliance)
            'total_sum_insured'        => 'decimal:2',
            'basic_rate'               => 'decimal:4', // Rates typically require extended precision
            'gross_amount_received'    => 'decimal:2',
            'basic_premium_received'   => 'decimal:2',
            'premium_levy_received'    => 'decimal:2', // Handles the 5% premium levy calculation cleanly
            'rib_commission_received'  => 'decimal:2',
            'insurer_premium_received' => 'decimal:2',
            'amount_remitted'          => 'decimal:2',
        ];
    }

    /**
     * Relationship to the user who processed the remittance ledger.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}