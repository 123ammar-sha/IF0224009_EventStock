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
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();

            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->comment('ID Kru Lapangan');

            $table->enum('type', ['outbound', 'inbound']);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'has_issue'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifests');
    }
};
