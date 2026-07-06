<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    // 1. Point to your specific table name
    protected $table = 'claim_register';

    // 2. Enable timestamps so Laravel automatically manages created_at/updated_at
    public $timestamps = true;


    /**
     * 3. The attributes that are mass assignable.
     * Combined all fields from your registration and edit logic.
     */
    protected $fillable = [
        'user', 
        'claim_intimation_date', 
        'insurer_name', 
        'client_name', 
        'type_of_claim', 
        'date_of_loss', 
        'date_of_notification', 
        'details_of_loss', 
        'claim_amount', 
        'documents_received', 
        'claim_status', 
        'remarks', 
        'date_settled', 
        'amount_settled', 
        'policy_currency',
        'claim_documents'
    ];
    
    // Note: I removed protected $guarded = []; because $fillable is already 
    // doing the job of protecting your attributes.

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'claim_amount' => 'float',
        'amount_settled' => 'float',
    ];
}