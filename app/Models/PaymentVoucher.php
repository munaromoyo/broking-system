<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentVoucher extends Model
{

    protected $table = 'payment_vouchers';

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
        'created_at',
        'updated_by'
    ];
    

    // app/Models/Voucher.php

    protected $casts = [
    'approved_at' => 'datetime',
];

}

