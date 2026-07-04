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
        Schema::table('manifests', function (Blueprint $table) {
            $table->foreignId('outbound_manifest_id')
                ->nullable()
                ->after('user_id')
                ->constrained('manifests')
                ->nullOnDelete()
                ->comment('Relasi ke manifest outbound asal (hanya untuk inbound)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifests', function (Blueprint $table) {
            $table->dropForeign(['outbound_manifest_id']);
            $table->dropColumn('outbound_manifest_id');
        });
    }
};
