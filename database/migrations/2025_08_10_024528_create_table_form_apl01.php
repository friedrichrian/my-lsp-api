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
        Schema::create('form_apl01', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained()->onDelete('cascade');
            $table->string('nama_lengkap');
            $table->string('no_ktp')->unique();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('kebangsaan')->nullable();
            $table->string('alamat_rumah')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('no_telepon_rumah')->nullable();
            $table->string('no_telepon_kantor')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('kualifikasi_pendidikan')->nullable();
            $table->string('nama_institusi')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('alamat_kantor')->nullable();
            $table->string('kode_pos_kantor')->nullable();
            $table->string('fax_kantor')->nullable();
            $table->string('email_kantor')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_apl01');
    }
};
