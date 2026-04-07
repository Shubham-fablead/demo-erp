<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sales_return_id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('product_id');

            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);

            $table->json('product_gst_details')->nullable();
            $table->decimal('product_gst_total', 12, 2)->default(0);

            $table->timestamps();

            // Optional FK
            // $table->foreign('sales_return_id')->references('id')->on('sales_returns');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_return_items');
    }
};
