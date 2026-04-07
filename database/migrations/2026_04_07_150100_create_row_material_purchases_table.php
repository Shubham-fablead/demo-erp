<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('row_material_purchases')) {
            Schema::create('row_material_purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('item');
                $table->integer('quantity');
                $table->longText('product_gst_details')->nullable();
                $table->decimal('product_gst_total', 14, 2)->default(0);
                $table->decimal('price', 14, 2)->default(0);
                $table->enum('purchase_status', ['pending', 'completed', 'cancelled', 'partially'])->default('pending');
                $table->decimal('amount_total', 14, 2)->default(0);
                $table->unsignedBigInteger('vendor_id');
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->string('payment_status')->default('pending');
                $table->decimal('discount_amount', 14, 2)->default(0);
                $table->decimal('discount_percent', 14, 2)->default(0);
                $table->boolean('isDeleted')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('row_material_purchases');
    }
};
