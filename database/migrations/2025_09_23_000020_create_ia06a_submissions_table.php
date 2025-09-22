<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ia06a_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assesment_asesi_id');
            $table->unsignedBigInteger('skema_id')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('ttd_asesi', ['belum','sudah'])->nullable();
            $table->enum('ttd_asesor', ['belum','sudah'])->nullable();
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
        Schema::dropIfExists('ia06a_submissions');
    }
};
