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
        Schema::create('ofertas', function (Blueprint $table) {
                $table->id('id_oferta'); // PK, AUTO_INCREMENT 
                $table->unsignedBigInteger('id_comerc'); // FK, NN 
                $table->string('titol', 100); // Descripció curta, NN 
                $table->integer('cost_punts'); // Preu en punts, NN 
                $table->boolean('estat')->default(1); // 1 (Activa), 0 (Inactiva/Pausada), NN 
                $table->timestamps();

                // Definició de la Clau Forana
                $table->foreign('id_comerc')->references('id_comerc')->on('comercs'); 
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas');
    }
};
