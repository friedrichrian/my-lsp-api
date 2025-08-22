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
        Schema::create('observation_kuks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_element_id')->constrained('observation_elements')->onDelete('cascade');
            $table->foreignId('kriteria_untuk_kerja_id')->constrained('kriteria_untuk_kerja')->onDelete('cascade'); // Reference ke tabel kriteria_untuk_kerja yang sudah ada
            $table->boolean('ya')->default(false);
            $table->boolean('tidak')->default(false);
            $table->text('standar_industri')->nullable(); // Standar Industri/Tempat Kerja
            $table->text('penilaian_lanjut')->nullable(); // Penilaian Lanjut
            $table->text('catatan')->nullable(); // Catatan/feedback
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_kuks');
    }
};
