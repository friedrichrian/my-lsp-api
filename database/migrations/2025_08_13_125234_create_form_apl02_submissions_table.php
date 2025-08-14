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
        Schema::create('form_apl02_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesi_id')->constrained('assesi')->onDelete('cascade');
            $table->foreignId('skema_id')->constrained('schemas')->onDelete('cascade');
            $table->dateTime('submission_date');
            $table->timestamps();
        });

        Schema::create('apl02_submission_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('form_apl02_submissions');
            $table->integer('unit_ke');
            $table->string('kode_unit');
            $table->foreignId('elemen_id')->constrained('elements')->onDelete('cascade');
            $table->enum('kompetensinitas', ['k', 'bk']);
            $table->timestamps();
        });

        Schema::create('apl02_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_detail_id')->constrained('apl02_submission_details');
            $table->foreignId('bukti_id')->constrained('bukti_dokumen_assesi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_apl02_submissions');
        Schema::dropIfExists('apl02_submission_details');
        Schema::dropIfExists('apl02_attachments');
        
    }
};
