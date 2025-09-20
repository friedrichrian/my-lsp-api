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
        Schema::create('ak05_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_asesi_id')->constrained('assesment_asesi')->onDelete('cascade');

            $table->enum('keputusan', ['k', 'bk']); // kompeten / belum kompeten
            $table->text('keterangan')->nullable();

            $table->text('aspek_positif')->nullable();
            $table->text('aspek_negatif')->nullable();
            $table->text('penolakan_hasil')->nullable();
            $table->text('saran_perbaikan')->nullable();

            $table->enum('ttd_asesor', ['belum', 'sudah'])->default('belum');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ak05_submissions');
    }
};
