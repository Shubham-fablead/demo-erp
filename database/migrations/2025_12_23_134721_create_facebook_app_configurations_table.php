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
        Schema::create('facebook_app_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('facebook_app_id')->nullable();
            $table->text('facebook_app_secret')->nullable();
            $table->string('phone_number_id')->nullable();
            $table->string('whatsapp_business_account_id')->nullable();
            $table->text('access_token')->nullable();
            $table->text('webhook_url')->nullable();
            $table->boolean('isDeleted')->default(0);
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('users')->onDelete('cascade');

            // Index on branch_id for better query performance (branch-wise queries)
            $table->index('branch_id');

            // Index on isDeleted for filtering active configurations
            $table->index('isDeleted');

            // Composite index for efficient branch-wise queries filtering active records
            $table->index(['branch_id', 'isDeleted']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facebook_app_configurations');
    }
};
