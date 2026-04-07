<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('productions')) {
            Schema::create('productions', function (Blueprint $table) {
                $table->id();
                $table->string('production_no')->unique();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('bom_id');
                $table->decimal('production_qty', 14, 3);
                $table->decimal('output_qty', 14, 3);
                $table->decimal('wastage_qty', 14, 3)->default(0);
                $table->decimal('wastage_percentage', 8, 2)->default(0);
                $table->decimal('extra_cost', 14, 2)->default(0);
                $table->decimal('labor_cost', 14, 2)->default(0);
                $table->decimal('electricity_cost', 14, 2)->default(0);
                $table->decimal('total_cost', 14, 2)->default(0);
                $table->decimal('cost_per_unit', 14, 4)->default(0);
                $table->date('production_date');
                $table->enum('status', ['draft', 'completed'])->default('draft');
                $table->string('batch_no')->nullable();
                $table->date('expiry_date')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('branch_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
