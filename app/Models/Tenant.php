<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Custom attributes that will automatically save into your 'data' JSON column
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'company_name',
            'address',
            'phone_contact',
            'email_contact',
            'company_tpin',
        ];
    }

    
}