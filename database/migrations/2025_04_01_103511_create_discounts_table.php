<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed']);  // Enum for discount type
            $table->decimal('discount_value', 10, 2);  // Assuming the discount value is a decimal
            $table->decimal('min_order_value', 10, 2);  // Minimum order value for the discount
            $table->date('valid_from');  // Discount start date
            $table->date('valid_to');  // Discount end date
            $table->enum('status', ['active', 'inactive'])->default('active');  // Discount status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};

