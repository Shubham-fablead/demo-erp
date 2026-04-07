<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_store', function (Blueprint $table) {
            $table->unsignedBigInteger('custom_invoice_id')->nullable()->after('order_id');
        });
    }

    public function down()
    {
        Schema::table('payment_store', function (Blueprint $table) {
            $table->dropForeign(['custom_invoice_id']);
            $table->dropColumn('custom_invoice_id');
        });
    }
};
