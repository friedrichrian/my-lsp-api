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
        //
        Schema::create('assesments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skema_id')->constrained('schemas')->onDelete('cascade');
            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')
                ->references('id_admin')
                ->on('admin')
                ->onDelete('cascade');
            $table->foreignId('assesor_id')->constrained('assesor')->onDelete('cascade');
            $table->date('tanggal_assesment');
            $table->enum('status', ['expired', 'active']);
            $table->string('tuk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schmema::dropIfExists('assesments');
    }
};
