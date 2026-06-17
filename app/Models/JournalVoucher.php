<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalVoucher extends Model
{
   protected $fillable = ['jv_number', 'jv_date', 'description', 'currency', 'status'];

    // Relationship: A voucher has many line entries
    public function entries()
    {
        return $this->hasMany(JournalEntry::class, 'jv_id');
    }
    
}
