<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
   protected $fillable = [
        'transaction_date', 'value_date', 'description', 
        'reference_number', 'currency', 'debits', 
        'credits', 'balance', 'status', 'linked_receipt_id'
    ];
}
