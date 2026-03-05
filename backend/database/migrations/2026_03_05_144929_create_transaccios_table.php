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
        Schema::create('transaccios', function (Blueprint $table) {
                $table->id('id_transaccio'); // PK, AUTO_INCREMENT (BIGINT per defecte a Laravel) 
                $table->unsignedBigInteger('id_usuari'); // Client (FK, NN) 
                $table->unsignedBigInteger('id_comerc'); // Botiga (FK, NN) 
                $table->unsignedBigInteger('id_oferta')->nullable(); // FK, Ple només si és bescanvi 
                $table->unsignedBigInteger('id_tiquet')->nullable(); // FK, Ple només si és acumulació 
                $table->enum('tipus', ['ACUMULACIO', 'BESCANVI']); // NN 
                $table->integer('punts_mov'); // Quantitat, NN 
                $table->dateTime('data_hora'); // Timestamp exacte, NN 
                $table->timestamps();

                // Definicions de les Claus Foranes
                $table->foreign('id_usuari')->references('id_usuari')->on('usuaris');
                $table->foreign('id_comerc')->references('id_comerc')->on('comercs');
                $table->foreign('id_oferta')->references('id_oferta')->on('ofertas');
                $table->foreign('id_tiquet')->references('id_tiquet')->on('tiquet_validats');
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaccios');
    }
};
