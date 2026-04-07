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
        Schema::create('payment_store', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->date('payment_date');
            $table->string('payment_type');
            $table->string('payment_method');
            $table->string('cash_amount');
            $table->string('upi_amount');
            $table->string('emi_month');
            $table->string('remaining_amount');
            $table->unsignedBigInteger('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_store');
    }
};
