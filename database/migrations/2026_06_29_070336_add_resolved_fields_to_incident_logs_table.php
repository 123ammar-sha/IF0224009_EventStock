<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResolvedFieldsToIncidentLogsTable extends Migration
{
    public function up()
    {
        Schema::table('incident_logs', function (Blueprint $table) {
            // Tambahkan field jika belum ada
            if (!Schema::hasColumn('incident_logs', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('resolved');
            }
            if (!Schema::hasColumn('incident_logs', 'resolved_by')) {
                $table->foreignId('resolved_by')
                    ->nullable()
                    ->constrained('users')
                    ->after('resolved_at');
            }
        });
    }

    public function down()
    {
        Schema::table('incident_logs', function (Blueprint $table) {
            if (Schema::hasColumn('incident_logs', 'resolved_at')) {
                $table->dropColumn('resolved_at');
            }
            if (Schema::hasColumn('incident_logs', 'resolved_by')) {
                $table->dropForeign(['resolved_by']);
                $table->dropColumn('resolved_by');
            }
        });
    }
}
