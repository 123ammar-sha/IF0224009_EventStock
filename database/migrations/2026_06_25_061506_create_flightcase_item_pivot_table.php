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
        Schema::create('flightcase_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flightcase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->integer('qty')->default(1)->comment('Jumlah item dalam flightcase');
            $table->timestamps();

            $table->unique(['flightcase_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flightcase_item');
    }
};
