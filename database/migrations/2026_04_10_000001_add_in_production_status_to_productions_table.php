<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE productions MODIFY COLUMN status ENUM('draft', 'in_production', 'completed') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE productions MODIFY COLUMN status ENUM('draft', 'completed') NOT NULL DEFAULT 'draft'");
    }
};
