<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ak04_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assesment_asesi_id');
            $table->string('nama_asesor')->nullable();
            $table->string('nama_asesi')->nullable();
            $table->date('tanggal_asesmen')->nullable();
            $table->string('skema_sertifikasi')->nullable();
            $table->string('no_skema_sertifikasi')->nullable();
            $table->text('alasan_banding')->nullable();
            $table->date('tanggal_approve')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->foreign('assesment_asesi_id')->references('id')->on('assesment_asesi')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ak04_submissions');
    }
};
