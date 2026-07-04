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
        Schema::create('incident_logs', function (Blueprint $table) {
            $table->id();
            // Relasi ke manifest_items agar kita tahu rusak/hilangnya di transaksi yang mana
            $table->foreignId('manifest_item_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['broken', 'lost']);
            $table->integer('qty_affected');

            // resolved: Penanda apakah barang sudah diganti/diperbaiki (true) atau belum (false)
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_logs');
    }
};
