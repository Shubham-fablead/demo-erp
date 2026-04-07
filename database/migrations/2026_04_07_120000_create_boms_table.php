<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('boms')) {
            Schema::create('boms', function (Blueprint $table) {
                $table->id();
                $table->string('bom_code')->unique();
                $table->unsignedBigInteger('product_id');
                $table->decimal('base_quantity', 14, 3)->default(1);
                $table->decimal('wastage_percentage', 8, 2)->default(0);
                $table->text('notes')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
