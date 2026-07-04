<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't allow adding values to enum directly in some versions
        // We'll modify the column to include 'consumed'
        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available', 'on_duty', 'maintenance', 'lost', 'consumed') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available', 'on_duty', 'maintenance', 'lost') DEFAULT 'available'");
    }
};
