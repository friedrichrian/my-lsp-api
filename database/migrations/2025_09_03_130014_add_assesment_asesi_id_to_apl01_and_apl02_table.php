<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('form_apl02_submissions', function (Blueprint $table) {
            $table->foreignId('assesment_asesi_id')
                ->constrained('assesment_asesi')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_apl02_submissions', function (Blueprint $table) {
            $table->dropForeign(['assesment_asesi_id']);
            $table->dropColumn('assesment_asesi_id');
        });
    }
};
