<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slip_register', function (Blueprint $table) {
            // Only add timestamps if 'updated_at' doesn't exist yet
            if (!Schema::hasColumn('slip_register', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('slip_register', function (Blueprint $table) {
            if (Schema::hasColumn('slip_register', 'updated_at')) {
                $table->dropTimestamps();
            }
        });
    }
};