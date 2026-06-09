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
        Schema::create('seguidors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuari');
            $table->unsignedBigInteger('id_comerc');
            $table->timestamps();

            $table->foreign('id_usuari')->references('id_usuari')->on('usuaris')->onDelete('cascade');
            $table->foreign('id_comerc')->references('id_comerc')->on('comercs')->onDelete('cascade');

            $table->unique(['id_usuari', 'id_comerc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguidors');
    }
};
