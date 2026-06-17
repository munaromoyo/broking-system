<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
   /**
     * The table associated with the model.
     * Overriding the default 'clients' naming convention.
     *
     * @var string
     */
    protected $table = 'client_register';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user', 
        'client_name', 
        'physical_address', 
        'postal_address', 
        'contact_number', 
        'email_address', 
        'nature_of_business', 
        'client_type'
    ];

    /**
     * If your legacy table does not have 'created_at' and 'updated_at' columns,
     * uncomment the line below to prevent Eloquent from trying to update them.
     */
    // public $timestamps = false;


    // If your table name is not "clients", uncomment the line below:
    // protected $table = 'your_clients_table';

    /**
     * Get all invoices associated with this client.
     */
    public function invoices()
    {
        // Assuming your invoices table has a 'client_name' or 'client_id' column
        return $this->hasMany(Invoice::class, 'client_name', 'client_name');
    }

    /**
     * Get all receipts associated with this client.
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'client_name', 'client_name');
    }

    /**
     * Get all policy cancellations associated with this client.
     */
    public function cancellations()
    {
        return $this->hasMany(SlipCancellation::class, 'insured_name', 'client_name');
    }



    
}
