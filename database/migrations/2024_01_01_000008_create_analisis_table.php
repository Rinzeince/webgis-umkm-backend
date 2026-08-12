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
        Schema::create('analisis', function (Blueprint $table) {
            $table->id('id_analisis');
            $table->tinyInteger('k_optimal')->nullable();
            $table->decimal('nilai_silhouette', 5, 4)->nullable();
            $table->decimal('nilai_dbi', 8, 4)->nullable();
            $table->json('dataset_snapshot')->nullable();
            $table->json('scaler_params')->nullable();
            $table->json('path_grafik')->nullable();
            $table->json('model_params')->nullable();
            $table->enum('status_job', ['dalam_antrean', 'diproses', 'selesai', 'gagal'])
                ->default('dalam_antrean');
            $table->text('error_log')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis');
    }
};
