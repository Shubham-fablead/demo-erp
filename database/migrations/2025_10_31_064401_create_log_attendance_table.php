<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_attendance', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 255);
            $table->string('check_date', 255);
            $table->string('check_in', 255)->nullable();
            $table->string('checkout_out', 255)->nullable();
            $table->string('branch_id', 255);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_attendance');
    }
};
