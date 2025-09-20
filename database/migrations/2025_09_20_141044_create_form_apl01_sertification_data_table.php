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
        Schema::create('form_apl01_sertification_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_apl01_id')->constrained('form_apl01')->onDelete('cascade');
            $table->foreignId('schema_id')->constrained('schemas')->onDelete('cascade');
            $table->string('tujuan_asesmen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_apl01_sertification_data');
    }
};
