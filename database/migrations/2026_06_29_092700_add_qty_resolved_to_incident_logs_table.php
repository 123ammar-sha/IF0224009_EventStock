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
        Schema::table('incident_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('incident_logs', 'qty_resolved')) {
                $table->integer('qty_resolved')->default(0)->after('qty_affected');
            }
            if (!Schema::hasColumn('incident_logs', 'qty_unresolved')) {
                $table->integer('qty_unresolved')->default(0)->after('qty_resolved');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_logs', function (Blueprint $table) {
            $table->dropColumn(['qty_resolved', 'qty_unresolved']);
        });
    }
};
