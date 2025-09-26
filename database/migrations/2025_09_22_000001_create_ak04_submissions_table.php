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
            $table->text('alasan_banding')->nullable();
            $table->timestamps();

            $table->foreign('assesment_asesi_id')->references('id')->on('assesment_asesi')->onDelete('cascade');
        });
        
        Schema::create('ak04_question', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->timestamps();
        });

        Schema::create('ak04_question_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ak04_submission_id');
            $table->foreignId('ak04_question_id')->constrained('ak04_question')->onDelete('cascade');
            $table->enum('selected_option', ['ya', 'tidak'])->nullable();
            $table->timestamps();

            $table->foreign('ak04_submission_id')->references('id')->on('ak04_submissions')->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('ak04_question_submissions');
        Schema::dropIfExists('ak04_question');
        Schema::dropIfExists('ak04_submissions');
    }
};
