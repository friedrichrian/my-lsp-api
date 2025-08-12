<?php

// database/migrations/2025_08_11_000000_create_apl02_simple_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusan')->onDelete('cascade');
            $table->string('judul_skema');
            $table->string('nomor_skema')->unique();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_id')->constrained()->onDelete('cascade');
            $table->integer('unit_ke');
            $table->string('kode_unit');
            $table->string('judul_unit');
            $table->timestamps();
            $table->unique(['schema_id', 'unit_ke']);
        });

        Schema::create('elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->integer('elemen_index');
            $table->string('nama_elemen');
            $table->timestamps();
            
            $table->unique(['unit_id', 'elemen_index']);
        });

        Schema::create('kriteria_untuk_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('element_id')->constrained()->onDelete('cascade');
            $table->integer('urutan');
            $table->text('deskripsi_kuk');
            $table->timestamps();
            
            $table->unique(['element_id', 'urutan']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('apl02_simple_items');
        Schema::dropIfExists('kriteria_untuk_kerja');
        Schema::dropIfExists('elements');
        Schema::dropIfExists('units');
        Schema::dropIfExists('schemas');
    }
};

