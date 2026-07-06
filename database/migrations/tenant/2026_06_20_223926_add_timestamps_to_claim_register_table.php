<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToClaimRegisterTable extends Migration
{
    public function up()
    {
        Schema::table('claim_register', function (Blueprint $table) {
            // This adds both created_at and updated_at as nullable columns
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::table('claim_register', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
}