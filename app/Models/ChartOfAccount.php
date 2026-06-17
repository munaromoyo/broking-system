<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    // If your table name is exactly this, Laravel finds it automatically.
    protected $table = 'chart_of_accounts';

    // Disable timestamps if your table doesn't have created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'account_code', 
        'account_name', 
        'category', 
        'sub_category'
    ];
    
}
