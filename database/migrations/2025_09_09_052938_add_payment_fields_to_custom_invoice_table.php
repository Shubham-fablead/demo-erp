<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCustomInvoiceItemTableAddPaymentFields extends Migration
{
    public function up()
    {
        Schema::table('custom_invoice_item', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_invoice_item', 'payment_mode')) {
                $table->string('payment_mode')->nullable()->after('invoice_id');
            }
            if (!Schema::hasColumn('custom_invoice_item', 'paid_type')) {
                $table->string('paid_type')->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('custom_invoice_item', 'cash_amount')) {
                $table->decimal('cash_amount', 10, 2)->default(0)->after('paid_type');
            }
            if (!Schema::hasColumn('custom_invoice_item', 'upi_amount')) {
                $table->decimal('upi_amount', 10, 2)->default(0)->after('cash_amount');
            }
            if (!Schema::hasColumn('custom_invoice_item', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('upi_amount');
            }
            if (!Schema::hasColumn('custom_invoice_item', 'remaining_amount')) {
                $table->decimal('remaining_amount', 10, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('custom_invoice_item', 'pending_amount')) {
                $table->decimal('pending_amount', 10, 2)->default(0)->after('remaining_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('custom_invoice_item', function (Blueprint $table) {
            $table->dropColumn([
                'payment_mode',
                'paid_type',
                'cash_amount',
                'upi_amount',
                'amount',
                'remaining_amount',
                'pending_amount',
            ]);
        });
    }
}
