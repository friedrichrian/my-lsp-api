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
        // tabel komponen (master)
        Schema::create('komponen', function (Blueprint $table) {
            $table->id();
            $table->string('komponen'); // contoh: "Saya mendapatkan penjelasan yang cukup memadai mengenai proses asesmen"
            $table->timestamps();
        });

        // tabel ak03_submissions (form utama per assesment)
        Schema::create('ak03_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_asesi_id')->constrained('assesment_asesi')->onDelete('cascade');
            $table->text('catatan_tambahan')->nullable(); // opsional di akhir form
            $table->timestamps();
        });

        // tabel ak03_submission_details (jawaban per komponen)
        Schema::create('ak03_submission_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ak03_submission_id')->constrained('ak03_submissions')->onDelete('cascade');
            $table->foreignId('komponen_id')->constrained('komponen')->onDelete('cascade');
            $table->enum('hasil', ['ya', 'tidak']);
            $table->text('catatan_asesi')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ak03_submission_details');
        Schema::dropIfExists('ak03_submissions');
        Schema::dropIfExists('komponen');
    }
};
