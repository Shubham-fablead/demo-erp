<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('row_material_purchase_invoice')) {
            Schema::create('row_material_purchase_invoice', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->string('invoice_number')->unique();
                $table->unsignedBigInteger('vendor_id');
                $table->string('bill_no');
                $table->longText('materials')->nullable();
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->decimal('paid', 14, 2)->nullable();
                $table->decimal('discount', 14, 2)->default(0);
                $table->decimal('shipping', 14, 2)->default(0);
                $table->decimal('grand_total', 14, 2)->default(0);
                $table->decimal('remaining_amount', 14, 2)->default(0);
                $table->string('gst_option')->nullable();
                $table->string('status')->default('pending');
                $table->longText('taxes')->nullable();
                $table->boolean('isDeleted')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('row_material_purchase_invoice');
    }
};
