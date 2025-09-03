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
        Schema::create('assesment_asesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_id')->constrained('assesments')->onDelete('cascade');
            $table->foreignId('assesi_id')->constrained('assesi')->onDelete('cascade');
            $table->enum('status', ['k', 'bk'])->default('k'); // k: Kelayakan, bk: Belum Kelayakan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('assesment_asesi');
    }
};
