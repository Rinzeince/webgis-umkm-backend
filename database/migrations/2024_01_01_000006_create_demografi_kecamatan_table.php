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
        Schema::create('demografi_kecamatan', function (Blueprint $table) {
            $table->unsignedInteger('id_demografi')->autoIncrement();
            $table->unsignedInteger('id_kecamatan');
            $table->smallInteger('tahun');
            $table->integer('kepadatan_penduduk')->nullable();
            $table->decimal('pertumbuhan_penduduk', 5, 2)->nullable();
            $table->decimal('jarak_ke_ibukota', 6, 2)->nullable();
            $table->timestamps();

            // Unique constraint on (id_kecamatan, tahun)
            $table->unique(['id_kecamatan', 'tahun'], 'uq_kec_tahun');

            // Foreign key with ON DELETE RESTRICT
            $table->foreign('id_kecamatan')
                ->references('id_kecamatan')
                ->on('kecamatan')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demografi_kecamatan');
    }
};
