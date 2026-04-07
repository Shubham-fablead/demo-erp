<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('debit_notes_type', function (Blueprint $table) {
            $table->string('transaction_type')->nullable()->after('branch_id');
            $table->unsignedBigInteger('order_id')->nullable()->after('transaction_type');
            $table->unsignedBigInteger('purchase_id')->nullable()->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('debit_notes_type', function (Blueprint $table) {
            $table->dropColumn(['transaction_type', 'order_id', 'purchase_id']);
        });
    }
};
