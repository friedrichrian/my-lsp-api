<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ia03_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assesment_asesi_id');
            $table->unsignedBigInteger('skema_id')->nullable();
            $table->timestamp('submission_date')->nullable();
            $table->timestamps();

            $table->foreign('assesment_asesi_id')
                ->references('id')->on('assesment_asesi')
                ->onDelete('cascade');
            $table->foreign('skema_id')
                ->references('id')->on('schemas')
                ->onDelete('set null');
        });

        Schema::create('ia03_submission_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('question_id');
            $table->enum('selected_option', ['ya','tidak'])->nullable();
            $table->text('response_text')->nullable();
            $table->timestamps();

            $table->foreign('submission_id')
                ->references('id')->on('ia03_submissions')
                ->onDelete('cascade');
            $table->foreign('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia03_submission_details');
        Schema::dropIfExists('ia03_submissions');
    }
};
