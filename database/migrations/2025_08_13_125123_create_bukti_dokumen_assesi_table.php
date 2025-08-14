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
        Schema::create('bukti_dokumen_assesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesi_id')->constrained('assesi')->onDelete('cascade');
            $table->string('nama_dokumen');
            $table->string('file_path');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bukti_dokumen_assesi');
    }
};
