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
        Schema::create('bukti_dokumen_formapl01', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_apl01_id')->constrained('form_apl01')->onDelete('cascade');
            $table->string('nama_dokumen');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_dokumen_formapl01');
    }
};
