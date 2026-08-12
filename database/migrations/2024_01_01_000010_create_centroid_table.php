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
        Schema::create('centroid', function (Blueprint $table) {
            $table->id('id_centroid');
            $table->unsignedBigInteger('id_analisis');
            $table->tinyInteger('label_cluster');
            $table->string('interpretasi', 60)->nullable();
            $table->json('sektor_dominan')->nullable();
            $table->json('sektor_rendah')->nullable();
            $table->json('ranking_sektor')->nullable();
            $table->json('nilai_fitur')->nullable();

            // Unique constraint on (id_analisis, label_cluster)
            $table->unique(['id_analisis', 'label_cluster'], 'uq_analisis_cluster');

            // Foreign key to analisis with ON DELETE CASCADE
            $table->foreign('id_analisis')
                ->references('id_analisis')
                ->on('analisis')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centroid');
    }
};
