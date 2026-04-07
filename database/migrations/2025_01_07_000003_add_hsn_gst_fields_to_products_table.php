<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->varchar('hsn_code')->nullable()->after('SKU');
            $table->varchar('gst_option')->nullable()->after('hsn_code');
            $table->text('product_gst')->nullable()->after('gst_option');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['hsn_code', 'gst_option', 'product_gst']);
        });
    }
};
