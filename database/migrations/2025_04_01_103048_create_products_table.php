<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Check if the table doesn't already exist
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vendor_id')->nullable();
                $table->foreignId('category_id')->nullable();
                $table->foreignId('brand_id')->nullable();
                $table->string('name')->nullable();
                $table->string('SKU')->nullable();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->integer('quantity')->nullable();
                $table->json('images')->nullable();
                $table->enum('availablility', ['in_stock', 'out_stock'])->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->boolean('isDeleted')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
