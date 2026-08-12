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
        Schema::create('dataset_agregat', function (Blueprint $table) {
            $table->unsignedInteger('id_agregat')->autoIncrement();
            $table->unsignedInteger('id_kecamatan');
            $table->integer('jml_makanan')->default(0);
            $table->integer('jml_kerajinan')->default(0);
            $table->integer('jml_fashion')->default(0);
            $table->integer('jml_jasa')->default(0);
            $table->integer('jml_lainnya')->default(0);
            $table->enum('status_analisis', ['perlu_analisis', 'sudah_dianalisis'])
                ->default('perlu_analisis');
            $table->timestamps();

            // Unique constraint on id_kecamatan (1:1 relationship)
            $table->unique('id_kecamatan', 'uq_kecamatan');

            // Foreign key with ON DELETE RESTRICT
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
        Schema::dropIfExists('dataset_agregat');
    }
};
