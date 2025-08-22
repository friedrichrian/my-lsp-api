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
        Schema::create('observation_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_session_id')->constrained('assessment_sessions')->onDelete('cascade');
            $table->string('nama_kelompok'); // Kelompok 1, 2, 3
            $table->text('umpan_balik')->nullable(); // Umpan balik untuk asesi per kelompok
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_groups');
    }
};
