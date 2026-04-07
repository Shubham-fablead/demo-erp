<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('purchase_id');   // original purchase
            $table->string('return_no')->unique();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->decimal('refund_amount', 12, 2)->default(0);

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by');

            $table->tinyInteger('isDeleted')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_returns');
    }
};
