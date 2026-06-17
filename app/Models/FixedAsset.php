<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    // Define the table if it's different from the plural 'fixed_assets'
    protected $table = 'fixed_assets';

    protected $fillable = [
        'asset_name', 
        'cost_price', 
        'current_value', 
        'purchase_date', 
        'depreciation_method'
    ];

    // This tells Laravel to treat purchase_date as a Carbon/Date object
    protected $casts = [
        'purchase_date' => 'date',
        'cost_price' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    
}
