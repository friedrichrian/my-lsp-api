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
        Schema::create('form_ia_01_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesi_id')->constrained('assesi')->onDelete('cascade');
            $table->foreignId('skema_id')->constrained('schemas')->onDelete('cascade');
            $table->foreignId('assesor_id')->nullable()->constrained('assesor')->onDelete('cascade');
            $table->dateTime('submission_date');
            $table->timestamps();
            $table->foreignId('assesment_asesi_id')
                ->constrained('assesment_asesi')
                ->onDelete('cascade');
        });

        Schema::create('ia_01_submission_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('form_ia_01_submissions')->onDelete('cascade');
            $table->integer('unit_ke');
            $table->string('kode_unit');
            $table->foreignId('elemen_id')->constrained('elements')->onDelete('cascade');
            $table->foreignId('kuk_id')->constrained('kriteria_untuk_kerja')->onDelete('cascade'); // Tambahkan kolom ini
            $table->enum('skkni', ['ya', 'tidak']);
            $table->text('teks_penilaian');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_ia_01_submission');
        Schema::dropIfExists('ia_01_submission_details');
    }
};
