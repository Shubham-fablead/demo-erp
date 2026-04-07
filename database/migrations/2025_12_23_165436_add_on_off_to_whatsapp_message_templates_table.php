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
        Schema::table('whatsapp_message_templates', function (Blueprint $table) {
            $table->enum('on_off', ['active', 'inactive'])->default('inactive')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_message_templates', function (Blueprint $table) {
            $table->dropColumn('on_off');
        });
    }
};
