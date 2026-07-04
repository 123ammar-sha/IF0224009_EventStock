<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'correction']);
            $table->integer('qty_change')->comment('Perubahan stok (+/-)');
            $table->integer('qty_before');
            $table->integer('qty_after');
            $table->string('reference_type')->nullable()->comment('Jenis referensi: manifest, purchase, adjustment');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID referensi');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
