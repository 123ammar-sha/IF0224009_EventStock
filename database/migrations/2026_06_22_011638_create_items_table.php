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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('flightcase_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique()->comment('Barcode / Serial Number');
            $table->string('name');
            $table->integer('total_qty')->default(0);
            $table->integer('available_qty')->default(0);
            $table->enum('status', ['available', 'on_duty', 'maintenance', 'lost'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
