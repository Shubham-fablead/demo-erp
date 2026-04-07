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
        Schema::create('purchase_invoice', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('vendor_id');
            $table->json('products'); // Store products as JSON
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('shipping', 10, 2)->nullable();
             $table->text('taxes')->nullable();
            $table->decimal('grand_total', 10, 2);
            $table->string('status')->default('pending');
            $table->boolean('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice');
    }
};
