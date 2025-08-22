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
        Schema::create('admin', function (Blueprint $table) {
            $table->id('id_admin'); // primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // foreign key to users table
            $table->string('nama_lengkap', 100);
            $table->string('email', 100)->unique();
            $table->string('no_hp', 20)->nullable();
            $table->enum('role', ['superadmin', 'admin', 'asesor', 'staff'])->default('admin');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
