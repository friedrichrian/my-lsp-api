<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ia02_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assesment_asesi_id');
            $table->unsignedBigInteger('skema_id')->nullable();
            $table->string('skema_sertifikasi')->nullable();
            $table->string('judul_unit')->nullable();
            $table->string('kode_unit')->nullable();
            $table->string('tuk')->nullable();
            $table->string('nama_asesor')->nullable();
            $table->string('nama_asesi')->nullable();
            $table->date('tanggal_asesmen')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->foreign('assesment_asesi_id')
                ->references('id')->on('assesment_asesi')
                ->onDelete('cascade');
            $table->foreign('skema_id')
                ->references('id')->on('schemas')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia02_submissions');
    }
};
