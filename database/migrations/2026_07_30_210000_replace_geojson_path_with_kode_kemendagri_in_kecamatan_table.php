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
        Schema::table('kecamatan', function (Blueprint $table) {
            $table->dropColumn('geojson_path');
            $table->string('kode_kemendagri', 20)->nullable()->after('nama_kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kecamatan', function (Blueprint $table) {
            $table->dropColumn('kode_kemendagri');
            $table->string('geojson_path', 255)->nullable()->after('nama_kecamatan');
        });
    }
};
