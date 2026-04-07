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
        Schema::create('custom_invoice_item', function (Blueprint $table) {
            $table->id();
            $table->string('item');  // Item name
            $table->integer('quantity');  // Quantity of the item
            $table->text('price')->nullable();  // Description of the purchase
            $table->enum('purchase_status', ['pending', 'completed', 'cancelled'])->default('pending');  // Purchase status
            $table->decimal('amount_total', 10, 2);  // Total amount for the purchase
            $table->unsignedBigInteger('vendor_id')->nullable(); 
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->text('invoice_id')->nullable();  
            $table->string('payment_status')->default('unpaid');  // Payment status
                $table->boolean('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_invoice_item');
    }
};
