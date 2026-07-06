<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentVoucher extends Model
{

    protected $table = 'payment_vouchers';

    // Disabling timestamps is correct since you are manually providing 
    // cancellation_date and cancelled_at in your controller.
    public $timestamps = true;

    // Mass assignable attributes
    protected $fillable = [
        'client_name', 
        'amount', 
        'currency', 
        'payment_method', 
        'description', 
        'expense_category', 
        'created_by',
        'status', // Added so you can default to 'Pending'
        'approved_by',
        'approved_at',
        'supporting_documents'
    ];
    

    // app/Models/Voucher.php

    protected $casts = [
    'approved_at' => 'datetime',
    'supporting_documents' => 'array',
];

}

