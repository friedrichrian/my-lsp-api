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
        Schema::create('assessment_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('judul_skema');
            $table->string('nomor_skema');
            $table->string('tuk'); // Tempat Uji Kompetensi
            $table->foreignId('assesor_id')->constrained('assesor')->onDelete('cascade');
            $table->foreignId('assesi_id')->constrained('assesi')->onDelete('cascade');
            $table->date('tanggal_asesmen');
            $table->enum('hasil_asesmen', ['kompeten', 'belum_kompeten'])->nullable();
            $table->text('catatan_asesor')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_sessions');
    }
};
