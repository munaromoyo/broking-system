<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Hardcoded here so this file knows exactly where to run
    protected $connection = 'tenantrib';

    public function up(): void
    {
        Schema::table('client_register', function (Blueprint $table) {
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::table('client_register', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};