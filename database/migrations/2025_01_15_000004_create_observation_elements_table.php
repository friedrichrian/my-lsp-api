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
        Schema::create('observation_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_unit_id')->constrained('observation_units')->onDelete('cascade');
            $table->foreignId('element_id')->constrained('elements')->onDelete('cascade'); // Reference ke tabel elements yang sudah ada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_elements');
    }
};
