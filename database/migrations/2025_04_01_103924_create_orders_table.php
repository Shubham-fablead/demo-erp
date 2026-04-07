<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('discount')->nullable();
            $table->text('tax_id')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->default('cash');
            $table->string('order_invoice')->nullable();
            $table->boolean('isDeleted')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
