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
        Schema::create('form_ak01_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_asesi_id')->constrained('assesment_asesi')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->dateTime('submission_date');
            $table->timestamps();
        });

        Schema::create('ak01_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('form_ak01_submissions')->onDelete('cascade');
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
        Schema::dropIfExists('form_ak01_submissions');
        Schema::dropIfExists('ak01_attachments');
    }
};
