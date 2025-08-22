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
        Schema::create('observation_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_group_id')->constrained('observation_groups')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade'); // Reference ke tabel units yang sudah ada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_units');
    }
};
