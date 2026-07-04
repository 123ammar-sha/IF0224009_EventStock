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
        Schema::create('manifest_items', function (Blueprint $table) {
            $table->id();
            // cascadeOnDelete: Jika Header (manifest) dihapus, semua isi detailnya ikut terhapus otomatis
            $table->foreignId('manifest_id')->constrained()->cascadeOnDelete();

            // restrictOnDelete: Barang tidak boleh dihapus dari master jika masih ada di riwayat transaksi
            $table->foreignId('item_id')->constrained()->restrictOnDelete();

            $table->integer('qty_requested');
            $table->integer('qty_actual')->default(0);

            // condition: Kunci untuk mendeteksi barang rusak saat kembali
            $table->enum('condition', ['good', 'broken', 'lost'])->default('good');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifest_items');
    }
};
