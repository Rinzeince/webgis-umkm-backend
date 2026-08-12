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
        Schema::create('umkm', function (Blueprint $table) {
            $table->id('id_umkm');
            $table->unsignedInteger('id_kecamatan');
            $table->unsignedInteger('id_kategori');
            $table->foreign('id_kecamatan')
                    ->references('id_kecamatan')
                    ->on('kecamatan')
                    ->restrictOnDelete();
            $table->foreign('id_kategori')
                    ->references('id_kategori')
                    ->on('kategori_umkm')
                    ->restrictOnDelete();
            $table->string('nama_umkm', 150);
            $table->string('nama_pemilik', 150)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('foto_url', 255)->nullable();
            $table->string('kontak', 50)->nullable();
            $table->enum('status_operasional', ['aktif', 'nonaktif'])
                ->default('aktif');
            $table->string('jam_operasional', 100)->nullable();
            $table->timestamps();

            // Foreign keys with ON DELETE RESTRICT
            // $table->foreign('id_kecamatan')
            //     ->references('id_kecamatan')
            //     ->on('kecamatan')
            //     ->onDelete('restrict');
            
            // $table->foreign('id_kategori')
            //     ->references('id_kategori')
            //     ->on('kategori_umkm')
            //     ->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umkm');
    }
};
