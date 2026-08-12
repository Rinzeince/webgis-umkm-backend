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
        Schema::create('hasil_cluster', function (Blueprint $table) {
            $table->id('id_hasil');
            $table->unsignedBigInteger('id_analisis');
            $table->unsignedInteger('id_kecamatan');
            $table->tinyInteger('label_cluster');
            $table->string('interpretasi', 60)->nullable();
            $table->string('sektor_top1', 20)->nullable();
            $table->string('sektor_top2', 20)->nullable();
            $table->string('sektor_bottom1', 20)->nullable();
            $table->string('sektor_bottom2', 20)->nullable();
            $table->json('ranking_sektor_5')->nullable();
            $table->enum('flag_imputasi', ['OK', 'PERLU_VALIDASI'])->default('OK');

            // Unique constraint on (id_analisis, id_kecamatan)
            $table->unique(['id_analisis', 'id_kecamatan'], 'uq_analisis_kecamatan');

            // Foreign key to analisis with ON DELETE CASCADE
            $table->foreign('id_analisis')
                ->references('id_analisis')
                ->on('analisis')
                ->cascadeOnDelete();

            // Foreign key to kecamatan with ON DELETE RESTRICT
            $table->foreign('id_kecamatan')
                ->references('id_kecamatan')
                ->on('kecamatan')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_cluster');
    }
};
