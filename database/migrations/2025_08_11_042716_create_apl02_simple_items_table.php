<?php

// database/migrations/2025_08_11_000000_create_apl02_simple_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('apl02_simple_items', function (Blueprint $table) {
            $table->id();
            $table->string('judul_skema')->nullable();
            $table->string('nomor_skema')->nullable();
            $table->integer('unit_ke')->nullable();
            $table->string('kode_unit')->nullable();
            $table->string('judul_unit')->nullable();
            $table->integer('elemen_index')->nullable();
            $table->string('elemen_text')->nullable();
            $table->string('sub_index')->nullable(); // 1.1, 1.2
            $table->string('sub_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('apl02_simple_items');
    }
};

