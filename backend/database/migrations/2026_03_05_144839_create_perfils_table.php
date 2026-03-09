<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('perfils', function (Blueprint $table) {
                $table->id('id_perfil'); // PK, AUTO_INCREMENT
                $table->unsignedBigInteger('id_usuari')->unique(); // FK, UK, NN 
                $table->integer('punts_totals')->default(0); // NN, No pot ser negatiu 
                $table->string('imatge_url', 255)->nullable(); // Permet nuls 
                $table->timestamps();

                // Definició de la Clau Forana
                $table->foreign('id_usuari')->references('id_usuari')->on('usuaris'); //
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfils');
    }
};
