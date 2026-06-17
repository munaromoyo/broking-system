<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
   protected $table = 'journal_entries'; // Explicitly set if not using plural
    public $timestamps = false;
    protected $fillable = ['jv_id', 'account_name', 'debit', 'credit'];
    
}
