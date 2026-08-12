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
        Schema::create('artikel', function (Blueprint $table) {
            $table->id('id_artikel');
            $table->string('title', 150);
            $table->string('slug', 170);
            $table->string('excerpt', 255)->nullable();
            $table->longText('content');
            $table->string('thumbnail_url', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedBigInteger('id_author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Unique constraint on slug
            $table->unique('slug', 'uq_slug');

            // Foreign key to users with ON DELETE SET NULL
            $table->foreign('id_author')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
