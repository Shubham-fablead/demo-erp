<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_items')) {
            Schema::create('production_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('production_id');
                $table->unsignedBigInteger('raw_material_id');
                $table->decimal('required_qty', 14, 3);
                $table->decimal('consume_qty', 14, 3);
                $table->decimal('rate', 14, 2)->default(0);
                $table->decimal('total_cost', 14, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_items');
    }
};
