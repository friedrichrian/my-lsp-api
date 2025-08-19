<?php

// database/migrations/2025_08_11_000000_create_asesmen_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Tabel skema sertifikasi (sudah ada)
        Schema::create('schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id')->constrained('jurusan')->onDelete('cascade');
            $table->string('judul_skema');
            $table->enum('jenis_skema', ['apl02','ia01', 'ak01', 'ak02', 'ak03', 'ak05', 'ia05']);
            $table->string('nomor_skema')->unique();
            $table->timestamp('tanggal_mulai')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        // Tabel units (sudah ada)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_id')->constrained()->onDelete('cascade');
            $table->integer('unit_ke');
            $table->string('kode_unit');
            $table->string('judul_unit');
            $table->timestamps();
            $table->unique(['schema_id', 'unit_ke']);
        });

        // Tabel elements (sudah ada)
        Schema::create('elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->integer('elemen_index');
            $table->string('nama_elemen');
            $table->timestamps();
            $table->unique(['unit_id', 'elemen_index']);
        });

        // Tabel kriteria untuk kerja (sudah ada)
        Schema::create('kriteria_untuk_kerja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('element_id')->constrained()->onDelete('cascade');
            $table->string('urutan');
            $table->text('deskripsi_kuk');
            $table->timestamps();
            $table->unique(['element_id', 'urutan']);
        });

        // TABEL BARU UNTUK FORMULIR ASESMEN
        // Tabel asesmen (header untuk semua formulir)
        Schema::create('asesmen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schema_id')->constrained()->onDelete('cascade');
            $table->foreignId('asesor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asesi_id')->constrained('users')->onDelete('cascade');
            $table->enum('tuk', ['Sewaktu', 'Tempat Kerja', 'Mandiri']);
            $table->date('tanggal_asesmen');
            $table->time('waktu_asesmen')->nullable();
            $table->string('lokasi_tuk');
            $table->enum('status', ['draft', 'pending', 'completed', 'rejected'])->default('draft');
            $table->timestamps();
        });

        // Tabel untuk FR.AK.01 - Persetujuan Asesmen dan Kerahasiaan
        Schema::create('form_ak01', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmen')->onDelete('cascade');
            
            // Metode pengumpulan bukti
            $table->boolean('verifikasi_portofolio')->default(false);
            $table->boolean('reviu_produk')->default(false);
            $table->boolean('observasi_langsung')->default(false);
            $table->boolean('kegiatan_terstruktur')->default(false);
            $table->boolean('pertanyaan_lisan')->default(false);
            $table->boolean('pertanyaan_tertulis')->default(false);
            $table->boolean('wawancara')->default(false);
            $table->string('metode_lainnya')->nullable();
            
            // Pernyataan dan persetujuan
            $table->text('pernyataan_asesi');
            $table->text('pernyataan_asesor');
            $table->text('persetujuan_asesi');
            
            // Tanda tangan
            $table->timestamp('tanggal_tanda_tangan_asesor')->nullable();
            $table->timestamp('tanggal_tanda_tangan_asesi')->nullable();
            $table->string('tanda_tangan_asesor')->nullable();
            $table->string('tanda_tangan_asesi')->nullable();
            
            $table->timestamps();
        });

        // Tabel untuk FR.AK.02 - Formulir Asesmen
        Schema::create('form_ak02', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmen')->onDelete('cascade');
            
            // Rekomendasi hasil asesmen
            $table->enum('rekomendasi', ['Kompeten', 'Belum Kompeten'])->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->text('komentar_asesor')->nullable();
            
            // Tanda tangan
            $table->timestamp('tanggal_tanda_tangan_asesor')->nullable();
            $table->timestamp('tanggal_tanda_tangan_asesi')->nullable();
            $table->string('tanda_tangan_asesor')->nullable();
            $table->string('tanda_tangan_asesi')->nullable();
            
            $table->timestamps();
        });

        // Tabel untuk metode pengumpulan bukti per unit kompetensi (FR.AK.02)
        Schema::create('form_ak02_bukti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_ak02_id')->constrained('form_ak02')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            
            // Metode pengumpulan bukti
            $table->boolean('observasi_demonstrasi')->default(false);
            $table->boolean('portofolio')->default(false);
            $table->boolean('pernyataan_pihak_ketiga')->default(false);
            $table->boolean('pertanyaan_lisan')->default(false);
            $table->boolean('pertanyaan_tertulis')->default(false);
            $table->boolean('proyek_kerja')->default(false);
            $table->boolean('lainnya')->default(false);
            $table->string('keterangan_lainnya')->nullable();
            
            $table->timestamps();
        });

        // Tabel untuk FR.AK.03 - Umpan Balik dan Catatan Asesmen
        Schema::create('form_ak03', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmen')->onDelete('cascade');
            
            // Umpan balik dari asesi
            $table->boolean('penjelasan_proses')->default(false);
            $table->boolean('kesempatan_belajar')->default(false);
            $table->boolean('diskusi_metoda')->default(false);
            $table->boolean('penggalian_bukti')->default(false);
            $table->boolean('demonstrasi_kompetensi')->default(false);
            $table->boolean('penjelasan_keputusan')->default(false);
            $table->boolean('umpan_balik')->default(false);
            $table->boolean('studi_dokumen')->default(false);
            $table->boolean('jaminan_kerahasiaan')->default(false);
            $table->boolean('komunikasi_efektif')->default(false);
            $table->text('catatan_tambahan')->nullable();
            
            $table->timestamps();
        });

        // Tabel untuk FR.AK.05 - Laporan Asesmen
        Schema::create('form_ak05', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmen')->onDelete('cascade');
            
            // Rekomendasi untuk setiap asesi
            $table->enum('rekomendasi', ['K', 'BK'])->nullable(); // Kompeten/Belum Kompeten
            $table->text('keterangan')->nullable();
            
            // Catatan asesmen
            $table->text('aspek_negatif_positif')->nullable();
            $table->text('pencatatan_penolakan')->nullable();
            $table->text('saran_perbaikan')->nullable();
            
            // Tanda tangan asesor
            $table->string('no_reg_asesor')->nullable();
            $table->timestamp('tanggal_tanda_tangan_asesor')->nullable();
            $table->string('tanda_tangan_asesor')->nullable();
            
            $table->timestamps();
        });

        // Tabel untuk IA.05 - Lembar Jawaban Pertanyaan Tertulis
        Schema::create('form_ia05', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesmen_id')->constrained('asesmen')->onDelete('cascade');
            
            // Umpan balik
            $table->text('umpan_balik')->nullable();
            
            // Tanda tangan
            $table->timestamp('tanggal_tanda_tangan_asesor')->nullable();
            $table->timestamp('tanggal_tanda_tangan_asesi')->nullable();
            $table->string('tanda_tangan_asesor')->nullable();
            $table->string('tanda_tangan_asesi')->nullable();
            
            $table->timestamps();
        });

        // Tabel untuk jawaban pertanyaan IA.05
        Schema::create('form_ia05_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_ia05_id')->constrained('form_ia05')->onDelete('cascade');
            $table->integer('nomor_soal');
            $table->string('jawaban')->nullable();
            $table->boolean('pencapaian')->default(false); // true = Ya, false = Tidak
            
            $table->timestamps();
            $table->unique(['form_ia05_id', 'nomor_soal']);
        });
    }

    public function down(): void {
        // Hapus tabel baru terlebih dahulu
        Schema::dropIfExists('form_ia05_jawaban');
        Schema::dropIfExists('form_ia05');
        Schema::dropIfExists('form_ak05');
        Schema::dropIfExists('form_ak03');
        Schema::dropIfExists('form_ak02_bukti');
        Schema::dropIfExists('form_ak02');
        Schema::dropIfExists('form_ak01');
        Schema::dropIfExists('asesmen');
        
        // Hapus tabel yang sudah ada
        Schema::dropIfExists('kriteria_untuk_kerja');
        Schema::dropIfExists('elements');
        Schema::dropIfExists('units');
        Schema::dropIfExists('schemas');
    }
};