<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('item');  // Item name
            $table->integer('quantity');  // Quantity of the item
            $table->text('price')->nullable();  // Description of the purchase
            $table->enum('purchase_status', ['pending', 'completed', 'cancelled'])->default('pending');  // Purchase status
            $table->decimal('amount_total', 10, 2);  // Total amount for the purchase
            $table->unsignedBigInteger('vendor_id');  // Foreign key referencing the vendor
            $table->text('invoice_id')->nullable();  
            $table->string('payment_status')->default('unpaid');  // Payment status
                $table->boolean('isDeleted')->default(0);
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('vendor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
