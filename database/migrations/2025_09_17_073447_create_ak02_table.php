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
        Schema::create('ak02_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_asesi_id')->constrained('assesment_asesi')->onDelete('cascade');
            $table->enum('rekomendasi_hasil', ['kompeten', 'tidak_kompeten']);
            $table->text('tindak_lanjut')->nullable();
            $table->text('komentar_asesor')->nullable();
            $table->enum('ttd_asesi', ['belum', 'sudah'])->default('belum');
            $table->enum('ttd_asesor', ['belum', 'sudah'])->default('belum');
            $table->timestamps();
        });

        Schema::create('ak02_submission_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ak02_submission_id')->constrained('ak02_submissions')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('ak02_detail_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ak02_detail_id')->constrained('ak02_submission_details')->onDelete('cascade');
            $table->string('bukti');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop child tables first to satisfy foreign key constraints
        Schema::dropIfExists('ak02_detail_bukti');
        Schema::dropIfExists('ak02_submission_details');
        Schema::dropIfExists('ak02_submissions');
    }
};
